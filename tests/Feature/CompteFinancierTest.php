<?php

use App\Models\CompteFinancier;
use App\Models\User;
use App\Services\CompteFinancierService;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('gérer-caisses', 'web');
    Permission::findOrCreate('gérer-parametres', 'web');

    $role = Role::findOrCreate('Gestionnaire', 'web');
    $role->givePermissionTo(['gérer-caisses', 'gérer-parametres']);
});

test('peut crediter un compte financier', function () {
    $user = User::factory()->create();
    $compte = CompteFinancier::create([
        'nom' => 'Caisse Test',
        'mode' => 'ESPECES_TEST',
        'solde_initial' => 10000,
        'solde_courant' => 10000,
        'actif' => true,
    ]);

    $service = app(CompteFinancierService::class);
    $mouvement = $service->crediter($compte, $user, 5000, 'Test Crédit');

    expect((float) $compte->fresh()->solde_courant)->toBe(15000.0);
    expect($mouvement->type)->toBe('CREDIT');
    expect((float) $mouvement->montant)->toBe(5000.0);
    expect((float) $mouvement->solde_apres)->toBe(15000.0);
});

test('peut debiter un compte financier', function () {
    $user = User::factory()->create();
    $compte = CompteFinancier::create([
        'nom' => 'Orange Money Test',
        'mode' => 'OM_TEST',
        'solde_initial' => 20000,
        'solde_courant' => 20000,
        'actif' => true,
    ]);

    $service = app(CompteFinancierService::class);
    $mouvement = $service->debiter($compte, $user, 7000, 'Test Débit');

    expect((float) $compte->fresh()->solde_courant)->toBe(13000.0);
    expect($mouvement->type)->toBe('DEBIT');
    expect((float) $mouvement->montant)->toBe(7000.0);
    expect((float) $mouvement->solde_apres)->toBe(13000.0);
});

test('peut effectuer un transfert atomique entre deux comptes', function () {
    $user = User::factory()->create();
    $source = CompteFinancier::create([
        'nom' => 'Source Espèces',
        'mode' => 'SRC_TEST',
        'solde_initial' => 50000,
        'solde_courant' => 50000,
        'actif' => true,
    ]);

    $dest = CompteFinancier::create([
        'nom' => 'Destination Banque',
        'mode' => 'DEST_TEST',
        'solde_initial' => 10000,
        'solde_courant' => 10000,
        'actif' => true,
    ]);

    $service = app(CompteFinancierService::class);
    $res = $service->transferer($source, $dest, $user, 15000, 'Dépôt banque');

    expect((float) $source->fresh()->solde_courant)->toBe(35000.0);
    expect((float) $dest->fresh()->solde_courant)->toBe(25000.0);
    expect($res['debit']->reference_id)->toBe($res['credit']->reference_id);
});

test('la page caisse principale est accessible et calcule le solde total', function () {
    $user = User::factory()->create();
    $user->assignRole('Gestionnaire');

    $response = $this->actingAs($user)->get(route('comptes.index'));
    $response->assertStatus(200);
    $response->assertSee('Caisse Principale');
});

test('peut effectuer un transfert via la route HTTP', function () {
    $user = User::factory()->create();
    $user->assignRole('Gestionnaire');

    $source = CompteFinancier::create(['nom' => 'Caisse 1', 'mode' => 'C1', 'solde_courant' => 30000, 'actif' => true]);
    $dest = CompteFinancier::create(['nom' => 'Caisse 2', 'mode' => 'C2', 'solde_courant' => 5000, 'actif' => true]);

    $response = $this->actingAs($user)->post(route('comptes.transferer'), [
        'source_id' => $source->id,
        'destination_id' => $dest->id,
        'montant' => 10000,
        'motif' => 'Transfert régularisation',
    ]);

    $response->assertRedirect(route('comptes.index'));
    $response->assertSessionHas('success');

    expect((float) $source->fresh()->solde_courant)->toBe(20000.0);
    expect((float) $dest->fresh()->solde_courant)->toBe(15000.0);
});

test('peut ajouter un nouveau compte financier via les parametres', function () {
    $user = User::factory()->create();
    $user->assignRole('Gestionnaire');

    $response = $this->actingAs($user)->post(route('parametres.comptes.store'), [
        'nom' => 'Wave Côte d\'Ivoire',
        'mode' => 'WAVE',
        'solde_initial' => 25000,
        'description' => 'Compte Wave entreprise',
    ]);

    $response->assertRedirect(route('parametres.comptes.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('comptes_financiers', [
        'nom' => 'Wave Côte d\'Ivoire',
        'mode' => 'WAVE',
        'solde_courant' => 25000,
    ]);
});

test('refoule le debit d un compte si le solde est insuffisant', function () {
    $user = User::factory()->create();
    $compte = CompteFinancier::create([
        'nom' => 'Compte Pauvre',
        'mode' => 'PAUVRE',
        'solde_initial' => 2000,
        'solde_courant' => 2000,
        'actif' => true,
    ]);

    $service = app(CompteFinancierService::class);

    expect(fn () => $service->debiter($compte, $user, 5000, 'Achat impossible'))
        ->toThrow(ValidationException::class);

    expect((float) $compte->fresh()->solde_courant)->toBe(2000.0);
});

test('refoule le transfert entre comptes si le solde du compte source est insuffisant', function () {
    $user = User::factory()->create();
    $source = CompteFinancier::create(['nom' => 'Source Insuffisante', 'mode' => 'SRC_INSUFF', 'solde_courant' => 3000, 'actif' => true]);
    $dest = CompteFinancier::create(['nom' => 'Destination Test', 'mode' => 'DEST_INSUFF', 'solde_courant' => 5000, 'actif' => true]);

    $service = app(CompteFinancierService::class);

    expect(fn () => $service->transferer($source, $dest, $user, 10000, 'Transfert trop grand'))
        ->toThrow(ValidationException::class);

    expect((float) $source->fresh()->solde_courant)->toBe(3000.0);
    expect((float) $dest->fresh()->solde_courant)->toBe(5000.0);
});
