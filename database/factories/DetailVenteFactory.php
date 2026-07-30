<?php

namespace Database\Factories;

use App\Models\DetailVente;
use App\Models\Produit;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailVente>
 */
class DetailVenteFactory extends Factory
{
    protected $model = DetailVente::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 10);
        $prix = fake()->randomFloat(2, 2000, 20000);

        return [
            'vente_id' => Vente::factory(),
            'produit_id' => Produit::factory(),
            'quantite' => $qty,
            'prix' => $prix,
            'remise' => 0,
            'total' => $qty * $prix,
        ];
    }
}
