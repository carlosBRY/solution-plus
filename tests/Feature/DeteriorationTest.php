<?php

use App\Enums\DeteriorationCause;
use App\Enums\MouvementType;
use App\Enums\StatutDeterioration;
use App\Models\Conditionnement;
use App\Models\Deterioration;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Services\DeteriorationService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-stocks', 'web');
    $role = Role::findOrCreate('Gestionnaire de Stock', 'web');
    $role->givePermissionTo('gérer-stocks');
});

test('peut creer une deterioration en brouillon sans modifier le stock', function () {
    $user = User::factory()->create();
    $user->assignRole('Gestionnaire de Stock');

    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE']);
    $stock = Stock::create(['produit_id' => $produit->id, 'quantite' => 50]);
    $condCaisse = Conditionnement::factory()->caisseDe12()->create(['produit_id' => $produit->id]);

    $response = $this->actingAs($user)->post(route('deteriorations.store'), [
        'date' => now()->format('Y-m-d H:i:s'),
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $condCaisse->id,
                'quantite_conditionnement' => 2, // 2 caisses de 12 = 24 bouteilles
                'cause' => DeteriorationCause::CASSE->value,
            ],
        ],
    ]);

    $response->assertRedirect();

    $deterioration = Deterioration::first();
    expect($deterioration)->not->toBeNull();
    expect($deterioration->statut)->toBe(StatutDeterioration::BROUILLON);

    // Le stock ne doit pas bouger en brouillon
    expect($stock->fresh()->quantite)->toBe(50);
});

test('validation d une deterioration decremente le stock en unites de base et cree un mouvement DETERIORATION', function () {
    $user = User::factory()->create();
    $user->assignRole('Gestionnaire de Stock');

    $produit = Produit::factory()->create(['unite_base' => 'BOUTEILLE']);
    $stock = Stock::create(['produit_id' => $produit->id, 'quantite' => 50]);
    $condCaisse = Conditionnement::factory()->caisseDe12()->create(['produit_id' => $produit->id]);

    $service = app(DeteriorationService::class);
    $deterioration = $service->creerBrouillon($user, ['date' => now()], [
        [
            'produit_id' => $produit->id,
            'conditionnement_id' => $condCaisse->id,
            'quantite_conditionnement' => 2, // 2 caisses de 12 = 24 bouteilles
            'cause' => DeteriorationCause::CASSE->value,
            'cout_unitaire' => 10000,
        ],
    ]);

    $response = $this->actingAs($user)->post(route('deteriorations.valider', $deterioration));

    $response->assertRedirect(route('deteriorations.show', $deterioration));

    // Le stock doit être décrémenté de 24 (50 - 24 = 26)
    expect($stock->fresh()->quantite)->toBe(26);

    // Verrouillage de la détérioration
    expect($deterioration->fresh()->statut)->toBe(StatutDeterioration::VALIDEE);

    // Vérifier la présence du mouvement de stock
    $this->assertDatabaseHas('mouvements_stock', [
        'produit_id' => $produit->id,
        'type' => MouvementType::DETERIORATION->value,
        'quantite' => -24,
        'stock_avant' => 50,
        'stock_apres' => 26,
    ]);
});

test('impossible de supprimer une deterioration validee', function () {
    $user = User::factory()->create();
    $user->assignRole('Gestionnaire de Stock');

    $produit = Produit::factory()->create();
    Stock::create(['produit_id' => $produit->id, 'quantite' => 100]);
    $cond = Conditionnement::factory()->create(['produit_id' => $produit->id]);

    $service = app(DeteriorationService::class);
    $deterioration = $service->creerBrouillon($user, ['date' => now()], [
        [
            'produit_id' => $produit->id,
            'conditionnement_id' => $cond->id,
            'quantite_conditionnement' => 1,
            'cause' => DeteriorationCause::PEREMPTION->value,
        ],
    ]);

    $service->valider($deterioration, $user);

    $response = $this->actingAs($user)->delete(route('deteriorations.destroy', $deterioration));

    $response->assertStatus(403);
});
