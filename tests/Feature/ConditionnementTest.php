<?php

use App\Models\Categorie;
use App\Models\Produit;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-produits', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-produits');
});

test('creer un produit genere automatiquement un conditionnement par defaut', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');
    $categorie = Categorie::factory()->create();

    $response = $this->actingAs($user)->post(route('produits.store'), [
        'nom' => 'Château Latour 2018',
        'categorie_id' => $categorie->id,
        'unite_base' => 'BOUTEILLE',
        'prix_achat' => 200000,
        'prix_vente' => 350000,
        'stock_min' => 2,
    ]);

    $response->assertRedirect(route('produits.index'));

    $produit = Produit::where('nom', 'Château Latour 2018')->first();
    expect($produit)->not->toBeNull();
    expect($produit->unite_base)->toBe('BOUTEILLE');

    $condDefaut = $produit->conditionnementParDefaut;
    expect($condDefaut)->not->toBeNull();
    expect($condDefaut->nom)->toBe('Bouteille');
    expect($condDefaut->quantite_unite_base)->toBe(1);
});

test('peut ajouter un conditionnement pack de 6 a un produit', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');
    $produit = Produit::factory()->create();

    $response = $this->actingAs($user)->post(route('produits.conditionnements.store', $produit), [
        'nom' => 'Pack de 6',
        'quantite_unite_base' => 6,
        'prix_achat' => 120000,
        'prix_vente' => 200000,
    ]);

    $response->assertRedirect(route('produits.index'));

    $this->assertDatabaseHas('conditionnements', [
        'produit_id' => $produit->id,
        'nom' => 'Pack de 6',
        'quantite_unite_base' => 6,
    ]);
});
