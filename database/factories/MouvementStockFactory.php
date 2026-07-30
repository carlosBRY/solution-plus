<?php

namespace Database\Factories;

use App\Enums\MouvementType;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MouvementStock>
 */
class MouvementStockFactory extends Factory
{
    protected $model = MouvementStock::class;

    public function definition(): array
    {
        $avant = fake()->numberBetween(10, 50);
        $qty = fake()->numberBetween(1, 10);

        return [
            'produit_id' => Produit::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(MouvementType::cases()),
            'quantite' => $qty,
            'stock_avant' => $avant,
            'stock_apres' => $avant + $qty,
            'motif' => fake()->sentence(),
            'reference' => 'MVT-'.fake()->randomNumber(6, true),
        ];
    }
}
