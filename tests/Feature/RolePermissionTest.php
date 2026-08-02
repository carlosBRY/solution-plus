<?php

use App\Models\CompteFinancier;
use App\Models\Produit;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::findOrCreate('gérer-parametres', 'web');
    Permission::findOrCreate('gérer-roles', 'web');
    Permission::findOrCreate('ajuster-stock', 'web');
    Permission::findOrCreate('modifier-solde-compte', 'web');
});

test('un utilisateur avec gerer-roles peut voir la liste des roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['gérer-roles']);

    $response = $this->actingAs($user)->get(route('parametres.roles.index'));

    $response->assertStatus(200);
    $response->assertSee('Rôles');
});

test('un utilisateur peut créer un nouveau rôle avec des permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['gérer-roles']);

    $response = $this->actingAs($user)->post(route('parametres.roles.store'), [
        'name' => 'Superviseur Nuit',
        'permissions' => ['ajuster-stock', 'modifier-solde-compte'],
    ]);

    $response->assertRedirect(route('parametres.roles.index'));

    $this->assertDatabaseHas('roles', ['name' => 'Superviseur Nuit']);

    $role = Role::findByName('Superviseur Nuit');
    expect($role->hasPermissionTo('ajuster-stock'))->toBeTrue();
    expect($role->hasPermissionTo('modifier-solde-compte'))->toBeTrue();
});

test('un utilisateur peut modifier les permissions d un rôle', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['gérer-roles']);

    $role = Role::create(['name' => 'Contrôleur', 'guard_name' => 'web']);
    $role->givePermissionTo(['ajuster-stock']);

    $response = $this->actingAs($user)->put(route('parametres.roles.update', $role), [
        'name' => 'Contrôleur Général',
        'permissions' => ['modifier-solde-compte'],
    ]);

    $response->assertRedirect(route('parametres.roles.index'));

    $role->refresh();
    expect($role->name)->toBe('Contrôleur Général');
    expect($role->hasPermissionTo('ajuster-stock'))->toBeFalse();
    expect($role->hasPermissionTo('modifier-solde-compte'))->toBeTrue();
});

test('la permission ajuster-stock est strictement contrôlée', function () {
    Permission::findOrCreate('gérer-stocks', 'web');

    $userSansPerm = User::factory()->create();
    $userSansPerm->givePermissionTo(['gérer-stocks']);

    $userAvecPerm = User::factory()->create();
    $userAvecPerm->givePermissionTo(['gérer-stocks', 'ajuster-stock']);

    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE']);

    // Tentative d'ajustement sans permission ajuster-stock -> 403 Forbidden
    $responseUnauth = $this->actingAs($userSansPerm)->post(route('stocks.ajuster', $produit), [
        'quantite' => 50,
        'motif' => 'Ajustement inventaire',
    ]);
    $responseUnauth->assertStatus(403);

    // Ajustement avec la permission -> Succès
    $responseAuth = $this->actingAs($userAvecPerm)->post(route('stocks.ajuster', $produit), [
        'quantite' => 50,
        'motif' => 'Ajustement inventaire autorisé',
    ]);
    $responseAuth->assertRedirect(route('stocks.index'));
});

test('la permission modifier-solde-compte est strictement contrôlée', function () {
    Permission::findOrCreate('gérer-parametres', 'web');

    $userSansPerm = User::factory()->create();
    $userSansPerm->givePermissionTo(['gérer-parametres']);

    $userAvecPerm = User::factory()->create();
    $userAvecPerm->givePermissionTo(['gérer-parametres', 'modifier-solde-compte']);

    $compte = CompteFinancier::create([
        'nom' => 'Compte Test',
        'mode' => 'TEST_MODE',
        'solde_initial' => 100000,
        'solde_courant' => 100000,
        'actif' => true,
    ]);

    // Sans permission -> 403
    $responseUnauth = $this->actingAs($userSansPerm)->post(route('parametres.comptes.initialiser', $compte), [
        'solde' => 500000,
    ]);
    $responseUnauth->assertStatus(403);

    // Avec permission -> 302 OK
    $responseAuth = $this->actingAs($userAvecPerm)->post(route('parametres.comptes.initialiser', $compte), [
        'solde' => 500000,
    ]);
    $responseAuth->assertRedirect(route('parametres.comptes.index'));

    expect((float) $compte->refresh()->solde_courant)->toBe(500000.0);
});
