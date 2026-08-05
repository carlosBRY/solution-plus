<?php

use App\Enums\ModePaiement;
use App\Enums\StatutVente;
use App\Models\Client;
use App\Models\CompteFinancier;
use App\Models\Conditionnement;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Services\VenteService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-clients', 'web');
    Permission::findOrCreate('gérer-ventes', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo(['gérer-clients', 'gérer-ventes']);

    CompteFinancier::firstOrCreate(
        ['mode' => 'ESPECES'],
        ['nom' => 'Caisse Espèces', 'solde_initial' => 0, 'solde_courant' => 0, 'actif' => true]
    );
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

test('un reglement de dette decremente le solde du client et credite le compte financier', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 150000]);
    $compteEsp = CompteFinancier::where('mode', 'ESPECES')->first();
    $soldeInitialCompte = (float) $compteEsp->solde_courant;

    $response = $this->actingAs($user)->post(route('clients.regler-dette', $client), [
        'montant' => 50000,
        'compte_financier_id' => $compteEsp->id,
    ]);

    $response->assertRedirect(route('clients.show', $client));

    // Le solde du client doit être réduit à 100 000 FCFA (150 000 - 50 000)
    expect($client->fresh()->solde)->toEqual(100000);

    // Le compte financier doit être crédité de 50 000 FCFA
    expect((float) $compteEsp->fresh()->solde_courant)->toEqual($soldeInitialCompte + 50000);

    $this->assertDatabaseHas('reglements_dettes', [
        'client_id' => $client->id,
        'compte_financier_id' => $compteEsp->id,
        'montant' => 50000,
    ]);
});

test('le remboursement de dette passe la vente a credit en statut PAYEE_CREDIT et remplit date_paiement_credit', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 0, 'plafond_credit' => 500000]);
    $produit = Produit::factory()->create(['prix_vente' => 30000]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 10]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 30000]);

    // 1. Créer une vente à crédit de 30 000 FCFA
    $venteService = app(VenteService::class);
    $vente = $venteService->creerVente($user, [
        'client_id' => $client->id,
        'mode_paiement' => ModePaiement::CREDIT->value,
        'montant_paye' => 0,
    ], [
        [
            'produit_id' => $produit->id,
            'conditionnement_id' => $cond->id,
            'quantite_conditionnement' => 1,
        ],
    ]);

    expect($vente->statut->value)->toBe(StatutVente::EN_ATTENTE->value);
    expect($vente->date_paiement_credit)->toBeNull();

    // 2. Le client rembourse 30 000 FCFA
    $compteEsp = CompteFinancier::where('mode', 'ESPECES')->first();
    $this->actingAs($user)->post(route('clients.regler-dette', $client), [
        'montant' => 30000,
        'compte_financier_id' => $compteEsp->id,
    ]);

    $venteFraiche = $vente->fresh();

    // 3. Vérifications : la vente passe en PAYEE_CREDIT et date_paiement_credit est enregistrée !
    expect($venteFraiche->statut->value)->toBe(StatutVente::PAYEE_CREDIT->value);
    expect($venteFraiche->date_paiement_credit)->not->toBeNull();
});

test('un gerant peut ajouter un credit a un client sans passer par une vente', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 10000]);

    $response = $this->actingAs($user)->post(route('clients.ajouter-credit', $client), [
        'montant' => 25000,
        'motif' => 'Prêt accordé au client',
    ]);

    $response->assertRedirect(route('clients.show', $client));

    // Le solde du client doit passer de 10 000 à 35 000 FCFA
    expect((float) $client->fresh()->solde)->toEqual(35000);

    $this->assertDatabaseHas('ajustements_credit', [
        'client_id' => $client->id,
        'type' => 'AJOUT',
        'montant' => 25000,
        'solde_avant' => 10000,
        'solde_apres' => 35000,
    ]);
});

test('l ajout de credit exige un motif obligatoire', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 0]);

    $response = $this->actingAs($user)->post(route('clients.ajouter-credit', $client), [
        'montant' => 5000,
        // motif manquant
    ]);

    $response->assertSessionHasErrors('motif');
});

test('un admin peut ajuster le solde d un client', function () {
    $adminRole = Role::findOrCreate('Administrateur', 'web');
    $adminRole->givePermissionTo(['gérer-clients']);

    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $client = Client::factory()->create(['solde' => 150000]);

    $response = $this->actingAs($admin)->post(route('clients.ajuster-credit', $client), [
        'nouveau_solde' => 80000,
        'motif' => 'Correction erreur double comptabilisation vente #123',
    ]);

    $response->assertRedirect(route('clients.show', $client));

    // Le solde doit être corrigé à 80 000 FCFA
    expect((float) $client->fresh()->solde)->toEqual(80000);

    $this->assertDatabaseHas('ajustements_credit', [
        'client_id' => $client->id,
        'type' => 'AJUSTEMENT',
        'solde_avant' => 150000,
        'solde_apres' => 80000,
    ]);
});

test('un gerant ne peut pas ajuster le solde d un client', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');

    $client = Client::factory()->create(['solde' => 50000]);

    $response = $this->actingAs($user)->post(route('clients.ajuster-credit', $client), [
        'nouveau_solde' => 0,
        'motif' => 'Tentative non autorisée',
    ]);

    $response->assertForbidden();

    // Le solde ne doit pas avoir changé
    expect((float) $client->fresh()->solde)->toEqual(50000);
});

test('un admin peut mettre le solde a zero pour annuler toute dette', function () {
    $adminRole = Role::findOrCreate('Administrateur', 'web');
    $adminRole->givePermissionTo(['gérer-clients']);

    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $client = Client::factory()->create(['solde' => 200000]);

    $response = $this->actingAs($admin)->post(route('clients.ajuster-credit', $client), [
        'nouveau_solde' => 0,
        'motif' => 'Annulation complète de la dette sur décision de la direction',
    ]);

    $response->assertRedirect(route('clients.show', $client));
    expect((float) $client->fresh()->solde)->toEqual(0);
});
