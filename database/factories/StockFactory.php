<?php

namespace Database\Factories;

use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'produit_id' => Produit::factory(),
            'quantite' => fake()->numberBetween(10, 100),
        ];
    }
}
