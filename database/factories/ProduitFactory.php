<?php

namespace Database\Factories;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produit>
 */
class ProduitFactory extends Factory
{
    protected $model = Produit::class;

    public function definition(): array
    {
        $prixAchat = fake()->randomFloat(2, 2000, 15000);

        return [
            'categorie_id' => Categorie::factory(),
            'nom' => fake()->words(3, true),
            'reference' => 'REF-'.fake()->unique()->randomNumber(5, true),
            'code_barre' => fake()->unique()->ean13(),
            'marque' => fake()->word(),
            'prix_achat' => $prixAchat,
            'prix_vente' => $prixAchat * 1.4,
            'stock_min' => 5,
            'photo' => null,
            'description' => fake()->sentence(),
            'actif' => true,
        ];
    }
}
