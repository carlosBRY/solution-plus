<?php

use App\Models\Parametre;
use App\Models\Produit;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-parametres', 'web');
    $role = Role::findOrCreate('Administrateur', 'web');
    $role->givePermissionTo('gérer-parametres');
});

test('administrateur peut voir la page des parametres generaux', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $response = $this->actingAs($admin)->get(route('parametres.index'));

    $response->assertOk();
});

test('administrateur peut mettre a jour les parametres generaux', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $parametre = Parametre::first() ?? Parametre::create(['nom_cave' => 'Cave Test']);

    $response = $this->actingAs($admin)->put(route('parametres.update-general'), [
        'nom_cave' => 'Cave Royale prestige',
        'devise' => 'EUR',
        'tva' => 20.00,
        'stock_min_global' => 10,
    ]);

    $response->assertRedirect(route('parametres.index'));
    $this->assertDatabaseHas('parametres', [
        'nom_cave' => 'Cave Royale prestige',
        'devise' => 'EUR',
    ]);
});

test('administrateur peut acceder a la gestion des conditionnements dans parametres', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $response = $this->actingAs($admin)->get(route('parametres.conditionnements'));

    $response->assertOk();
});

test('administrateur peut ajouter un conditionnement depuis la gestion des parametres', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');
    $produit = Produit::factory()->create();

    $response = $this->actingAs($admin)->post(route('parametres.conditionnements.store'), [
        'produit_id' => $produit->id,
        'nom' => 'Caisse Bois de 24',
        'quantite_unite_base' => 24,
        'prix_achat' => 500000,
        'prix_vente' => 750000,
    ]);

    $response->assertRedirect(route('parametres.conditionnements'));
    $this->assertDatabaseHas('conditionnements', [
        'produit_id' => $produit->id,
        'nom' => 'Caisse Bois de 24',
        'quantite_unite_base' => 24,
    ]);
});
