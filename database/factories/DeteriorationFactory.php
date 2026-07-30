<?php

namespace Database\Factories;

use App\Enums\StatutDeterioration;
use App\Models\Deterioration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deterioration>
 */
class DeteriorationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'numero' => 'DET-'.date('Ymd').'-'.strtoupper($this->faker->unique()->bothify('???###')),
            'date' => now(),
            'statut' => StatutDeterioration::BROUILLON,
            'total_perte' => 0,
            'observation' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Deterioration validee.
     */
    public function validee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => StatutDeterioration::VALIDEE,
            'valide_par' => User::factory(),
            'date_validation' => now(),
        ]);
    }
}
