<?php

namespace Database\Factories;

use App\Models\Produit;
use App\Models\RetourDetail;
use App\Models\RetourVente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RetourDetail>
 */
class RetourDetailFactory extends Factory
{
    protected $model = RetourDetail::class;

    public function definition(): array
    {
        return [
            'retour_id' => RetourVente::factory(),
            'produit_id' => Produit::factory(),
            'quantite' => fake()->numberBetween(1, 3),
        ];
    }
}
