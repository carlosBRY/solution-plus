<?php

namespace Database\Factories;

use App\Models\Parametre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Parametre>
 */
class ParametreFactory extends Factory
{
    protected $model = Parametre::class;

    public function definition(): array
    {
        return [
            'nom_cave' => 'Cave Prestige d\'Or',
            'telephone' => '+225 07 00 00 00 00',
            'adresse' => 'Abidjan, Cocody Riviera',
            'email' => 'contact@caveprestige.com',
            'logo' => null,
            'devise' => 'FCFA',
            'tva' => 18.00,
            'stock_min_global' => 5,
            'message_ticket' => 'Merci de votre visite et à bientôt dans notre cave !',
        ];
    }
}
