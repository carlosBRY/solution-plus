<?php

use App\Models\Depense;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-depenses', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-depenses');
});

test('un utilisateur autorise peut enregistrer une depense', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->post(route('depenses.store'), [
        'libelle' => 'Facture électricité',
        'reference_piece' => 'FACT-2026-0789',
        'categorie' => 'Factures',
        'montant' => 35000,
        'observation' => 'Mois de Juillet',
    ]);

    $response->assertRedirect(route('depenses.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('depenses', [
        'user_id' => $user->id,
        'libelle' => 'Facture électricité',
        'reference_piece' => 'FACT-2026-0789',
        'categorie' => 'Factures',
        'montant' => 35000,
    ]);
});

test('peut modifier une depense existante', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $depense = Depense::create([
        'user_id' => $user->id,
        'libelle' => 'Achat café',
        'reference_piece' => 'REÇU-001',
        'categorie' => 'Fournitures',
        'montant' => 5000,
        'date' => now(),
    ]);

    $response = $this->actingAs($user)->put(route('depenses.update', $depense), [
        'libelle' => 'Achat café et sucre',
        'reference_piece' => 'REÇU-001-MODIF',
        'categorie' => 'Fournitures',
        'montant' => 7500,
    ]);

    $response->assertRedirect(route('depenses.index'));

    $this->assertDatabaseHas('depenses', [
        'id' => $depense->id,
        'libelle' => 'Achat café et sucre',
        'reference_piece' => 'REÇU-001-MODIF',
        'montant' => 7500,
    ]);
});

test('peut supprimer une depense', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $depense = Depense::create([
        'user_id' => $user->id,
        'libelle' => 'Transport coursier',
        'reference_piece' => 'PIECE-99',
        'categorie' => 'Transport',
        'montant' => 3000,
        'date' => now(),
    ]);

    $response = $this->actingAs($user)->delete(route('depenses.destroy', $depense));

    $response->assertRedirect(route('depenses.index'));

    $this->assertDatabaseMissing('depenses', [
        'id' => $depense->id,
    ]);
});

test('peut filtrer la liste des depenses par categorie', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    Depense::create([
        'user_id' => $user->id,
        'libelle' => 'Transport',
        'reference_piece' => 'P1',
        'categorie' => 'Transport',
        'montant' => 2000,
        'date' => now(),
    ]);

    Depense::create([
        'user_id' => $user->id,
        'libelle' => 'Ampoules bureau',
        'reference_piece' => 'P2',
        'categorie' => 'Maintenance',
        'montant' => 4000,
        'date' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('depenses.index', ['categorie' => 'Maintenance']));

    $response->assertStatus(200);
    $response->assertSee('Ampoules bureau');
    $response->assertDontSee('Transport coursier');
});
