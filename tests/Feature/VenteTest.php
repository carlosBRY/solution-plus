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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-ventes', 'web');
    $role = Role::findOrCreate('Vendeur', 'web');
    $role->givePermissionTo('gérer-ventes');

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
