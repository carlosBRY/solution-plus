<?php

namespace App\Services;

use App\Enums\ModePaiement;
use App\Enums\MouvementType;
use App\Enums\StatutVente;
use App\Models\Client;
use App\Models\Conditionnement;
use App\Models\DetailVente;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VenteService
{
    public function __construct(
        protected CompteFinancierService $compteService
    ) {}

    /**
     * Enregistre une vente, contrôle la disponibilité en stock, décrémente les stocks en unités de base et enregistre le règlement (avec gestion automatique du crédit).
     */
    public function creerVente(User $user, array $data, array $items): Vente
    {
        return DB::transaction(function () use ($user, $data, $items) {
            $numero = 'VTE-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
            $modePaiement = $data['mode_paiement'] ?? ModePaiement::ESPECES->value;

            $client = null;
            if (! empty($data['client_id'])) {
                $client = Client::where('id', $data['client_id'])->lockForUpdate()->first();
            }

            $vente = Vente::create([
                'client_id' => $client?->id,
                'user_id' => $user->id,
                'numero' => $numero,
                'date' => $data['date'] ?? now(),
                'statut' => StatutVente::EN_ATTENTE,
                'sous_total' => 0,
                'remise' => $data['remise_globale'] ?? 0,
                'tva' => $data['tva'] ?? 0,
                'total' => 0,
                'montant_paye' => 0,
                'monnaie' => 0,
            ]);

            $sousTotal = 0;

            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $conditionnement = Conditionnement::where('id', $item['conditionnement_id'])
                    ->where('produit_id', $produit->id)
                    ->firstOrFail();

                $quantiteCond = (int) $item['quantite_conditionnement'];
                $coeff = (int) $conditionnement->quantite_unite_base;
                $quantiteUniteBase = $quantiteCond * $coeff;

                // Verrouiller le stock pour contrôle strict
                $stock = Stock::where('produit_id', $produit->id)->lockForUpdate()->first();

                if (! $stock || $stock->quantite < $quantiteUniteBase) {
                    $dispo = $stock?->quantite ?? 0;
                    throw ValidationException::withMessages([
                        'stock' => "Stock insuffisant pour '{$produit->nom}'. Disponible: {$dispo} {$produit->unite_base}(s), Requis: {$quantiteUniteBase}.",
                    ]);
                }

                // Prix de vente fixé selon le paramétrage DB (non modifiable)
                $prixVente = (float) ($conditionnement->prix_vente ?? ($produit->prix_vente * $coeff));

                $remiseLigne = (float) ($item['remise'] ?? 0);
                $totalLigne = max(0, ($quantiteCond * $prixVente) - $remiseLigne);
                $sousTotal += $totalLigne;

                DetailVente::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $produit->id,
                    'conditionnement_id' => $conditionnement->id,
                    'quantite' => $quantiteUniteBase,
                    'quantite_conditionnement' => $quantiteCond,
                    'coefficient_conversion' => $coeff,
                    'prix' => $prixVente,
                    'remise' => $remiseLigne,
                    'total' => $totalLigne,
                ]);

                // Décrémenter le stock en unité de base
                $stockAvant = $stock->quantite;
                $stockApres = $stockAvant - $quantiteUniteBase;
                $stock->update(['quantite' => $stockApres]);

                // Mouvement de stock
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'user_id' => $user->id,
                    'conditionnement_id' => $conditionnement->id,
                    'type' => MouvementType::SORTIE,
                    'quantite' => -$quantiteUniteBase,
                    'quantite_conditionnement' => $quantiteCond,
                    'coefficient_conversion' => $coeff,
                    'stock_avant' => $stockAvant,
                    'stock_apres' => $stockApres,
                    'motif' => "Vente {$vente->numero}",
                    'reference' => $vente->numero,
                ]);
            }

            $remiseGlobale = (float) ($data['remise_globale'] ?? 0);
            $tva = (float) ($data['tva'] ?? 0);
            $totalVente = max(0, $sousTotal - $remiseGlobale + $tva);

            $isCredit = ($modePaiement === ModePaiement::CREDIT->value || $modePaiement === ModePaiement::CREDIT);

            if ($isCredit) {
                if (! $client) {
                    throw ValidationException::withMessages([
                        'client_id' => 'Pour enregistrer une vente à crédit, vous devez obligatoirement sélectionner un client enregistré.',
                    ]);
                }

                $montantPaye = (float) ($data['montant_paye'] ?? 0);
                $dette = max(0, $totalVente - $montantPaye);

                if ($dette > 0 && $client->depassePlafondCredit($dette)) {
                    $plafondFormate = number_format($client->plafond_credit, 0, ',', ' ');
                    $soldeFormate = number_format($client->solde, 0, ',', ' ');
                    throw ValidationException::withMessages([
                        'credit' => "Cette vente à crédit ({$dette} FCFA) dépasse le plafond autorisé pour {$client->nom}. Solde actuel: {$soldeFormate} FCFA, Plafond: {$plafondFormate} FCFA.",
                    ]);
                }

                // Incrémenter le solde débiteur du client (dette) sans impacter la caisse
                if ($dette > 0) {
                    $client->increment('solde', $dette);
                }
            } else {
                $montantPaye = (float) ($data['montant_paye'] ?? $totalVente);

                if ($montantPaye < $totalVente) {
                    throw ValidationException::withMessages([
                        'montant_paye' => 'Le montant reçu ('.number_format($montantPaye, 0, ',', ' ').' FCFA) est inférieur au total de la commande ('.number_format($totalVente, 0, ',', ' ').' FCFA). Sélectionnez le mode "Crédit" pour accorder un crédit à un client.',
                    ]);
                }

                $dette = 0;
            }

            $monnaie = max(0, $montantPaye - $totalVente);

            $statutVente = StatutVente::PAYEE;
            $datePaiementCredit = null;

            if ($isCredit) {
                if ($dette <= 0.01) {
                    $statutVente = StatutVente::PAYEE_CREDIT;
                    $datePaiementCredit = now();
                } else {
                    $statutVente = StatutVente::EN_ATTENTE;
                }
            }

            $vente->update([
                'sous_total' => $sousTotal,
                'total' => $totalVente,
                'montant_paye' => $montantPaye,
                'monnaie' => $monnaie,
                'is_credit' => $isCredit,
                'date_paiement_credit' => $datePaiementCredit,
                'statut' => $statutVente,
            ]);

            // Enregistrer le règlement sur le compte financier uniquement pour la somme réellement reçue
            if ($montantPaye > 0) {
                $montantEncaisse = min($montantPaye, $totalVente);
                $modeCode = is_object($modePaiement) ? $modePaiement->value : (string) $modePaiement;
                $compte = $this->compteService->getCompteParMode($modeCode);

                $paiement = Paiement::create([
                    'vente_id' => $vente->id,
                    'mode' => $modePaiement,
                    'montant' => $montantEncaisse,
                    'reference' => $data['reference_paiement'] ?? null,
                    'compte_financier_id' => $compte?->id,
                    'date' => now(),
                ]);

                if ($compte) {
                    $this->compteService->crediter(
                        $compte,
                        $user,
                        $montantEncaisse,
                        "Vente #{$vente->numero}",
                        $vente->id,
                        'vente'
                    );
                }
            }

            return $vente->load('details.produit', 'details.conditionnement', 'client', 'paiements');
        });
    }
}
