<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Database\Seeder;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vinsRouges = Categorie::where('nom', 'Vins Rouges')->first();
        $vinsBlancs = Categorie::where('nom', 'Vins Blancs')->first();
        $champagnes = Categorie::where('nom', 'Champagnes & Effervescents')->first();
        $spiritueux = Categorie::where('nom', 'Spiritueux & Cognacs')->first();

        $produits = [
            [
                'categorie_id' => $vinsRouges?->id ?? 1,
                'nom' => 'Château Margaux 2018',
                'reference' => 'REF-MARG-2018',
                'code_barre' => '3250390001011',
                'marque' => 'Château Margaux',
                'prix_achat' => 150000.00,
                'prix_vente' => 220000.00,
                'stock_min' => 3,
                'description' => 'Premier Grand Cru Classé de Margaux, millésime 2018 d\'exception.',
                'actif' => true,
            ],
            [
                'categorie_id' => $vinsRouges?->id ?? 1,
                'nom' => 'St-Émilion Grand Cru Château Simard',
                'reference' => 'REF-SIM-2015',
                'code_barre' => '3250390001028',
                'marque' => 'Château Simard',
                'prix_achat' => 15000.00,
                'prix_vente' => 24000.00,
                'stock_min' => 12,
                'description' => 'Vin rouge élégant, notes de fruits noirs et d\'épices.',
                'actif' => true,
            ],
            [
                'categorie_id' => $vinsBlancs?->id ?? 2,
                'nom' => 'Chablis Premier Cru Domaine Laroche',
                'reference' => 'REF-CHAB-2020',
                'code_barre' => '3250390002011',
                'marque' => 'Domaine Laroche',
                'prix_achat' => 18000.00,
                'prix_vente' => 28000.00,
                'stock_min' => 6,
                'description' => 'Vin blanc minéral, fraîcheur et notes d\'agrumes.',
                'actif' => true,
            ],
            [
                'categorie_id' => $champagnes?->id ?? 4,
                'nom' => 'Dom Pérignon Vintage Brut',
                'reference' => 'REF-DOMP-BRUT',
                'code_barre' => '3250390004015',
                'marque' => 'Dom Pérignon',
                'prix_achat' => 120000.00,
                'prix_vente' => 180000.00,
                'stock_min' => 4,
                'description' => 'Champagne d\'exception, équilibre parfait et longueur en bouche.',
                'actif' => true,
            ],
            [
                'categorie_id' => $spiritueux?->id ?? 5,
                'nom' => 'Hennessy XO Cognac 70cl',
                'reference' => 'REF-HEN-XO',
                'code_barre' => '3250390005012',
                'marque' => 'Hennessy',
                'prix_achat' => 95000.00,
                'prix_vente' => 140000.00,
                'stock_min' => 5,
                'description' => 'Cognac emblématique aux arômes riches et boisés.',
                'actif' => true,
            ],
        ];

        foreach ($produits as $prod) {
            Produit::firstOrCreate(['code_barre' => $prod['code_barre']], $prod);
        }
    }
}
