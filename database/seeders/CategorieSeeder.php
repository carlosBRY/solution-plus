<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'nom' => 'Vins Rouges',
                'description' => 'Grands crus, Bordeaux, Bourgogne, Vallée du Rhône et vins de table rouges.',
            ],
            [
                'nom' => 'Vins Blancs',
                'description' => 'Vins secs, moelleux, Chablis, Sauvignon et Riesling.',
            ],
            [
                'nom' => 'Vins Rosés',
                'description' => 'Côtes de Provence, Rosé d\'Anjou et vins rosés de rafraîchissement.',
            ],
            [
                'nom' => 'Champagnes & Effervescents',
                'description' => 'Brut, Blanc de Blancs, Prosecco et Cava.',
            ],
            [
                'nom' => 'Spiritueux & Cognacs',
                'description' => 'Whisky, Rhum, Cognac, Vodka et Gin.',
            ],
        ];

        foreach ($categories as $cat) {
            Categorie::firstOrCreate(['nom' => $cat['nom']], $cat);
        }
    }
}
