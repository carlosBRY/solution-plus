<?php

namespace App\Services;

use App\Enums\MouvementType;
use App\Models\Inventaire;
use App\Models\InventaireDetail;
use App\Models\MouvementStock;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventaireService
{
    /**
     * Enregistrer un nouvel inventaire avec comptage physique et régularisation automatique du stock.
     */
    public function creerInventaire(User $user, array $items, ?string $observation = null): Inventaire
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => "L'inventaire doit contenir au moins un produit.",
            ]);
        }

        return DB::transaction(function () use ($user, $items, $observation) {
            $inventaire = Inventaire::create([
                'user_id' => $user->id,
                'date' => now(),
                'observation' => $observation,
            ]);

            foreach ($items as $item) {
                $produitId = $item['produit_id'];
                $stockPhysique = max(0, (int) $item['stock_physique']);

                $stock = Stock::firstOrCreate(
                    ['produit_id' => $produitId],
                    ['quantite' => 0]
                );

                $stockLock = Stock::where('id', $stock->id)->lockForUpdate()->first();
                $stockTheorique = (int) $stockLock->quantite;
                $ecart = $stockPhysique - $stockTheorique;

                InventaireDetail::create([
                    'inventaire_id' => $inventaire->id,
                    'produit_id' => $produitId,
                    'stock_theorique' => $stockTheorique,
                    'stock_physique' => $stockPhysique,
                    'ecart' => $ecart,
                ]);

                // Mettre à jour le stock réel
                $stockLock->update(['quantite' => $stockPhysique]);

                // Si écart constaté, enregistrer le mouvement d'ajustement
                if ($ecart !== 0) {
                    MouvementStock::create([
                        'produit_id' => $produitId,
                        'user_id' => $user->id,
                        'type' => MouvementType::AJUSTEMENT,
                        'quantite' => $ecart,
                        'stock_avant' => $stockTheorique,
                        'stock_apres' => $stockPhysique,
                        'motif' => 'Ajustement suite à l\'inventaire du '.now()->format('d/m/Y H:i'),
                        'reference' => 'INV-'.substr($inventaire->id, -6),
                    ]);
                }
            }

            return $inventaire->load(['inventaireDetails.produit', 'user']);
        });
    }
}
