<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@caveprestige.com'],
            [
                'name' => 'Administrateur Principal',
                'password' => Hash::make('password'),
                'telephone' => '+225 07 01 02 03 04',
                'adresse' => 'Abidjan Plateau',
                'actif' => true,
            ]
        );
        $admin->assignRole('Administrateur');

        $gerant = User::firstOrCreate(
            ['email' => 'gerant@caveprestige.com'],
            [
                'name' => 'Jean-Marc Kouassi',
                'password' => Hash::make('password'),
                'telephone' => '+225 05 11 22 33 44',
                'adresse' => 'Cocody Angré',
                'actif' => true,
            ]
        );
        $gerant->assignRole('Gérant');

        $caissier = User::firstOrCreate(
            ['email' => 'caisse@caveprestige.com'],
            [
                'name' => 'Awa Traoré',
                'password' => Hash::make('password'),
                'telephone' => '+225 01 44 55 66 77',
                'adresse' => 'Marcory Zone 4',
                'actif' => true,
            ]
        );
        $caissier->assignRole('Caissier');
    }
}
