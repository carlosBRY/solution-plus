<?php

namespace Database\Seeders;

use App\Models\CompteFinancier;
use Illuminate\Database\Seeder;

class CompteFinancierSeeder extends Seeder
{
    /**
     * Crée les comptes financiers par défaut.
     */
    public function run(): void
    {
        $comptes = [
            [
                'nom' => 'Caisse Espèces',
                'mode' => 'ESPECES',
                'description' => 'Caisse physique — billets et pièces',
            ],
            [
                'nom' => 'Orange Money',
                'mode' => 'ORANGE_MONEY',
                'description' => 'Paiements et encaissements via Orange Money',
            ],
            [
                'nom' => 'Moov Money',
                'mode' => 'MOOV_MONEY',
                'description' => 'Paiements et encaissements via Moov Money',
            ],
            [
                'nom' => 'Compte Bancaire',
                'mode' => 'VIREMENT',
                'description' => 'Virements et opérations bancaires',
            ],
        ];

        foreach ($comptes as $compte) {
            CompteFinancier::firstOrCreate(
                ['mode' => $compte['mode']],
                [
                    'nom' => $compte['nom'],
                    'solde_initial' => 0,
                    'solde_courant' => 0,
                    'actif' => true,
                    'description' => $compte['description'],
                ]
            );
        }
    }
}
