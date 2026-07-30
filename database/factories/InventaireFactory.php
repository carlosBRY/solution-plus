<?php

namespace Database\Factories;

use App\Models\Inventaire;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventaire>
 */
class InventaireFactory extends Factory
{
    protected $model = Inventaire::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'observation' => fake()->sentence(),
        ];
    }
}
