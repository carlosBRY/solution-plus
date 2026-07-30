<?php

namespace Database\Factories;

use App\Enums\ModePaiement;
use App\Models\Paiement;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paiement>
 */
class PaiementFactory extends Factory
{
    protected $model = Paiement::class;

    public function definition(): array
    {
        return [
            'vente_id' => Vente::factory(),
            'mode' => fake()->randomElement(ModePaiement::cases()),
            'montant' => fake()->randomFloat(2, 2000, 50000),
            'reference' => 'PAY-'.fake()->randomNumber(6, true),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
