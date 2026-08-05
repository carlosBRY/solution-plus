<?php

use App\Http\Controllers\ApprovisionnementController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\CasierController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompteFinancierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DeteriorationController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\InventaireController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VenteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Catégories
    Route::middleware('can:gérer-categories')->group(function () {
        Route::resource('categories', CategorieController::class)->except(['create', 'edit', 'show']);
    });

    // Produits & Conditionnements
    Route::middleware('can:gérer-produits')->group(function () {
        Route::resource('produits', ProduitController::class)->except(['create', 'edit', 'show']);
        Route::post('produits/{produit}/conditionnements', [ProduitController::class, 'storeConditionnement'])->name('produits.conditionnements.store');
    });

    // Approvisionnements
    Route::middleware('can:gérer-approvisionnements')->group(function () {
        Route::get('approvisionnements/create', [ApprovisionnementController::class, 'create'])->name('approvisionnements.create');
        Route::post('approvisionnements', [ApprovisionnementController::class, 'store'])->name('approvisionnements.store');
        Route::post('approvisionnements/{approvisionnement}/receptionner', [ApprovisionnementController::class, 'receptionner'])->name('approvisionnements.receptionner');
    });
    Route::middleware('permission:gérer-approvisionnements|consulter-comptabilite')->group(function () {
        Route::get('approvisionnements', [ApprovisionnementController::class, 'index'])->name('approvisionnements.index');
        Route::get('approvisionnements/{approvisionnement}', [ApprovisionnementController::class, 'show'])->name('approvisionnements.show');
    });

    // Ventes (Caisse & Point de vente)
    Route::middleware('can:gérer-ventes')->group(function () {
        Route::get('ventes/create', [VenteController::class, 'create'])->name('ventes.create');
        Route::post('ventes', [VenteController::class, 'store'])->name('ventes.store');
    });
    Route::middleware('permission:gérer-ventes|consulter-comptabilite')->group(function () {
        Route::get('ventes', [VenteController::class, 'index'])->name('ventes.index');
        Route::get('ventes/{vente}', [VenteController::class, 'show'])->name('ventes.show');
    });

    // Clients & Crédits
    Route::middleware('can:gérer-clients')->group(function () {
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('clients/{client}/regler-dette', [ClientController::class, 'reglerDette'])->name('clients.regler-dette');
        Route::post('clients/{client}/ajouter-credit', [ClientController::class, 'ajouterCredit'])->name('clients.ajouter-credit');
        Route::post('clients/{client}/ajuster-credit', [ClientController::class, 'ajusterCredit'])->name('clients.ajuster-credit');
    });
    Route::middleware('permission:gérer-clients|consulter-comptabilite')->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    });

    // Fournisseurs
    Route::middleware('can:gérer-fournisseurs')->group(function () {
        Route::post('fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store');
        Route::put('fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update');
        Route::delete('fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy');
        Route::post('fournisseurs/{fournisseur}/tarifs', [FournisseurController::class, 'updateTarifs'])->name('fournisseurs.tarifs.update');
    });
    Route::middleware('permission:gérer-fournisseurs|consulter-comptabilite')->group(function () {
        Route::get('fournisseurs', [FournisseurController::class, 'index'])->name('fournisseurs.index');
        Route::get('fournisseurs/{fournisseur}', [FournisseurController::class, 'show'])->name('fournisseurs.show');
    });

    // Caisses & Comptes Financiers
    Route::middleware('can:gérer-caisses')->group(function () {
        Route::post('comptes/transferer', [CompteFinancierController::class, 'transferer'])->name('comptes.transferer');
        Route::post('comptes/deposer', [CompteFinancierController::class, 'deposer'])->name('comptes.deposer');
        Route::post('comptes/retirer', [CompteFinancierController::class, 'retirer'])->name('comptes.retirer');
        Route::post('caisses/ouvrir', [CaisseController::class, 'ouvrir'])->name('caisses.ouvrir');
        Route::post('caisses/{caisse}/fermer', [CaisseController::class, 'fermer'])->name('caisses.fermer');
    });
    Route::middleware('permission:gérer-caisses|consulter-comptabilite')->group(function () {
        Route::get('comptes', [CompteFinancierController::class, 'index'])->name('comptes.index');
        Route::get('comptes/{compte}', [CompteFinancierController::class, 'show'])->name('comptes.show');
        Route::get('caisses', [CaisseController::class, 'index'])->name('caisses.index');
        Route::get('caisses/{caisse}', [CaisseController::class, 'show'])->name('caisses.show');
    });

    // Dépenses
    Route::middleware('can:gérer-depenses')->group(function () {
        Route::post('depenses', [DepenseController::class, 'store'])->name('depenses.store');
        Route::put('depenses/{depense}', [DepenseController::class, 'update'])->name('depenses.update');
        Route::delete('depenses/{depense}', [DepenseController::class, 'destroy'])->name('depenses.destroy');
    });
    Route::middleware('permission:gérer-depenses|consulter-comptabilite')->group(function () {
        Route::get('depenses', [DepenseController::class, 'index'])->name('depenses.index');
    });

    // Stock & Mouvements de stock
    Route::middleware('can:gérer-stocks')->group(function () {
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::post('stocks/{produit}/ajuster', [StockController::class, 'ajuster'])->name('stocks.ajuster');
        Route::get('stocks/mouvements', [StockController::class, 'mouvements'])->name('stocks.mouvements');

        // Détériorations — actions d'écriture (créer, supprimer, valider)
        Route::post('deteriorations/{deterioration}/valider', [DeteriorationController::class, 'valider'])->name('deteriorations.valider');
        Route::post('deteriorations', [DeteriorationController::class, 'store'])->name('deteriorations.store');
        Route::get('deteriorations/create', [DeteriorationController::class, 'create'])->name('deteriorations.create');
        Route::delete('deteriorations/{deterioration}', [DeteriorationController::class, 'destroy'])->name('deteriorations.destroy');
    });
    // Détériorations — consultation (index & show)
    Route::middleware('permission:gérer-stocks|consulter-comptabilite')->group(function () {
        Route::get('deteriorations', [DeteriorationController::class, 'index'])->name('deteriorations.index');
        Route::get('deteriorations/{deterioration}', [DeteriorationController::class, 'show'])->name('deteriorations.show');
    });

    // Inventaires
    Route::middleware('can:gérer-inventaires')->group(function () {
        Route::resource('inventaires', InventaireController::class)->except(['edit', 'update', 'destroy']);
    });

    // Casiers & Bouteilles Consignées
    Route::middleware('can:gérer-casiers')->prefix('casiers')->name('casiers.')->group(function () {
        Route::get('/', [CasierController::class, 'index'])->name('index');
        Route::post('/types', [CasierController::class, 'storeType'])->name('types.store');
        Route::put('/types/{typeCasier}/stock', [CasierController::class, 'adjustStock'])->name('types.adjust-stock');
        Route::post('/initialiser-stock', [CasierController::class, 'initialiserStockGlobal'])->name('initialiser-stock');
        Route::post('/mouvements', [CasierController::class, 'storeMouvement'])->name('mouvements.store');
        Route::post('/mouvements/{consignation}/solder', [CasierController::class, 'solderMouvement'])->name('mouvements.solder');
        Route::delete('/mouvements/{consignation}', [CasierController::class, 'destroyMouvement'])->name('mouvements.destroy');
    });

    // Module Paramètres (Général, Conditionnements, Utilisateurs)
    Route::middleware('can:gérer-parametres')->prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/', [ParametreController::class, 'index'])->name('index');
        Route::put('/general', [ParametreController::class, 'updateGeneral'])->name('update-general');

        Route::get('/conditionnements', [ParametreController::class, 'conditionnements'])->name('conditionnements');
        Route::post('/conditionnements', [ParametreController::class, 'storeConditionnement'])->name('conditionnements.store');
        Route::put('/conditionnements/{conditionnement}', [ParametreController::class, 'updateConditionnement'])->name('conditionnements.update');
        Route::delete('/conditionnements/{conditionnement}', [ParametreController::class, 'destroyConditionnement'])->name('conditionnements.destroy');

        Route::get('/comptes-financiers', [ParametreController::class, 'comptes'])->name('comptes.index');
        Route::post('/comptes-financiers', [ParametreController::class, 'storeCompte'])->name('comptes.store');
        Route::put('/comptes-financiers/{compte}', [ParametreController::class, 'updateCompte'])->name('comptes.update');
        Route::post('/comptes-financiers/{compte}/initialiser', [ParametreController::class, 'initialiserSolde'])->name('comptes.initialiser');
    });

    // Paramètres > Gestion des utilisateurs
    Route::middleware('can:gérer-utilisateurs')->prefix('parametres')->name('parametres.')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle-active');
        Route::put('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    });

    // Paramètres > Gestion des Rôles & Permissions
    Route::middleware('can:gérer-roles')->prefix('parametres')->name('parametres.')->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RolePermissionController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');
    });
});

require __DIR__.'/auth.php';
