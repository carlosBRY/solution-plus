<?php

namespace Database\Seeders;

use App\Models\Parametre;
use Illuminate\Database\Seeder;

class ParametreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Parametre::firstOrCreate(
            ['nom_cave' => 'Cave Prestige d\'Or'],
            [
                'telephone' => '+225 27 22 00 11 22',
                'adresse' => 'Boulevard Hassan II, Cocody Les Deux-Plateaux, Abidjan',
                'email' => 'contact@caveprestige.ci',
                'logo' => 'logos/cave_prestige.png',
                'devise' => 'FCFA',
                'tva' => 18.00,
                'stock_min_global' => 5,
                'message_ticket' => 'Merci de votre confiance ! Nos grands crus sont à déguster avec modération.',
            ]
        );
    }
}
