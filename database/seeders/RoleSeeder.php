<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Modules de base
            'voir-dashboard',
            'gérer-utilisateurs',
            'gérer-produits',
            'gérer-categories',
            'gérer-fournisseurs',
            'gérer-clients',
            'gérer-ventes',
            'gérer-approvisionnements',
            'gérer-stocks',
            'gérer-inventaires',
            'gérer-caisses',
            'gérer-depenses',
            'gérer-parametres',
            'gérer-casiers',
            // Fonctionnalités sensibles & sécurisées
            'ajuster-stock',
            'modifier-solde-compte',
            'gérer-roles',
            'valider-détérioration',
            'annuler-vente',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Create Roles & Assign Permissions
        $admin = Role::findOrCreate('Administrateur', 'web');
        $admin->givePermissionTo(Permission::all());

        $gerant = Role::findOrCreate('Gérant', 'web');
        $gerant->givePermissionTo([
            'voir-dashboard',
            'gérer-produits',
            'gérer-categories',
            'gérer-fournisseurs',
            'gérer-clients',
            'gérer-ventes',
            'gérer-approvisionnements',
            'gérer-stocks',
            'gérer-inventaires',
            'gérer-caisses',
            'gérer-depenses',
            'gérer-casiers',
            'ajuster-stock',
            'valider-détérioration',
        ]);

        $caissier = Role::findOrCreate('Caissier', 'web');
        $caissier->givePermissionTo([
            'voir-dashboard',
            'gérer-clients',
            'gérer-ventes',
            'gérer-caisses',
        ]);

        $stockeur = Role::findOrCreate('Gestionnaire de Stock', 'web');
        $stockeur->givePermissionTo([
            'voir-dashboard',
            'gérer-produits',
            'gérer-stocks',
            'gérer-inventaires',
            'gérer-approvisionnements',
            'ajuster-stock',
        ]);
    }
}
