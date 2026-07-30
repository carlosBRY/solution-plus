<?php

namespace App\Services;

use App\Enums\MouvementType;
use App\Enums\StatutDeterioration;
use App\Models\Conditionnement;
use App\Models\Deterioration;
use App\Models\DeteriorationDetail;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeteriorationService
{
    /**
     * Crée une déclaration de détérioration en mode BROUILLON.
     */
    public function creerBrouillon(User $user, array $data, array $items): Deterioration
    {
        return DB::transaction(function () use ($user, $data, $items) {
            $numero = 'DET-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

            $deterioration = Deterioration::create([
                'user_id' => $user->id,
                'numero' => $numero,
                'date' => $data['date'] ?? now(),
                'statut' => StatutDeterioration::BROUILLON,
                'observation' => $data['observation'] ?? null,
                'total_perte' => 0,
            ]);

            $totalPerte = 0;

            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $conditionnement = Conditionnement::where('id', $item['conditionnement_id'])
                    ->where('produit_id', $produit->id)
                    ->firstOrFail();

                $quantiteCond = (int) $item['quantite_conditionnement'];
                $coeff = (int) $conditionnement->quantite_unite_base;
                $quantiteUniteBase = $quantiteCond * $coeff;

                $coutUnitaire = isset($item['cout_unitaire'])
                    ? (float) $item['cout_unitaire']
                    : (float) ($conditionnement->prix_achat ?? $produit->prix_achat);

                $valeurPerte = $quantiteUniteBase * $coutUnitaire;
                $totalPerte += $valeurPerte;

                DeteriorationDetail::create([
                    'deterioration_id' => $deterioration->id,
                    'produit_id' => $produit->id,
                    'conditionnement_id' => $conditionnement->id,
                    'quantite_conditionnement' => $quantiteCond,
                    'coefficient_conversion' => $coeff,
                    'quantite_unite_base' => $quantiteUniteBase,
                    'cout_unitaire' => $coutUnitaire,
                    'valeur_perte' => $valeurPerte,
                    'cause' => $item['cause'],
                    'observation' => $item['observation'] ?? null,
                ]);
            }

            $deterioration->update(['total_perte' => $totalPerte]);

            return $deterioration->load('details.produit', 'details.conditionnement');
        });
    }

    /**
     * Mettre à jour une détérioration en BROUILLON.
     */
    public function modifierBrouillon(Deterioration $deterioration, array $data, array $items): Deterioration
    {
        if (! $deterioration->isEstBrouillon()) {
            throw new Exception('Une détérioration validée ou annulée ne peut plus être modifiée.');
        }

        return DB::transaction(function () use ($deterioration, $data, $items) {
            $deterioration->details()->delete();

            $totalPerte = 0;

            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $conditionnement = Conditionnement::where('id', $item['conditionnement_id'])
                    ->where('produit_id', $produit->id)
                    ->firstOrFail();

                $quantiteCond = (int) $item['quantite_conditionnement'];
                $coeff = (int) $conditionnement->quantite_unite_base;
                $quantiteUniteBase = $quantiteCond * $coeff;

                $coutUnitaire = isset($item['cout_unitaire'])
                    ? (float) $item['cout_unitaire']
                    : (float) ($conditionnement->prix_achat ?? $produit->prix_achat);

                $valeurPerte = $quantiteUniteBase * $coutUnitaire;
                $totalPerte += $valeurPerte;

                DeteriorationDetail::create([
                    'deterioration_id' => $deterioration->id,
                    'produit_id' => $produit->id,
                    'conditionnement_id' => $conditionnement->id,
                    'quantite_conditionnement' => $quantiteCond,
                    'coefficient_conversion' => $coeff,
                    'quantite_unite_base' => $quantiteUniteBase,
                    'cout_unitaire' => $coutUnitaire,
                    'valeur_perte' => $valeurPerte,
                    'cause' => $item['cause'],
                    'observation' => $item['observation'] ?? null,
                ]);
            }

            $deterioration->update([
                'date' => $data['date'] ?? $deterioration->date,
                'observation' => $data['observation'] ?? $deterioration->observation,
                'total_perte' => $totalPerte,
            ]);

            return $deterioration->fresh(['details.produit', 'details.conditionnement']);
        });
    }

    /**
     * Valide une détérioration et décrémente le stock en unités de base avec verrouillage transactionnel (lockForUpdate).
     */
    public function valider(Deterioration $deterioration, User $valideur): Deterioration
    {
        if ($deterioration->isEstValidee()) {
            throw ValidationException::withMessages([
                'statut' => 'Cette détérioration a déjà été validée.',
            ]);
        }

        if ($deterioration->statut === StatutDeterioration::ANNULEE) {
            throw ValidationException::withMessages([
                'statut' => 'Impossible de valider une détérioration annulée.',
            ]);
        }

        return DB::transaction(function () use ($deterioration, $valideur) {
            $deterioration->load('details.produit', 'details.conditionnement');

            foreach ($deterioration->details as $detail) {
                // Verrouiller la ligne de stock avec lockForUpdate()
                $stock = Stock::where('produit_id', $detail->produit_id)->lockForUpdate()->first();

                if (! $stock) {
                    throw ValidationException::withMessages([
                        'stock' => "Aucun enregistrement de stock trouvé pour le produit {$detail->produit->nom}.",
                    ]);
                }

                $quantiteRequise = $detail->quantite_unite_base;

                if ($stock->quantite < $quantiteRequise) {
                    throw ValidationException::withMessages([
                        'stock' => "Stock insuffisant pour {$detail->produit->nom}. Disponible: {$stock->quantite} {$detail->produit->unite_base}(s), requis: {$quantiteRequise}.",
                    ]);
                }

                $stockAvant = $stock->quantite;
                $stockApres = $stockAvant - $quantiteRequise;

                // Décrémenter le stock en unité de base
                $stock->update(['quantite' => $stockApres]);

                // Mouvement de stock de type DETERIORATION
                MouvementStock::create([
                    'produit_id' => $detail->produit_id,
                    'user_id' => $valideur->id,
                    'conditionnement_id' => $detail->conditionnement_id,
                    'type' => MouvementType::DETERIORATION,
                    'quantite' => -$quantiteRequise,
                    'quantite_conditionnement' => $detail->quantite_conditionnement,
                    'coefficient_conversion' => $detail->coefficient_conversion,
                    'stock_avant' => $stockAvant,
                    'stock_apres' => $stockApres,
                    'motif' => "Détérioration {$deterioration->numero} (Cause: {$detail->cause->value})",
                    'reference' => $deterioration->numero,
                ]);
            }

            // Marquer comme validée (immutabilité)
            $deterioration->update([
                'statut' => StatutDeterioration::VALIDEE,
                'valide_par' => $valideur->id,
                'date_validation' => now(),
            ]);

            return $deterioration;
        });
    }

    /**
     * Supprime une détérioration (Autorisé uniquement si BROUILLON).
     */
    public function supprimer(Deterioration $deterioration): bool
    {
        if (! $deterioration->isEstBrouillon()) {
            throw new Exception('Impossible de supprimer une détérioration validée.');
        }

        return DB::transaction(function () use ($deterioration) {
            $deterioration->details()->delete();

            return $deterioration->delete();
        });
    }
}
