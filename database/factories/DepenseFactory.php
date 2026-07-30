<?php

namespace Database\Factories;

use App\Models\Depense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Depense>
 */
class DepenseFactory extends Factory
{
    protected $model = Depense::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'libelle' => fake()->sentence(3),
            'categorie' => fake()->randomElement(['Facture d\'eau/électricité', 'Transport', 'Fournitures', 'Entretien']),
            'montant' => fake()->randomFloat(2, 1000, 30000),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'observation' => fake()->sentence(),
        ];
    }
}
