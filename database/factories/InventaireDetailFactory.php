<?php

namespace Database\Factories;

use App\Models\Inventaire;
use App\Models\InventaireDetail;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventaireDetail>
 */
class InventaireDetailFactory extends Factory
{
    protected $model = InventaireDetail::class;

    public function definition(): array
    {
        $theo = fake()->numberBetween(10, 50);
        $phys = fake()->numberBetween(8, 52);

        return [
            'inventaire_id' => Inventaire::factory(),
            'produit_id' => Produit::factory(),
            'stock_theorique' => $theo,
            'stock_physique' => $phys,
            'ecart' => $phys - $theo,
        ];
    }
}
