<?php

namespace Database\Factories;

use App\Models\RetourVente;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RetourVente>
 */
class RetourVenteFactory extends Factory
{
    protected $model = RetourVente::class;

    public function definition(): array
    {
        return [
            'vente_id' => Vente::factory(),
            'user_id' => User::factory(),
            'motif' => fake()->randomElement(['Bouteille défectueuse', 'Erreur de commande', 'Bouchonnée']),
            'date' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
