<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\Client;
use App\Models\Parametre;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\Vente;
use Illuminate\Contracts\View\View;

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

        return view('dashboard', compact(
            'totalVentes',
            'totalBouteillesStock',
            'totalClients',
            'caisseOuverte',
            'produitsAlerte',
            'dernieresVentes',
            'parametre'
        ));
    }
}
