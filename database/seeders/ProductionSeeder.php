<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class ProductionSeeder extends Seeder
{
    /**
     * Exécute les seeders de PRODUCTION (Données essentielles uniquement).
     */
    public function run(): void
    {
        // 1. Réinitialisation du cache des permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Initialisation des Rôles et Permissions
        $this->call([
            RoleSeeder::class,
            ParametreSeeder::class,
            CompteFinancierSeeder::class,
        ]);

        // 3. Création du compte Super Administrateur principal
        $admin = User::firstOrCreate(
            ['email' => 'admin@solutionplus.ci'],
            [
                'name' => 'Super Administrateur',
                'password' => Hash::make('password'),
                'telephone' => '+225 07 00 00 00 00',
                'adresse' => 'Abidjan, Côte d\'Ivoire',
                'actif' => true,
            ]
        );

        $admin->assignRole('Administrateur');
    }
}
