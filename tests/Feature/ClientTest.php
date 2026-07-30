<?php

use App\Enums\ModePaiement;
use App\Models\Client;
use App\Models\Conditionnement;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-clients', 'web');
    Permission::findOrCreate('gérer-ventes', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo(['gérer-clients', 'gérer-ventes']);
});

test('peut creer un client et consulter sa liste', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $response = $this->actingAs($user)->post(route('clients.store'), [
        'nom' => 'Kouadio Paul',
        'telephone' => '+225 0707070707',
        'email' => 'paul@example.ci',
        'plafond_credit' => 1000000,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('clients', [
        'nom' => 'Kouadio Paul',
        'plafond_credit' => 1000000,
    ]);
});

test('une vente a credit incremente le solde debiteur du client', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 0, 'plafond_credit' => 500000]);
    $produit = Produit::factory()->create(['prix_vente' => 50000]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 10]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 50000]);

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'client_id' => $client->id,
        'mode_paiement' => ModePaiement::CREDIT->value,
        'montant_paye' => 0, // Crédit total de 50 000 FCFA
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
                'prix' => 50000,
            ],
        ],
    ]);

    $response->assertRedirect();

    // La dette du client doit être à 50 000 FCFA
    expect($client->fresh()->solde)->toEqual(50000);
});

test('bloque une vente a credit si le solde depasse le plafond autorise', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 90000, 'plafond_credit' => 100000]);
    $produit = Produit::factory()->create(['prix_vente' => 50000]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 10]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 50000]);

    // Tentative d'ajouter 50 000 de dette -> Solde passerait à 140 000 > Plafond 100 000
    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'client_id' => $client->id,
        'mode_paiement' => ModePaiement::CREDIT->value,
        'montant_paye' => 0,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
                'prix' => 50000,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    // Le solde du client ne doit pas avoir changé
    expect($client->fresh()->solde)->toEqual(90000);
});

test('un reglement de dette decremente le solde du client', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 150000]);

    $response = $this->actingAs($user)->post(route('clients.regler-dette', $client), [
        'montant' => 50000,
        'mode' => 'ESPECES',
    ]);

    $response->assertRedirect(route('clients.show', $client));

    // Le solde doit être réduit à 100 000 FCFA (150 000 - 50 000)
    expect($client->fresh()->solde)->toEqual(100000);

    $this->assertDatabaseHas('reglements_dettes', [
        'client_id' => $client->id,
        'montant' => 50000,
    ]);
});
