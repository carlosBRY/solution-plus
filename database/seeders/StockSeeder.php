<?php

namespace Database\Seeders;

use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produits = Produit::all();

        foreach ($produits as $produit) {
            Stock::firstOrCreate(
                ['produit_id' => $produit->id],
                ['quantite' => rand(15, 60)]
            );
        }
    }
}
