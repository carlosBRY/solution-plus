<?php

use App\Enums\StatutApprovisionnement;
use App\Models\Conditionnement;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('gérer-fournisseurs', 'web');
    Permission::findOrCreate('gérer-approvisionnements', 'web');
});

test('peut définir et mettre à jour la grille tarifaire des conditionnements d un fournisseur', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['gérer-fournisseurs']);

    $fournisseur = Fournisseur::factory()->create(['nom' => 'Solibra SA']);
    $produit = Produit::factory()->create(['nom' => 'Bière Beaufort', 'prix_achat' => 500]);
    $condBouteille = Conditionnement::factory()->create(['produit_id' => $produit->id, 'nom' => 'Bouteille 65cl', 'quantite_unite_base' => 1]);
    $condCasier = Conditionnement::factory()->create(['produit_id' => $produit->id, 'nom' => 'Casier 12', 'quantite_unite_base' => 12]);

    $response = $this->actingAs($user)->post(route('fournisseurs.tarifs.update', $fournisseur), [
        'tarifs' => [
            ['produit_id' => $produit->id, 'conditionnement_id' => $condBouteille->id, 'prix_achat' => 480],
            ['produit_id' => $produit->id, 'conditionnement_id' => $condCasier->id, 'prix_achat' => 5500],
        ],
    ]);

    $response->assertRedirect(route('fournisseurs.show', $fournisseur));

    $this->assertDatabaseHas('fournisseur_produit', [
        'fournisseur_id' => $fournisseur->id,
        'produit_id' => $produit->id,
        'conditionnement_id' => $condBouteille->id,
        'prix_achat' => 480.00,
    ]);

    $this->assertDatabaseHas('fournisseur_produit', [
        'fournisseur_id' => $fournisseur->id,
        'produit_id' => $produit->id,
        'conditionnement_id' => $condCasier->id,
        'prix_achat' => 5500.00,
    ]);
});

test('la création d un approvisionnement met à jour automatiquement le tarif du conditionnement chez le fournisseur', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['gérer-approvisionnements']);

    $fournisseur = Fournisseur::factory()->create();
    $produit = Produit::factory()->create(['prix_achat' => 600, 'unite_base' => 'BOUTEILLE']);
    $cond = Conditionnement::factory()->create([
        'produit_id' => $produit->id,
        'quantite_unite_base' => 12,
        'prix_achat' => 6500,
    ]);

    $response = $this->actingAs($user)->post(route('approvisionnements.store'), [
        'fournisseur_id' => $fournisseur->id,
        'reference_facture' => 'FAC-2026-99',
        'statut' => StatutApprovisionnement::EN_ATTENTE->value,
        'items' => [
            [
                'produit_id' => $produit->id,
                'conditionnement_id' => $cond->id,
                'quantite_conditionnement' => 10,
                'prix_achat' => 6300, // Prix d'achat spécifique du casier lors de cette livraison
            ],
        ],
    ]);

    $response->assertRedirect();

    // Vérifie que le tarif du fournisseur pour ce conditionnement a été mis à jour à 6300
    $this->assertDatabaseHas('fournisseur_produit', [
        'fournisseur_id' => $fournisseur->id,
        'produit_id' => $produit->id,
        'conditionnement_id' => $cond->id,
        'prix_achat' => 6300.00,
    ]);
});

test('la creation d un conditionnement avec prix d achat le configure automatiquement chez tous les fournisseurs', function () {
    $f1 = Fournisseur::factory()->create(['nom' => 'Solibra']);
    $f2 = Fournisseur::factory()->create(['nom' => 'Bracodi']);
    $produit = Produit::factory()->create();

    $cond = Conditionnement::factory()->create([
        'produit_id' => $produit->id,
        'nom' => 'Pack de 6',
        'prix_achat' => 3200,
    ]);

    $this->assertDatabaseHas('fournisseur_produit', [
        'fournisseur_id' => $f1->id,
        'produit_id' => $produit->id,
        'conditionnement_id' => $cond->id,
        'prix_achat' => 3200.00,
    ]);

    $this->assertDatabaseHas('fournisseur_produit', [
        'fournisseur_id' => $f2->id,
        'produit_id' => $produit->id,
        'conditionnement_id' => $cond->id,
        'prix_achat' => 3200.00,
    ]);
});

test('la creation d un nouveau fournisseur lui attribue automatiquement les tarifs des conditionnements existants', function () {
    $produit = Produit::factory()->create();
    $cond = Conditionnement::factory()->create([
        'produit_id' => $produit->id,
        'nom' => 'Casier de 24',
        'prix_achat' => 12000,
    ]);

    $nouveauFournisseur = Fournisseur::factory()->create(['nom' => 'Nouveau Grossiste']);

    $this->assertDatabaseHas('fournisseur_produit', [
        'fournisseur_id' => $nouveauFournisseur->id,
        'produit_id' => $produit->id,
        'conditionnement_id' => $cond->id,
        'prix_achat' => 12000.00,
    ]);
});
