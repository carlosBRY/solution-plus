<?php

namespace Database\Factories;

use App\Enums\StatutCaisse;
use App\Models\Caisse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caisse>
 */
class CaisseFactory extends Factory
{
    protected $model = Caisse::class;

    public function definition(): array
    {
        $ouverture = fake()->dateTimeBetween('-1 week', 'now');
        $initial = fake()->randomFloat(2, 50000, 150000);

        return [
            'user_id' => User::factory(),
            'date_ouverture' => $ouverture,
            'date_fermeture' => null,
            'solde_initial' => $initial,
            'solde_final' => null,
            'ecart' => 0,
            'statut' => StatutCaisse::OUVERTE,
        ];
    }
}
