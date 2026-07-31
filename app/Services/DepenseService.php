<?php

namespace App\Services;

use App\Models\CompteFinancier;
use App\Models\Depense;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepenseService
{
    public function __construct(
        protected CompteFinancierService $compteService
    ) {}

    /**
     * Enregistrer une nouvelle dépense avec débit automatique du compte financier.
     */
    public function creerDepense(User $user, array $data): Depense
    {
        return DB::transaction(function () use ($user, $data) {
            $compte = null;
            if (! empty($data['compte_financier_id'])) {
                $compte = CompteFinancier::find($data['compte_financier_id']);
            } elseif (! empty($data['mode'])) {
                $compte = $this->compteService->getCompteParMode($data['mode']);
            }

            $depense = Depense::create([
                'user_id' => $user->id,
                'libelle' => $data['libelle'],
                'reference_piece' => $data['reference_piece'] ?? null,
                'categorie' => $data['categorie'] ?? 'Divers',
                'montant' => (float) $data['montant'],
                'date' => $data['date'] ?? now(),
                'observation' => $data['observation'] ?? null,
                'mode' => $compte?->mode ?? ($data['mode'] ?? null),
                'compte_financier_id' => $compte?->id,
            ]);

            if ($compte) {
                $refPiece = $depense->reference_piece ? " (Pièce: {$depense->reference_piece})" : '';
                $this->compteService->debiter(
                    $compte,
                    $user,
                    (float) $data['montant'],
                    "Dépense: {$depense->libelle}{$refPiece}",
                    $depense->id,
                    'depense'
                );
            }

            return $depense;
        });
    }

    /**
     * Modifier une dépense existante.
     */
    public function modifierDepense(Depense $depense, array $data): Depense
    {
        return DB::transaction(function () use ($depense, $data) {
            $depense->update([
                'libelle' => $data['libelle'],
                'reference_piece' => $data['reference_piece'] ?? $depense->reference_piece,
                'categorie' => $data['categorie'] ?? $depense->categorie,
                'montant' => (float) $data['montant'],
                'date' => $data['date'] ?? $depense->date,
                'observation' => $data['observation'] ?? null,
            ]);

            return $depense->refresh();
        });
    }

    /**
     * Supprimer une dépense (et recréditer le compte si applicable).
     */
    public function supprimerDepense(Depense $depense): bool
    {
        return DB::transaction(function () use ($depense) {
            if ($depense->compteFinancier && $depense->user) {
                $this->compteService->crediter(
                    $depense->compteFinancier,
                    $depense->user,
                    (float) $depense->montant,
                    "Annulation dépense: {$depense->libelle}",
                    $depense->id,
                    'depense_annulation'
                );
            }

            return (bool) $depense->delete();
        });
    }
}
