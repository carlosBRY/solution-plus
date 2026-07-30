<?php

namespace Database\Factories;

use App\Models\Conditionnement;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conditionnement>
 */
class ConditionnementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'produit_id' => Produit::factory(),
            'nom' => 'Bouteille',
            'quantite_unite_base' => 1,
            'prix_achat' => $this->faker->randomFloat(2, 500, 50000),
            'prix_vente' => $this->faker->randomFloat(2, 1000, 100000),
            'code_barre' => $this->faker->optional()->ean13(),
            'is_achat' => true,
            'is_vente' => true,
            'is_par_defaut' => true,
        ];
    }

    /**
     * Un conditionnement en pack de 6.
     */
    public function packDe6(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => 'Pack de 6',
            'quantite_unite_base' => 6,
            'is_par_defaut' => false,
        ]);
    }

    /**
     * Une caisse de 12.
     */
    public function caisseDe12(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => 'Caisse de 12',
            'quantite_unite_base' => 12,
            'is_par_defaut' => false,
        ]);
    }
}
