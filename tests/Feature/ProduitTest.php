<?php

use App\Enums\MouvementType;
use App\Models\Categorie;
use App\Models\Conditionnement;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-produits', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-produits');
});

test('user with permission can view products list', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->get(route('produits.index'));

    $response->assertOk();
});

test('user can create a product with initial stock', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');
    $categorie = Categorie::factory()->create();

    $response = $this->actingAs($user)->post(route('produits.store'), [
        'nom' => 'Château Margaux 2015',
        'categorie_id' => $categorie->id,
        'unite_base' => 'BOUTEILLE',
        'prix_achat' => 150000,
        'prix_vente' => 250000,
        'stock_min' => 5,
        'quantite_initiale' => 12,
    ]);

    $response->assertRedirect(route('produits.index'));
    $this->assertDatabaseHas('produits', ['nom' => 'Château Margaux 2015']);
    $this->assertDatabaseHas('stocks', ['quantite' => 12]);
    $this->assertDatabaseHas('mouvements_stock', [
        'type' => MouvementType::STOCK_INITIAL->value,
        'quantite' => 12,
    ]);
});

test('calcule correctement le stock formate en caisses et bouteilles restantes', function () {
    $produit = Produit::factory()->create(['unite_base' => 'Bouteille']);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 50]);

    Conditionnement::create([
        'produit_id' => $produit->id,
        'nom' => 'Caisse de 12',
        'quantite_unite_base' => 12,
        'prix_vente' => 12000,
    ]);

    expect($produit->refresh()->stock_formate)->toBe('4 Caisse de 12 + 2 bts (50 Bouteilles)');
});
