<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'nom' => 'Hôtel Le Palmier Prestige',
                'telephone' => '+225 27 21 00 11 22',
                'email' => 'achats@palmierprestige.ci',
                'adresse' => 'Boulevard de la République, Plateau',
                'solde' => 0,
                'plafond_credit' => 2000000.00,
            ],
            [
                'nom' => 'Restaurant L\'Epicurien',
                'telephone' => '+225 07 88 99 00 11',
                'email' => 'cave@lepicurien-abidjan.com',
                'adresse' => 'Rue des Jardins, Deux Plateaux',
                'solde' => 150000.00,
                'plafond_credit' => 1000000.00,
            ],
            [
                'nom' => 'M. Philippe Dupont',
                'telephone' => '+225 05 44 33 22 11',
                'email' => 'p.dupont@email.com',
                'adresse' => 'Zone 4C, Marcory',
                'solde' => 0,
                'plafond_credit' => 500000.00,
            ],
        ];

        foreach ($clients as $client) {
            Client::firstOrCreate(['nom' => $client['nom']], $client);
        }
    }
}
