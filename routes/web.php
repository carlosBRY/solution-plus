<?php

use App\Http\Controllers\ApprovisionnementController;
use App\Http\Controllers\CaisseController;
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
        Route::post('approvisionnements/{approvisionnement}/receptionner', [ApprovisionnementController::class, 'receptionner'])->name('approvisionnements.receptionner');
        Route::resource('approvisionnements', ApprovisionnementController::class)->except(['edit', 'update', 'destroy']);
    });

    // Ventes (Caisse & Point de vente)
    Route::middleware('can:gérer-ventes')->group(function () {
        Route::resource('ventes', VenteController::class)->except(['edit', 'update', 'destroy']);
    });

    // Clients & Crédits
    Route::middleware('can:gérer-clients')->group(function () {
        Route::post('clients/{client}/regler-dette', [ClientController::class, 'reglerDette'])->name('clients.regler-dette');
        Route::resource('clients', ClientController::class)->except(['create', 'edit']);
    });

    // Fournisseurs
    Route::middleware('can:gérer-fournisseurs')->group(function () {
        Route::resource('fournisseurs', FournisseurController::class)->except(['create', 'edit']);
    });

    // Caisses & Comptes Financiers
    Route::middleware('can:gérer-caisses')->group(function () {
        Route::get('comptes', [CompteFinancierController::class, 'index'])->name('comptes.index');
        Route::post('comptes/transferer', [CompteFinancierController::class, 'transferer'])->name('comptes.transferer');
        Route::get('comptes/{compte}', [CompteFinancierController::class, 'show'])->name('comptes.show');

        Route::post('caisses/ouvrir', [CaisseController::class, 'ouvrir'])->name('caisses.ouvrir');
        Route::post('caisses/{caisse}/fermer', [CaisseController::class, 'fermer'])->name('caisses.fermer');
        Route::resource('caisses', CaisseController::class)->only(['index', 'show']);
    });

    // Dépenses
    Route::middleware('can:gérer-depenses')->group(function () {
        Route::resource('depenses', DepenseController::class)->except(['create', 'edit', 'show']);
    });

    // Stock & Mouvements de stock
    Route::middleware('can:gérer-stocks')->group(function () {
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::post('stocks/{produit}/ajuster', [StockController::class, 'ajuster'])->name('stocks.ajuster');
        Route::get('stocks/mouvements', [StockController::class, 'mouvements'])->name('stocks.mouvements');

        Route::post('deteriorations/{deterioration}/valider', [DeteriorationController::class, 'valider'])->name('deteriorations.valider');
        Route::resource('deteriorations', DeteriorationController::class)->except(['edit', 'update']);
    });

    // Inventaires
    Route::middleware('can:gérer-inventaires')->group(function () {
        Route::resource('inventaires', InventaireController::class)->except(['edit', 'update', 'destroy']);
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
});

require __DIR__.'/auth.php';
