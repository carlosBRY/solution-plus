<?php

namespace Database\Factories;

use App\Enums\DeteriorationCause;
use App\Models\Conditionnement;
use App\Models\Deterioration;
use App\Models\DeteriorationDetail;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeteriorationDetail>
 */
class DeteriorationDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantiteCond = $this->faker->numberBetween(1, 5);
        $coeff = 1;
        $qteBase = $quantiteCond * $coeff;
        $coutUnit = $this->faker->randomFloat(2, 1000, 50000);

        return [
            'deterioration_id' => Deterioration::factory(),
            'produit_id' => Produit::factory(),
            'conditionnement_id' => Conditionnement::factory(),
            'quantite_conditionnement' => $quantiteCond,
            'coefficient_conversion' => $coeff,
            'quantite_unite_base' => $qteBase,
            'cout_unitaire' => $coutUnit,
            'valeur_perte' => $qteBase * $coutUnit,
            'cause' => DeteriorationCause::CASSE,
            'observation' => $this->faker->optional()->sentence(),
        ];
    }
}
