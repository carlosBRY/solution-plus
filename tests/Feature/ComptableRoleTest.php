<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('un comptable peut acceder en consultation aux modules financiers', function () {
    $comptableUser = User::factory()->create();
    $comptableUser->assignRole('Comptable');

    $this->actingAs($comptableUser)->get(route('dashboard'))->assertOk();
    $this->actingAs($comptableUser)->get(route('comptes.index'))->assertOk();
    $this->actingAs($comptableUser)->get(route('caisses.index'))->assertOk();
    $this->actingAs($comptableUser)->get(route('ventes.index'))->assertOk();
    $this->actingAs($comptableUser)->get(route('approvisionnements.index'))->assertOk();
    $this->actingAs($comptableUser)->get(route('depenses.index'))->assertOk();
    $this->actingAs($comptableUser)->get(route('clients.index'))->assertOk();
    $this->actingAs($comptableUser)->get(route('fournisseurs.index'))->assertOk();
});

test('un comptable est bloque lors des tentatives d actions d ecriture ou de modification', function () {
    $comptableUser = User::factory()->create();
    $comptableUser->assignRole('Comptable');

    // Tentative d'enregistrement de dépense
    $this->actingAs($comptableUser)->post(route('depenses.store'), [
        'motif' => 'Dépense non autorisée',
        'montant' => 5000,
    ])->assertForbidden();

    // Tentative de transfert entre comptes
    $this->actingAs($comptableUser)->post(route('comptes.transferer'), [
        'montant' => 1000,
    ])->assertForbidden();

    // Tentative de création de vente
    $this->actingAs($comptableUser)->get(route('ventes.create'))->assertForbidden();
    $this->actingAs($comptableUser)->post(route('ventes.store'), [])->assertForbidden();

    // Tentative de création d'approvisionnement
    $this->actingAs($comptableUser)->get(route('approvisionnements.create'))->assertForbidden();
    $this->actingAs($comptableUser)->post(route('approvisionnements.store'), [])->assertForbidden();

    // Tentative d'accès aux modules d'administration
    $this->actingAs($comptableUser)->get(route('parametres.index'))->assertForbidden();
    $this->actingAs($comptableUser)->get(route('parametres.users.index'))->assertForbidden();
    $this->actingAs($comptableUser)->get(route('parametres.roles.index'))->assertForbidden();
});
