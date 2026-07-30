<?php

namespace Database\Factories;

use App\Models\Fournisseur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fournisseur>
 */
class FournisseurFactory extends Factory
{
    protected $model = Fournisseur::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->company(),
            'telephone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'adresse' => fake()->streetAddress(),
            'ville' => fake()->city(),
            'pays' => fake()->country(),
            'observation' => fake()->sentence(),
        ];
    }
}
