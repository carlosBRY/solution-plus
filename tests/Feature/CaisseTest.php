<?php

use App\Enums\StatutCaisse;
use App\Models\CompteFinancier;
use App\Models\Depense;
use App\Models\User;
use App\Services\CaisseService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-caisses', 'web');
    $role = Role::findOrCreate('Caissier', 'web');
    $role->givePermissionTo('gérer-caisses');

    // S'assurer qu'au moins 2 comptes existent
    CompteFinancier::firstOrCreate(
        ['mode' => 'ESPECES'],
        ['nom' => 'Caisse Espèces', 'solde_initial' => 0, 'solde_courant' => 0, 'actif' => true]
    );

    CompteFinancier::firstOrCreate(
        ['mode' => 'ORANGE_MONEY'],
        ['nom' => 'Orange Money', 'solde_initial' => 0, 'solde_courant' => 0, 'actif' => true]
    );
});

test('un utilisateur autorise peut ouvrir une session de caisse par compte', function () {
    $user = User::factory()->create();
    $user->assignRole('Caissier');

    $comptes = CompteFinancier::actif()->get();
    $soldesInitiaux = [];
    foreach ($comptes as $c) {
        $soldesInitiaux[$c->id] = 25000;
    }

    $response = $this->actingAs($user)->post(route('caisses.ouvrir'), [
        'soldes_initiaux' => $soldesInitiaux,
    ]);

    $response->assertRedirect(route('caisses.index'));
    $response->assertSessionHas('success');

    $totalInitial = count($comptes) * 25000;
    $this->assertDatabaseHas('caisses', [
        'user_id' => $user->id,
        'solde_initial' => $totalInitial,
        'statut' => StatutCaisse::OUVERTE->value,
    ]);
});

test('impossible d ouvrir deux sessions de caisse actives pour le meme utilisateur', function () {
    $user = User::factory()->create();
    $user->assignRole('Caissier');

    $service = app(CaisseService::class);
    $service->ouvrirCaisse($user, []);

    $comptes = CompteFinancier::actif()->get();
    $soldesInitiaux = [];
    foreach ($comptes as $c) {
        $soldesInitiaux[$c->id] = 10000;
    }

    $response = $this->actingAs($user)->post(route('caisses.ouvrir'), [
        'soldes_initiaux' => $soldesInitiaux,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('peut fermer une caisse par compte et calculer les ecarts', function () {
    $user = User::factory()->create();
    $user->assignRole('Caissier');

    $service = app(CaisseService::class);
    $compteEsp = CompteFinancier::where('mode', 'ESPECES')->first();
    $compteOm = CompteFinancier::where('mode', 'ORANGE_MONEY')->first();

    $caisse = $service->ouvrirCaisse($user, [
        $compteEsp->id => 50000,
        $compteOm->id => 20000,
    ]);

    // Dépense sur le compte Espèces
    Depense::create([
        'user_id' => $user->id,
        'libelle' => 'Fournitures',
        'categorie' => 'Divers',
        'montant' => 10000,
        'mode' => 'ESPECES',
        'compte_financier_id' => $compteEsp->id,
        'date' => now(),
    ]);

    // Solde théorique Espèces = 50000 - 10000 = 40000
    // Solde théorique Orange Money = 20000
    // Solde théorique global = 60000

    $response = $this->actingAs($user)->post(route('caisses.fermer', $caisse), [
        'soldes_finaux' => [
            $compteEsp->id => 38000, // ecart -2000
            $compteOm->id => 20000,  // ecart 0
        ],
    ]);

    $response->assertRedirect(route('caisses.show', $caisse));

    expect($caisse->fresh()->statut->value)->toBe(StatutCaisse::FERMEE->value);
    expect((float) $caisse->fresh()->solde_final)->toBe(58000.0);
    expect((float) $caisse->fresh()->ecart)->toBe(-2000.0);
});

test('les soldes de cloture de la veille sont reconduits automatiquement pour l ouverture suivante', function () {
    $user = User::factory()->create();
    $user->assignRole('Caissier');

    $service = app(CaisseService::class);
    $compteEsp = CompteFinancier::where('mode', 'ESPECES')->first();

    // 1. Première session clôturée avec solde final 45000
    $caisse1 = $service->ouvrirCaisse($user, [$compteEsp->id => 10000]);
    $service->fermerCaisse($caisse1, [$compteEsp->id => 45000]);

    // 2. Obtenir les soldes de reconduction pour l'ouverture suivante
    $soldesReconduits = $service->getDerniersSoldesFinaux($user);

    expect((float) $soldesReconduits[$compteEsp->id]['solde'])->toBe(45000.0);
});
