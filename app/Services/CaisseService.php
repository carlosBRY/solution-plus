<?php

namespace App\Services;

use App\Enums\StatutCaisse;
use App\Models\Caisse;
use App\Models\CompteFinancier;
use App\Models\Depense;
use App\Models\Paiement;
use App\Models\ReglementDette;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CaisseService
{
    /**
     * Obtenir la caisse actuellement ouverte pour un utilisateur ou globale.
     */
    public function getCaisseOuverte(?User $user = null): ?Caisse
    {
        $query = Caisse::where('statut', StatutCaisse::OUVERTE);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->latest('date_ouverture')->first();
    }

    /**
     * Récupère les soldes de la dernière clôture pour la reconduction automatique.
     * Retourne un tableau [compte_id => solde_final] pour chaque compte.
     *
     * @return array<string, array{nom: string, mode: string, solde: float}>
     */
    public function getDerniersSoldesFinaux(?User $user = null): array
    {
        $query = Caisse::where('statut', StatutCaisse::FERMEE);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $derniereCaisse = $query->latest('date_fermeture')->first();

        $comptes = CompteFinancier::actif()->orderBy('nom')->get();
        $soldes = [];

        foreach ($comptes as $compte) {
            $soldeFinal = null;

            // Chercher le solde final dans la dernière session clôturée
            if ($derniereCaisse && $derniereCaisse->details_comptes) {
                $details = $derniereCaisse->details_comptes['comptes'] ?? [];
                if (isset($details[$compte->id])) {
                    $soldeFinal = (float) $details[$compte->id]['solde_final'];
                }
            }

            // Fallback : solde courant du compte
            $soldes[$compte->id] = [
                'nom' => $compte->nom,
                'mode' => $compte->mode,
                'solde' => $soldeFinal ?? (float) $compte->solde_courant,
            ];
        }

        return $soldes;
    }

    /**
     * Ouvrir une nouvelle session de caisse avec soldes initiaux par compte.
     *
     * @param  array<string, float>  $soldesInitiaux  [compte_id => montant]
     */
    public function ouvrirCaisse(User $user, array $soldesInitiaux = []): Caisse
    {
        $existante = $this->getCaisseOuverte($user);

        if ($existante) {
            throw ValidationException::withMessages([
                'solde_initial' => 'Vous avez déjà une session de caisse ouverte.',
            ]);
        }

        $comptes = CompteFinancier::actif()->get()->keyBy('id');
        $detailsComptes = [];
        $soldeInitialTotal = 0;

        foreach ($comptes as $id => $compte) {
            $soldeCompte = (float) ($soldesInitiaux[$id] ?? $compte->solde_courant);
            $soldeInitialTotal += $soldeCompte;

            $detailsComptes[$id] = [
                'nom' => $compte->nom,
                'mode' => $compte->mode,
                'solde_initial' => $soldeCompte,
                'ventes' => 0,
                'reglements' => 0,
                'depenses' => 0,
                'solde_theorique' => $soldeCompte,
                'solde_final' => null,
                'ecart' => null,
            ];
        }

        return Caisse::create([
            'user_id' => $user->id,
            'date_ouverture' => now(),
            'solde_initial' => max(0, $soldeInitialTotal),
            'statut' => StatutCaisse::OUVERTE,
            'details_comptes' => ['comptes' => $detailsComptes],
        ]);
    }

    /**
     * Calculer les statistiques et le solde théorique d'une session de caisse,
     * ventilés par compte financier / mode de paiement.
     *
     * @return array{by_account: array, solde_initial: float, total_ventes: float, total_reglements: float, total_encaissements: float, total_depenses: float, solde_theorique: float, ecart: float}
     */
    public function getStatistiquesCaisse(Caisse $caisse): array
    {
        $dateDebut = $caisse->date_ouverture;
        $dateFin = $caisse->date_fermeture ?? now();
        $details = $caisse->details_comptes['comptes'] ?? [];

        // Ventes par mode de paiement (groupées)
        $ventesParMode = Paiement::whereBetween('created_at', [$dateDebut, $dateFin])
            ->selectRaw('CASE WHEN compte_financier_id IS NOT NULL THEN compte_financier_id ELSE mode END as group_key, SUM(montant) as total')
            ->groupBy('group_key')
            ->pluck('total', 'group_key')
            ->toArray();

        // Règlements de dettes par mode
        $reglementsParMode = ReglementDette::whereBetween('created_at', [$dateDebut, $dateFin])
            ->selectRaw('mode, SUM(montant) as total')
            ->groupBy('mode')
            ->pluck('total', 'mode')
            ->toArray();

        // Dépenses par compte financier
        $depensesParCompte = Depense::whereBetween('created_at', [$dateDebut, $dateFin])
            ->whereNotNull('compte_financier_id')
            ->selectRaw('compte_financier_id, SUM(montant) as total')
            ->groupBy('compte_financier_id')
            ->pluck('total', 'compte_financier_id')
            ->toArray();

        // Dépenses sans compte associé (anciennes ou non liées)
        $depensesSansCompte = (float) Depense::whereBetween('created_at', [$dateDebut, $dateFin])
            ->whereNull('compte_financier_id')
            ->sum('montant');

        $comptes = CompteFinancier::actif()->get()->keyBy('id');

        // Mode → CompteFinancier ID mapping
        $modeToCompteId = $comptes->mapWithKeys(fn ($c) => [$c->mode => $c->id])->toArray();

        $byAccount = [];
        $totalVentes = 0;
        $totalReglements = 0;
        $totalDepenses = 0;
        $soldeInitialGlobal = 0;

        foreach ($comptes as $id => $compte) {
            $soldeInit = (float) ($details[$id]['solde_initial'] ?? $compte->solde_courant);

            // Ventes: match by compte_financier_id ou par mode
            $ventes = (float) ($ventesParMode[$id] ?? 0);
            if ($ventes === 0.0 && isset($modeToCompteId[$compte->mode])) {
                $ventes = (float) ($ventesParMode[$compte->mode] ?? 0);
            }

            // Règlements: match by mode
            $reglements = (float) ($reglementsParMode[$compte->mode] ?? 0);

            // Dépenses: match by compte_financier_id
            $depenses = (float) ($depensesParCompte[$id] ?? 0);

            $soldeTheorique = $soldeInit + $ventes + $reglements - $depenses;

            // Récupérer solde_final et ecart si la caisse est clôturée
            $soldeFinal = $details[$id]['solde_final'] ?? null;
            $ecart = $soldeFinal !== null ? (float) $soldeFinal - $soldeTheorique : null;

            $byAccount[$id] = [
                'nom' => $compte->nom,
                'mode' => $compte->mode,
                'solde_initial' => $soldeInit,
                'ventes' => $ventes,
                'reglements' => $reglements,
                'depenses' => $depenses,
                'solde_theorique' => $soldeTheorique,
                'solde_final' => $soldeFinal,
                'ecart' => $ecart,
            ];

            $totalVentes += $ventes;
            $totalReglements += $reglements;
            $totalDepenses += $depenses;
            $soldeInitialGlobal += $soldeInit;
        }

        // Ajouter les dépenses sans compte au total
        $totalDepenses += $depensesSansCompte;

        $soldeTheoriqueGlobal = $soldeInitialGlobal + $totalVentes + $totalReglements - $totalDepenses;
        $ecartGlobal = $caisse->solde_final !== null ? (float) $caisse->solde_final - $soldeTheoriqueGlobal : 0.0;

        return [
            'by_account' => $byAccount,
            'solde_initial' => $soldeInitialGlobal,
            'total_ventes' => $totalVentes,
            'total_reglements' => $totalReglements,
            'total_encaissements' => $totalVentes + $totalReglements,
            'total_depenses' => $totalDepenses,
            'solde_theorique' => $soldeTheoriqueGlobal,
            'ecart' => $ecartGlobal,
        ];
    }

    /**
     * Clôturer une session de caisse avec soldes finaux par compte.
     *
     * @param  array<string, float>  $soldesFinaux  [compte_id => montant_compté]
     */
    public function fermerCaisse(Caisse $caisse, array $soldesFinaux): Caisse
    {
        if ($caisse->statut === StatutCaisse::FERMEE) {
            throw ValidationException::withMessages([
                'solde_final' => 'Cette caisse a déjà été clôturée.',
            ]);
        }

        return DB::transaction(function () use ($caisse, $soldesFinaux) {
            $stats = $this->getStatistiquesCaisse($caisse);
            $byAccount = $stats['by_account'];

            $soldeFinalGlobal = 0;
            $detailsComptes = [];

            foreach ($byAccount as $compteId => $accountStats) {
                $soldeFinalCompte = (float) ($soldesFinaux[$compteId] ?? $accountStats['solde_theorique']);
                $ecartCompte = $soldeFinalCompte - $accountStats['solde_theorique'];
                $soldeFinalGlobal += $soldeFinalCompte;

                $detailsComptes[$compteId] = [
                    'nom' => $accountStats['nom'],
                    'mode' => $accountStats['mode'],
                    'solde_initial' => $accountStats['solde_initial'],
                    'ventes' => $accountStats['ventes'],
                    'reglements' => $accountStats['reglements'],
                    'depenses' => $accountStats['depenses'],
                    'solde_theorique' => $accountStats['solde_theorique'],
                    'solde_final' => $soldeFinalCompte,
                    'ecart' => $ecartCompte,
                ];
            }

            $ecartGlobal = $soldeFinalGlobal - $stats['solde_theorique'];

            $caisse->update([
                'date_fermeture' => now(),
                'solde_final' => $soldeFinalGlobal,
                'ecart' => $ecartGlobal,
                'statut' => StatutCaisse::FERMEE,
                'details_comptes' => ['comptes' => $detailsComptes],
            ]);

            return $caisse->refresh();
        });
    }
}
