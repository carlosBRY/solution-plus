<?php

use App\Enums\ModePaiement;
use App\Enums\MouvementType;
use App\Enums\StatutVente;
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
                'quantite_conditionnement' => 1, // 1 caisse de 12 = 12 unités > stock de 2
                'prix' => 300000,
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
