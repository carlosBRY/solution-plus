<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use Illuminate\Database\Seeder;

class FournisseurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fournisseurs = [
            [
                'nom' => 'Grands Vignobles de France',
                'telephone' => '+33 4 56 78 90 12',
                'email' => 'import@vignobles-france.fr',
                'adresse' => 'Route des Vins 33000 Bordeaux',
                'ville' => 'Bordeaux',
                'pays' => 'France',
                'observation' => 'Fournisseur principal de Bordeaux et Bourgogne.',
            ],
            [
                'nom' => 'Distillerie & Spiritueux de l\'Ouest',
                'telephone' => '+225 27 22 44 88 00',
                'email' => 'commercial@dso-ci.com',
                'adresse' => 'Zone Industrielle de Yopougon',
                'ville' => 'Abidjan',
                'pays' => 'Côte d\'Ivoire',
                'observation' => 'Distributeur officiel de Whiskies et Cognacs.',
            ],
            [
                'nom' => 'Maison Champagne & Bulles',
                'telephone' => '+33 3 26 00 11 22',
                'email' => 'orders@champagne-bulles.fr',
                'adresse' => 'Avenue de Champagne 51200 Épernay',
                'ville' => 'Épernay',
                'pays' => 'France',
                'observation' => 'Spécialiste champagnes haut de gamme.',
            ],
        ];

        foreach ($fournisseurs as $fournisseur) {
            Fournisseur::firstOrCreate(['nom' => $fournisseur['nom']], $fournisseur);
        }
    }
}
