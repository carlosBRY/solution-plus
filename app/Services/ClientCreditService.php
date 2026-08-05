<?php

namespace App\Services;

use App\Enums\ModePaiement;
use App\Enums\StatutVente;
use App\Models\AjustementCredit;
use App\Models\Client;
use App\Models\CompteFinancier;
use App\Models\Paiement;
use App\Models\ReglementDette;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientCreditService
{
    public function __construct(
        protected CompteFinancierService $compteService
    ) {}

    /**
     * Enregistre un règlement de dette / remboursement de crédit par un client avec crédit automatique du compte financier
     * et affectation FIFO des remboursements aux ventes à crédit en attente.
     */
    public function reglerDette(Client $client, User $user, array $data): ReglementDette
    {
        $montant = (float) $data['montant'];

        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant du règlement doit être supérieur à zéro.',
            ]);
        }

        if ($client->solde <= 0) {
            throw ValidationException::withMessages([
                'montant' => "Le client {$client->nom} n'a aucune dette en cours à régler.",
            ]);
        }

        return DB::transaction(function () use ($client, $user, $data, $montant) {
            // Verrouiller le client
            $clientLock = Client::where('id', $client->id)->lockForUpdate()->first();

            $numero = 'REG-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

            $compte = null;
            if (! empty($data['compte_financier_id'])) {
                $compte = CompteFinancier::find($data['compte_financier_id']);
            } elseif (! empty($data['mode'])) {
                $compte = $this->compteService->getCompteParMode($data['mode']);
            }

            $reglement = ReglementDette::create([
                'client_id' => $clientLock->id,
                'user_id' => $user->id,
                'numero' => $numero,
                'montant' => $montant,
                'mode' => $compte?->mode ?? ($data['mode'] ?? 'ESPECES'),
                'compte_financier_id' => $compte?->id,
                'reference' => $data['reference'] ?? null,
                'date' => $data['date'] ?? now(),
                'observation' => $data['observation'] ?? null,
            ]);

            // Décrémenter la dette / le solde du client (sans descendre en dessous de 0)
            $nouveauSolde = max(0, $clientLock->solde - $montant);
            $clientLock->update(['solde' => $nouveauSolde]);

            // Créditer le compte financier sélectionné
            if ($compte && $montant > 0) {
                $this->compteService->crediter(
                    $compte,
                    $user,
                    $montant,
                    "Règlement dette client: {$clientLock->nom} (Reçu #{$numero})",
                    $reglement->id,
                    'reglement_dette'
                );
            }

            // Affectation FIFO du remboursement aux ventes à crédit en attente
            $ventesEnAttente = Vente::where('client_id', $clientLock->id)
                ->where('is_credit', true)
                ->where('statut', StatutVente::EN_ATTENTE)
                ->orderBy('date', 'asc')
                ->lockForUpdate()
                ->get();

            $resteAAttribuer = $montant;

            foreach ($ventesEnAttente as $vente) {
                if ($resteAAttribuer <= 0) {
                    break;
                }

                $duSurVente = (float) $vente->total - (float) $vente->montant_paye;
                if ($duSurVente <= 0) {
                    continue;
                }

                $montantAffecte = min($resteAAttribuer, $duSurVente);
                $nouveauMontantPaye = (float) $vente->montant_paye + $montantAffecte;
                $resteAAttribuer -= $montantAffecte;

                // Créer un paiement lié à la vente
                $modePaiement = ModePaiement::tryFrom($reglement->mode ?? '') ?? ModePaiement::ESPECES;
                Paiement::create([
                    'vente_id' => $vente->id,
                    'mode' => $modePaiement,
                    'montant' => $montantAffecte,
                    'reference' => "Règlement dette #{$reglement->numero}",
                    'compte_financier_id' => $compte?->id,
                    'date' => now(),
                ]);

                // Si la vente à crédit est totalement réglée
                if ($nouveauMontantPaye >= ((float) $vente->total - 0.01)) {
                    $vente->update([
                        'montant_paye' => $vente->total,
                        'monnaie' => 0,
                        'statut' => StatutVente::PAYEE_CREDIT,
                        'date_paiement_credit' => now(),
                    ]);
                } else {
                    $vente->update([
                        'montant_paye' => $nouveauMontantPaye,
                    ]);
                }
            }

            return $reglement->load('client', 'user', 'compteFinancier');
        });
    }

    /**
     * Ajoute un crédit (dette) à un client sans passer par une vente.
     * Accessible aux Administrateurs et Gérants.
     */
    public function ajouterCredit(Client $client, User $user, array $data): AjustementCredit
    {
        $montant = (float) $data['montant'];

        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant du crédit doit être supérieur à zéro.',
            ]);
        }

        return DB::transaction(function () use ($client, $user, $data, $montant) {
            $clientLock = Client::where('id', $client->id)->lockForUpdate()->first();
            $soldeAvant = (float) $clientLock->solde;
            $soldeApres = $soldeAvant + $montant;

            $clientLock->update(['solde' => $soldeApres]);

            return AjustementCredit::create([
                'client_id' => $clientLock->id,
                'user_id' => $user->id,
                'type' => 'AJOUT',
                'montant' => $montant,
                'solde_avant' => $soldeAvant,
                'solde_apres' => $soldeApres,
                'motif' => $data['motif'],
                'date' => $data['date'] ?? now(),
            ]);
        });
    }

    /**
     * Ajuste (corrige) le solde d'un client en cas d'erreur.
     * Réservé exclusivement aux Administrateurs.
     */
    public function ajusterCredit(Client $client, User $user, array $data): AjustementCredit
    {
        $nouveauSolde = (float) $data['nouveau_solde'];

        if ($nouveauSolde < 0) {
            throw ValidationException::withMessages([
                'nouveau_solde' => 'Le nouveau solde ne peut pas être négatif.',
            ]);
        }

        return DB::transaction(function () use ($client, $user, $data, $nouveauSolde) {
            $clientLock = Client::where('id', $client->id)->lockForUpdate()->first();
            $soldeAvant = (float) $clientLock->solde;
            $difference = $nouveauSolde - $soldeAvant;

            $clientLock->update(['solde' => $nouveauSolde]);

            return AjustementCredit::create([
                'client_id' => $clientLock->id,
                'user_id' => $user->id,
                'type' => 'AJUSTEMENT',
                'montant' => $difference,
                'solde_avant' => $soldeAvant,
                'solde_apres' => $nouveauSolde,
                'motif' => $data['motif'],
                'date' => $data['date'] ?? now(),
            ]);
        });
    }
}
