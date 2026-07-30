<?php

namespace Database\Factories;

use App\Models\Approvisionnement;
use App\Models\DetailApprovisionnement;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailApprovisionnement>
 */
class DetailApprovisionnementFactory extends Factory
{
    protected $model = DetailApprovisionnement::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(5, 50);
        $prix = fake()->randomFloat(2, 1000, 10000);

        return [
            'approvisionnement_id' => Approvisionnement::factory(),
            'produit_id' => Produit::factory(),
            'quantite' => $qty,
            'prix_achat' => $prix,
            'total' => $qty * $prix,
        ];
    }
}
