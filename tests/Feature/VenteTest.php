<?php

use App\Enums\ModePaiement;
use App\Enums\MouvementType;
use App\Enums\StatutVente;
use App\Models\Client;
use App\Models\CompteFinancier;
use App\Models\Conditionnement;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Models\Vente;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-ventes', 'web');
    $role = Role::findOrCreate('Vendeur', 'web');
    $role->givePermissionTo('gérer-ventes');

    Role::findOrCreate('Administrateur', 'web')->givePermissionTo('gérer-ventes');

    CompteFinancier::firstOrCreate(
        ['mode' => 'ESPECES'],
        ['nom' => 'Caisse Espèces', 'solde_initial' => 0, 'solde_courant' => 0, 'actif' => true]
    );
});

test('peut enregistrer une vente et decrementer le stock correctement', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE', 'prix_vente' => 25000]);
    $stock = Stock::create(['produit_id' => $produit->id, 'quantite' => 50]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 25000]);

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 75000,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 3, // 3 bouteilles x 1 = 3 unités de base
                'prix' => 25000,
            ],
        ],
    ]);

    $response->assertRedirect();

    // Stock = 50 - 3 = 47
    expect($stock->fresh()->quantite)->toBe(47);

    $this->assertDatabaseHas('mouvements_stock', [
        'produit_id' => $produit->id,
        'type' => MouvementType::SORTIE->value,
        'quantite' => -3,
        'stock_avant' => 50,
        'stock_apres' => 47,
    ]);

    $this->assertDatabaseHas('ventes', [
        'statut' => StatutVente::PAYEE->value,
    ]);
});

test('vente echoue si stock insuffisant', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE']);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 2]);
    $cond = Conditionnement::factory()->caisseDe12()->create(['produit_id' => $produit->id, 'prix_vente' => 300000]);

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 300000,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
                'prix' => 300000,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('vente a credit exige un client enregistre', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 10]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 10000]);

    // Sans client
    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::CREDIT->value,
        'client_id' => null,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['client_id']);
});

test('vente a credit decremente le stock enregistre la dette du client et n impacte pas les comptes de caisse', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $client = Client::factory()->create(['solde' => 0, 'plafond_credit' => 100000]);
    $produit = Produit::factory()->create();
    $stock = Stock::create(['produit_id' => $produit->id, 'quantite' => 20]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 15000]);

    $compteEsp = CompteFinancier::where('mode', 'ESPECES')->first();
    $soldeInitialCompte = (float) $compteEsp->solde_courant;

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::CREDIT->value,
        'client_id' => $client->id,
        'montant_paye' => 0,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 2, // 2 x 15000 = 30000 FCFA
            ],
        ],
    ]);

    $response->assertRedirect();

    // 1. Stock décrémenté: 20 - 2 = 18
    expect($stock->fresh()->quantite)->toBe(18);

    // 2. Client solde (dette) augmenté de 30000
    expect((float) $client->fresh()->solde)->toBe(30000.0);

    // 3. Solde caisse non modifié !
    expect((float) $compteEsp->fresh()->solde_courant)->toBe($soldeInitialCompte);
});

test('vente au comptant echoue si montant paye est inferieur au total', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 10]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 20000]);

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 10000, // Insuffisant pour 20000 FCFA
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

// === Tests de la règle métier : Stock Minimum ===

test('non admin ne peut pas vendre un produit dont le stock est inferieur ou egal au stock minimum', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');

    // Produit avec stock_min = 5 et stock actuel = 4 (sous le seuil)
    $produit = Produit::factory()->create([
        'unite_base' => 'BOUTEILLE',
        'prix_vente' => 10000,
        'stock_min' => 5,
        'actif' => true,
    ]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 4]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 10000]);

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 10000,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    // Le stock ne doit pas avoir bougé
    expect(Stock::where('produit_id', $produit->id)->first()->quantite)->toBe(4);
});

test('admin peut vendre un produit sous stock minimum avec confirmation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    // Produit avec stock_min = 5 et stock actuel = 3 (sous le seuil)
    $produit = Produit::factory()->create([
        'unite_base' => 'BOUTEILLE',
        'prix_vente' => 10000,
        'stock_min' => 5,
        'actif' => true,
    ]);
    $stock = Stock::create(['produit_id' => $produit->id, 'quantite' => 3]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 10000]);

    $response = $this->actingAs($admin)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 20000,
        'confirmer_vente_stock_min' => 1,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 2,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionMissing('error');

    // Stock décrémenté de 3 à 1
    expect($stock->fresh()->quantite)->toBe(1);
});

