<?php

use App\Models\Fournisseur;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-fournisseurs', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-fournisseurs');
});

test('utilisateur avec permission peut consulter et creer un fournisseur', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->get(route('fournisseurs.index'));
    $response->assertOk();

    $responseStore = $this->actingAs($user)->post(route('fournisseurs.store'), [
        'nom' => 'Maison Ruinart',
        'telephone' => '+33 3 26 77 51 51',
        'email' => 'contact@ruinart.fr',
        'ville' => 'Reims',
        'pays' => 'France',
    ]);

    $responseStore->assertRedirect();
    $this->assertDatabaseHas('fournisseurs', [
        'nom' => 'Maison Ruinart',
        'ville' => 'Reims',
    ]);
});

test('peut afficher la fiche d un fournisseur avec son historique d approvisionnements', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');
    $fournisseur = Fournisseur::factory()->create();

    $response = $this->actingAs($user)->get(route('fournisseurs.show', $fournisseur));

    $response->assertOk();
});
