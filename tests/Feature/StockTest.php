<?php

use App\Enums\MouvementType;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Services\StockService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-stocks', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-stocks');
});

test('la page stocks index est accessible a un utilisateur autorise', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->get(route('stocks.index'));
    $response->assertStatus(200);
    $response->assertSee('Gestion du Stock');
});

test('un utilisateur non autorise ne peut pas acceder au stock', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('stocks.index'));
    $response->assertStatus(403);
});

test('ajusterStock met a jour la quantite et cree un mouvement', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 100]);

    $service = app(StockService::class);
    $stock = $service->ajusterStock($produit, $user, 75, 'Correction inventaire');

    expect($stock->quantite)->toBe(75);

    $this->assertDatabaseHas('mouvements_stock', [
        'produit_id' => $produit->id,
        'user_id' => $user->id,
        'type' => MouvementType::AJUSTEMENT->value,
        'stock_avant' => 100,
        'stock_apres' => 75,
    ]);
});

test('l ajustement via route HTTP met a jour le stock', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 50]);

    $response = $this->actingAs($user)->post(route('stocks.ajuster', $produit), [
        'quantite' => 80,
        'motif' => 'Ajustement suite à réception non enregistrée',
    ]);

    $response->assertRedirect(route('stocks.index'));
    $response->assertSessionHas('success');

    expect(Stock::where('produit_id', $produit->id)->value('quantite'))->toBe(80);
});

test('la page des mouvements de stock est accessible a un utilisateur autorise', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->get(route('stocks.mouvements'));
    $response->assertStatus(200);
    $response->assertSee('Journal des Mouvements de Stock');
});
