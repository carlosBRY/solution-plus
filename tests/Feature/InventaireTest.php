<?php

use App\Enums\MouvementType;
use App\Models\Inventaire;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Services\InventaireService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-inventaires', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-inventaires');
});

test('la page inventaires index est accessible a un utilisateur autorise', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->get(route('inventaires.index'));
    $response->assertStatus(200);
    $response->assertSee('Historique des Inventaires');
});

test('un utilisateur non autorise ne peut pas acceder aux inventaires', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('inventaires.index'));
    $response->assertStatus(403);
});

test('creerInventaire cree le bon inventaire et regularise les stocks', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 100]);

    $service = app(InventaireService::class);
    $inventaire = $service->creerInventaire($user, [
        ['produit_id' => $produit->id, 'stock_physique' => 85],
    ], 'Test inventaire service');

    expect($inventaire)->toBeInstanceOf(Inventaire::class);
    expect($inventaire->inventaireDetails)->toHaveCount(1);

    $detail = $inventaire->inventaireDetails->first();
    expect($detail->stock_theorique)->toBe(100);
    expect($detail->stock_physique)->toBe(85);
    expect($detail->ecart)->toBe(-15);

    // Stock mis à jour
    expect(Stock::where('produit_id', $produit->id)->value('quantite'))->toBe(85);

    // Mouvement d'ajustement créé
    $this->assertDatabaseHas('mouvements_stock', [
        'produit_id' => $produit->id,
        'user_id' => $user->id,
        'type' => MouvementType::AJUSTEMENT->value,
        'stock_avant' => 100,
        'stock_apres' => 85,
    ]);
});

test('aucun mouvement cree si stock physique egal stock theorique', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 50]);

    $service = app(InventaireService::class);
    $service->creerInventaire($user, [
        ['produit_id' => $produit->id, 'stock_physique' => 50],
    ]);

    $this->assertDatabaseMissing('mouvements_stock', [
        'produit_id' => $produit->id,
        'type' => MouvementType::AJUSTEMENT->value,
    ]);
});

test('un inventaire soumis via HTTP redirige vers le rapport', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 30]);

    $response = $this->actingAs($user)->post(route('inventaires.store'), [
        'observation' => 'Inventaire de test HTTP',
        'items' => [
            ['produit_id' => $produit->id, 'stock_physique' => 25],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('inventaires', [
        'user_id' => $user->id,
        'observation' => 'Inventaire de test HTTP',
    ]);
});
