<?php

namespace Database\Factories;

use App\Enums\StatutApprovisionnement;
use App\Models\Approvisionnement;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Approvisionnement>
 */
class ApprovisionnementFactory extends Factory
{
    protected $model = Approvisionnement::class;

    public function definition(): array
    {
        $montant = fake()->randomFloat(2, 50000, 500000);

        return [
            'fournisseur_id' => Fournisseur::factory(),
            'user_id' => User::factory(),
            'numero' => 'APP-'.fake()->unique()->randomNumber(6, true),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'montant' => $montant,
            'remise' => 0,
            'tva' => $montant * 0.18,
            'total' => $montant * 1.18,
            'statut' => fake()->randomElement(StatutApprovisionnement::cases()),
        ];
    }
}
