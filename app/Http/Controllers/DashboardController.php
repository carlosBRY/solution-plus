<?php

namespace App\Http\Controllers;

use App\Enums\StatutVente;
use App\Models\Caisse;
use App\Models\Client;
use App\Models\CompteFinancier;
use App\Models\Depense;
use App\Models\Parametre;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\Vente;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord principal.
     */
    public function __invoke(): View
    {
        $totalVentes = Vente::sum('total');
        $totalBouteillesStock = Stock::sum('quantite');
        $totalClients = Client::count();
        $caisseOuverte = Caisse::where('statut', 'OUVERTE')->latest()->first();

        $produitsAlerte = Produit::with(['categorie', 'stock'])
            ->whereHas('stock', function ($query) {
                $query->whereColumn('stocks.quantite', '<=', 'produits.stock_min');
            })
            ->take(5)
            ->get();

        $dernieresVentes = Vente::with(['client', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $parametre = Parametre::first();

        // === Données financières pour la carte récapitulative ===

        // Soldes des comptes financiers (caisse principale + autres comptes actifs)
        $comptesFinanciers = CompteFinancier::actif()->orderByDesc('solde_courant')->get();

        // Dates repères
        $aujourdHui = Carbon::today();
        $debutSemaine = Carbon::now()->startOfWeek();
        $debutMois = Carbon::now()->startOfMonth();

        // Dépenses
        $depensesJour = Depense::whereDate('date', $aujourdHui)->sum('montant');
        $depensesMois = Depense::whereBetween('date', [$debutMois, Carbon::now()])->sum('montant');

        // Ventes (hors annulées)
        $ventesActives = fn () => Vente::where('statut', '!=', StatutVente::ANNULEE);
        $ventesJour = $ventesActives()->whereDate('date', $aujourdHui)->sum('total');
        $ventesSemaine = $ventesActives()->whereBetween('date', [$debutSemaine, Carbon::now()])->sum('total');
        $ventesMois = $ventesActives()->whereBetween('date', [$debutMois, Carbon::now()])->sum('total');

        // Total crédits octroyés = somme des soldes (dettes) de tous les clients
        $totalCreditsOctroyes = Client::where('solde', '>', 0)->sum('solde');

        return view('dashboard', compact(
            'totalVentes',
            'totalBouteillesStock',
            'totalClients',
            'caisseOuverte',
            'produitsAlerte',
            'dernieresVentes',
            'parametre',
            'comptesFinanciers',
            'depensesJour',
            'depensesMois',
            'ventesJour',
            'ventesSemaine',
            'ventesMois',
            'totalCreditsOctroyes'
        ));
    }
}
