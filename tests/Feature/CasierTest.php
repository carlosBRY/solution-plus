<?php

use App\Models\Client;
use App\Models\ConsignationCasier;
use App\Models\TypeCasier;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Permission::findOrCreate('gérer-casiers', 'web');
});

test('un utilisateur sans permission ne peut pas acceder a la gestion des casiers', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('casiers.index'));
    $response->assertStatus(403);
});

test('un utilisateur autorise peut voir le tableau de bord des casiers', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('gérer-casiers');

    $response = $this->actingAs($user)->get(route('casiers.index'));
    $response->assertStatus(200);
    $response->assertSee('Casiers');
});

test('on peut creer un nouveau type de casier', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('gérer-casiers');

    $response = $this->actingAs($user)->post(route('casiers.types.store'), [
        'nom' => 'Casier 24 Bouteilles',
        'capacite_bouteilles' => 24,
        'quantite_casiers_cave' => 50,
        'quantite_bouteilles_seules_cave' => 10,
        'description' => 'Grands casiers 24 places',
    ]);

    $response->assertRedirect(route('casiers.index'));

    $this->assertDatabaseHas('type_casiers', [
        'nom' => 'Casier 24 Bouteilles',
        'capacite_bouteilles' => 24,
        'quantite_casiers_cave' => 50,
        'quantite_bouteilles_seules_cave' => 10,
    ]);
});

test('on peut ajuster le stock physique de casiers et bouteilles en cave', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('gérer-casiers');

    $typeCasier = TypeCasier::create([
        'nom' => 'Casier 12 places',
        'capacite_bouteilles' => 12,
        'quantite_casiers_cave' => 10,
        'quantite_bouteilles_seules_cave' => 5,
    ]);

    $response = $this->actingAs($user)->put(route('casiers.types.adjust-stock', $typeCasier), [
        'quantite_casiers_cave' => 15,
        'quantite_bouteilles_seules_cave' => 8,
    ]);

    $response->assertRedirect(route('casiers.index'));

    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(15);
    expect($typeCasier->quantite_bouteilles_seules_cave)->toBe(8);
});

test('on peut enregistrer un pret de casiers a un client et solder la consignation', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('gérer-casiers');

    $client = Client::factory()->create();
    $typeCasier = TypeCasier::create([
        'nom' => 'Casier 12 places',
        'capacite_bouteilles' => 12,
        'quantite_casiers_cave' => 20,
        'quantite_bouteilles_seules_cave' => 5,
    ]);

    // 1. Enregistrement du prêt au client
    $response = $this->actingAs($user)->post(route('casiers.mouvements.store'), [
        'type_casier_id' => $typeCasier->id,
        'client_id' => $client->id,
        'type_mouvement' => 'PRET_CLIENT',
        'nombre_casiers' => 3,
        'nombre_bouteilles_seules' => 2,
        'notes' => 'Prêt fête de famille',
    ]);

    $response->assertRedirect(route('casiers.index'));

    $this->assertDatabaseHas('consignations_casiers', [
        'type_casier_id' => $typeCasier->id,
        'client_id' => $client->id,
        'type_mouvement' => 'PRET_CLIENT',
        'nombre_casiers' => 3,
        'nombre_bouteilles_seules' => 2,
        'statut' => 'EN_COURS',
    ]);

    // Le stock en cave doit avoir diminué (20 - 3 = 17)
    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(17);

    // 2. Solde / Restitution du prêt par le client
    $consignation = ConsignationCasier::first();

    $responseSolde = $this->actingAs($user)->post(route('casiers.mouvements.solder', $consignation));
    $responseSolde->assertRedirect(route('casiers.index'));

    expect($consignation->refresh()->statut)->toBe('SOLDE');
    // Le stock en cave doit avoir été ré-intégré (17 + 3 = 20)
    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(20);
});

test('on peut enregistrer un depot de casiers a la cave', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('gérer-casiers');

    $typeCasier = TypeCasier::create([
        'nom' => 'Casier 20 places',
        'capacite_bouteilles' => 20,
        'quantite_casiers_cave' => 5,
        'quantite_bouteilles_seules_cave' => 0,
    ]);

    $response = $this->actingAs($user)->post(route('casiers.mouvements.store'), [
        'type_casier_id' => $typeCasier->id,
        'nom_personne' => 'M. Konan Passant',
        'contact_personne' => '0707070707',
        'type_mouvement' => 'DEPOT_CAVE',
        'nombre_casiers' => 4,
        'nombre_bouteilles_seules' => 0,
    ]);

    $response->assertRedirect(route('casiers.index'));

    // Stock cave augmenté de 4 (5 + 4 = 9)
    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(9);
});

test('on peut initialiser et ajouter du stock physique global d emballages', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('gérer-casiers');

    $typeCasier = TypeCasier::create([
        'nom' => 'Casier 12 Bouteilles',
        'capacite_bouteilles' => 12,
        'quantite_casiers_cave' => 0,
        'quantite_bouteilles_seules_cave' => 0,
    ]);

    // Initialisation exacte (DEFINIR)
    $responseInit = $this->actingAs($user)->post(route('casiers.initialiser-stock'), [
        'type_casier_id' => $typeCasier->id,
        'mode_saisie' => 'DEFINIR',
        'quantite_casiers_cave' => 100,
        'quantite_bouteilles_seules_cave' => 24,
    ]);
    $responseInit->assertRedirect(route('casiers.index'));
    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(100);
    expect($typeCasier->quantite_bouteilles_seules_cave)->toBe(24);

    // Ajout au stock existant (AJOUTER)
    $responseAjout = $this->actingAs($user)->post(route('casiers.initialiser-stock'), [
        'type_casier_id' => $typeCasier->id,
        'mode_saisie' => 'AJOUTER',
        'quantite_casiers_cave' => 50,
        'quantite_bouteilles_seules_cave' => 10,
    ]);
    $responseAjout->assertRedirect(route('casiers.index'));
    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(150);
    expect($typeCasier->quantite_bouteilles_seules_cave)->toBe(34);
});

test('on peut annuler un mouvement errone et le stock est corrige automatiquement', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('gérer-casiers');

    $typeCasier = TypeCasier::create([
        'nom' => 'Casier 12 places',
        'capacite_bouteilles' => 12,
        'quantite_casiers_cave' => 30,
        'quantite_bouteilles_seules_cave' => 10,
    ]);

    // Enregistrer un prêt (stock passe de 30 à 25)
    $this->actingAs($user)->post(route('casiers.mouvements.store'), [
        'type_casier_id' => $typeCasier->id,
        'nom_personne' => 'Erreur Test',
        'type_mouvement' => 'PRET_CLIENT',
        'nombre_casiers' => 5,
        'nombre_bouteilles_seules' => 3,
    ]);

    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(25);
    expect($typeCasier->quantite_bouteilles_seules_cave)->toBe(7);

    $consignation = ConsignationCasier::first();

    // Annuler le mouvement erroné → stock doit revenir à 30/10
    $response = $this->actingAs($user)->delete(route('casiers.mouvements.destroy', $consignation));
    $response->assertRedirect(route('casiers.index'));

    expect($typeCasier->refresh()->quantite_casiers_cave)->toBe(30);
    expect($typeCasier->quantite_bouteilles_seules_cave)->toBe(10);
    $this->assertDatabaseMissing('consignations_casiers', ['id' => $consignation->id]);
});
