<?php

use App\Enums\MouvementType;
use App\Enums\StatutApprovisionnement;
use App\Models\CompteFinancier;
use App\Models\Conditionnement;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Services\ApprovisionnementService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-approvisionnements', 'web');
    $role = Role::findOrCreate('Gérant', 'web');
    $role->givePermissionTo('gérer-approvisionnements');
});

test('peut creer un approvisionnement receptionne qui incremente le stock', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');
    $fournisseur = Fournisseur::factory()->create();
    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE']);
    $stock = Stock::create(['produit_id' => $produit->id, 'quantite' => 10]);
    $cond = Conditionnement::factory()->caisseDe12()->create(['produit_id' => $produit->id, 'prix_achat' => 120000]);

    $response = $this->actingAs($user)->post(route('approvisionnements.store'), [
        'fournisseur_id' => $fournisseur->id,
        'reference_facture' => 'FACT-TEST-001',
        'date' => now()->format('Y-m-d H:i:s'),
        'statut' => StatutApprovisionnement::RECEPTIONNE->value,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 3, // 3 caisses de 12 = 36 bouteilles
                'prix_achat' => 120000,
            ],
        ],
    ]);

    $response->assertRedirect();

    // Stock doit être 10 + 36 = 46
    expect($stock->fresh()->quantite)->toBe(46);

    $this->assertDatabaseHas('mouvements_stock', [
        'produit_id' => $produit->id,
        'type' => MouvementType::ENTREE->value,
        'quantite' => 36,
        'stock_avant' => 10,
        'stock_apres' => 46,
    ]);
});

test('peut receptionner un approvisionnement en attente', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');
    $fournisseur = Fournisseur::factory()->create();
    $produit = Produit::factory()->create();
    $stock = Stock::create(['produit_id' => $produit->id, 'quantite' => 5]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id]);

    $service = app(ApprovisionnementService::class);
    $approvisionnement = $service->creerApprovisionnement($user, [
        'fournisseur_id' => $fournisseur->id,
        'reference_facture' => 'FACT-TEST-002',
        'statut' => StatutApprovisionnement::EN_ATTENTE->value,
    ], [
        [
            'produit_id' => $produit->id,
            'conditionnement_id' => $cond->id,
            'quantite_conditionnement' => 10,
            'prix_achat' => 5000,
        ],
    ]);

    // Stock ne doit pas bouger en attente
    expect($stock->fresh()->quantite)->toBe(5);

    $response = $this->actingAs($user)->post(route('approvisionnements.receptionner', $approvisionnement));

    $response->assertRedirect();

    // Stock = 5 + 10*1 = 15
    expect($stock->fresh()->quantite)->toBe(15);
    expect($approvisionnement->fresh()->statut)->toBe(StatutApprovisionnement::RECEPTIONNE);
});

test('refoule un approvisionnement si le solde du compte financier est insuffisant', function () {
    $user = User::factory()->create();
    $user->assignRole('Gérant');
    $fournisseur = Fournisseur::factory()->create();
    $produit = Produit::factory()->create();
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id, 'prix_achat' => 50000]);
    $compte = CompteFinancier::create([
        'nom' => 'Banque Test',
        'mode' => 'BANQUE_TEST',
        'solde_initial' => 10000,
        'solde_courant' => 10000,
        'actif' => true,
    ]);

    $response = $this->actingAs($user)->post(route('approvisionnements.store'), [
        'fournisseur_id' => $fournisseur->id,
        'reference_facture' => 'FACT-TEST-SOLDE',
        'compte_financier_id' => $compte->id,
        'statut' => StatutApprovisionnement::RECEPTIONNE->value,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 2, // Total = 2 * 50 000 = 100 000 FCFA > 10 000 FCFA (solde)
                'prix_achat' => 50000,
            ],
        ],
    ]);

    $response->assertSessionHasErrors(['compte_financier_id']);
    expect((float) $compte->fresh()->solde_courant)->toBe(10000.0);
    $this->assertDatabaseMissing('approvisionnements', [
        'reference_facture' => 'FACT-TEST-SOLDE',
    ]);
});