test('admin ne peut pas vendre un produit sous stock minimum sans confirmation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $produit = Produit::factory()->create([
        'unite_base' => 'BOUTEILLE',
        'prix_vente' => 10000,
        'stock_min' => 5,
        'actif' => true,
    ]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 3]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 10000]);

    // Sans cocher confirmer_vente_stock_min
    $response = $this->actingAs($admin)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 10000,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    // Le stock ne doit pas avoir bougé
    expect(Stock::where('produit_id', $produit->id)->first()->quantite)->toBe(3);
});

test('admin ne peut pas vendre plus que le stock disponible meme avec confirmation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Administrateur');

    $produit = Produit::factory()->create([
        'unite_base' => 'BOUTEILLE',
        'prix_vente' => 10000,
        'stock_min' => 5,
        'actif' => true,
    ]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 2]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 10000]);

    // Tente de vendre 5 bouteilles alors qu'il n'en reste que 2
    $response = $this->actingAs($admin)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 50000,
        'confirmer_vente_stock_min' => 1,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 5, // 5 > 2 (stock disponible)
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    // Le stock ne doit pas avoir bougé
    expect(Stock::where('produit_id', $produit->id)->first()->quantite)->toBe(2);
});

test('enregistre les informations du client comptant pour le reçu', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE', 'prix_vente' => 5000]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 20]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 5000]);

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 5000,
        'client_comptant_nom' => 'Kouamé',
        'client_comptant_prenom' => 'Michel',
        'client_comptant_contact' => '0708091011',
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
                'prix' => 5000,
            ],
        ],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('ventes', [
        'client_id' => null,
        'client_comptant_nom' => 'Kouamé',
        'client_comptant_prenom' => 'Michel',
        'client_comptant_contact' => '0708091011',
    ]);
});

test('calcule correctement la monnaie et le montant net encaisse en caisse sur le recu', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE', 'prix_vente' => 8000]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 10]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 8000]);

    // Client remet 10 000 FCFA pour un achat de 8 000 FCFA
    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 10000,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
                'prix' => 8000,
            ],
        ],
    ]);

    $vente = Vente::latest('id')->first();
    expect((float) $vente->total)->toBe(8000.0);
    expect((float) $vente->montant_paye)->toBe(10000.0);
    expect((float) $vente->monnaie)->toBe(2000.0);
    expect((float) $vente->montant_encaisse)->toBe(8000.0);

    // Vérifier que le paiement créé en caisse est de 8 000 FCFA
    $this->assertDatabaseHas('paiements', [
        'vente_id' => $vente->id,
        'montant' => 8000,
    ]);

    // Vérifier l'affichage sur la vue de reçu / ticket
    $showResponse = $this->actingAs($user)->get(route('ventes.show', $vente));
    $showResponse->assertOk();
    $showResponse->assertSee('Montant Remis par Client');
    $showResponse->assertSee('10 000 FCFA');
    $showResponse->assertSee('Monnaie Rendue au Client');
    $showResponse->assertSee('-2 000 FCFA');
    $showResponse->assertSee('Net Encaissé en Caisse');
    $showResponse->assertSee('8 000 FCFA');
});

test('peut choisir une date specifique pour une vente', function () {
    $user = User::factory()->create();
    $user->assignRole('Vendeur');
    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE', 'prix_vente' => 5000]);
    Stock::create(['produit_id' => $produit->id, 'quantite' => 20]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_vente' => 5000]);

    $dateChoisie = '2026-07-15 10:30:00';

    $response = $this->actingAs($user)->post(route('ventes.store'), [
        'date' => $dateChoisie,
        'mode_paiement' => ModePaiement::ESPECES->value,
        'montant_paye' => 5000,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 1,
                'prix' => 5000,
            ],
        ],
    ]);

    $response->assertRedirect();

    $vente = Vente::latest('id')->first();
    expect($vente->date->format('Y-m-d H:i:s'))->toBe($dateChoisie);
});
