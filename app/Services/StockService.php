<?php

namespace App\Services;

use App\Enums\MouvementType;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Ajuster manuellement la quantité en stock d'un produit.
     */
    public function ajusterStock(Produit $produit, User $user, int $nouvelleQuantite, string $motif, ?string $reference = null): Stock
    {
        return DB::transaction(function () use ($produit, $user, $nouvelleQuantite, $motif, $reference) {
            $stock = Stock::firstOrCreate(
                ['produit_id' => $produit->id],
                ['quantite' => 0]
            );

            // Verrouiller la ligne stock
            $stockLock = Stock::where('id', $stock->id)->lockForUpdate()->first();
            $stockAvant = (int) $stockLock->quantite;
            $nouvelleQuantite = max(0, $nouvelleQuantite);
            $quantiteMouvement = $nouvelleQuantite - $stockAvant;

            $stockLock->update(['quantite' => $nouvelleQuantite]);

            MouvementStock::create([
                'produit_id' => $produit->id,
                'user_id' => $user->id,
                'type' => MouvementType::AJUSTEMENT,
                'quantite' => $quantiteMouvement,
                'stock_avant' => $stockAvant,
                'stock_apres' => $nouvelleQuantite,
                'motif' => $motif,
                'reference' => $reference,
            ]);

            return $stockLock->refresh();
        });
    }

    /**
     * Enregistrer une entrée ou sortie manuelle de stock.
     */
    public function mouvementManuel(Produit $produit, User $user, MouvementType $type, int $quantite, string $motif, ?string $reference = null): Stock
    {
        return DB::transaction(function () use ($produit, $user, $type, $quantite, $motif, $reference) {
            $stock = Stock::firstOrCreate(
                ['produit_id' => $produit->id],
                ['quantite' => 0]
            );

            $stockLock = Stock::where('id', $stock->id)->lockForUpdate()->first();
            $stockAvant = (int) $stockLock->quantite;
            $absQuantite = abs($quantite);

            if ($type === MouvementType::ENTREE || $type === MouvementType::RETOUR) {
                $nouvelleQuantite = $stockAvant + $absQuantite;
                $quantiteMouvement = $absQuantite;
            } else {
                $nouvelleQuantite = max(0, $stockAvant - $absQuantite);
                $quantiteMouvement = -$absQuantite;
            }

            $stockLock->update(['quantite' => $nouvelleQuantite]);

            MouvementStock::create([
                'produit_id' => $produit->id,
                'user_id' => $user->id,
                'type' => $type,
                'quantite' => $quantiteMouvement,
                'stock_avant' => $stockAvant,
                'stock_apres' => $nouvelleQuantite,
                'motif' => $motif,
                'reference' => $reference,
            ]);

            return $stockLock->refresh();
        });
    }
}
