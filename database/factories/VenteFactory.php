<?php

namespace Database\Factories;

use App\Enums\StatutVente;
use App\Models\Client;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vente>
 */
class VenteFactory extends Factory
{
    protected $model = Vente::class;

    public function definition(): array
    {
        $sousTotal = fake()->randomFloat(2, 5000, 100000);
        $total = $sousTotal * 1.18;

        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'numero' => 'VNT-'.fake()->unique()->randomNumber(6, true),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'sous_total' => $sousTotal,
            'remise' => 0,
            'tva' => $sousTotal * 0.18,
            'total' => $total,
            'montant_paye' => $total,
            'monnaie' => 0,
            'statut' => fake()->randomElement(StatutVente::cases()),
        ];
    }
}
