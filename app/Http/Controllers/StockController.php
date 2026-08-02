<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Vue d'ensemble de l'état des stocks.
     */
    public function index(Request $request): View
    {
        $query = Produit::with(['categorie', 'stock', 'conditionnements'])->where('actif', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('code_barre', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->input('categorie_id'));
        }

        if ($request->filled('statut')) {
            $statut = $request->input('statut');
            if ($statut === 'alerte') {
                $query->whereHas('stock', function ($q) {
                    $q->whereColumn('stocks.quantite', '<=', 'produits.stock_min')
                        ->where('stocks.quantite', '>', 0);
                });
            } elseif ($statut === 'rupture') {
                $query->whereHas('stock', function ($q) {
                    $q->where('stocks.quantite', '<=', 0);
                });
            }
        }

        $produits = $query->orderBy('nom')->paginate(10)->withQueryString();
        $categories = Categorie::orderBy('nom')->get();

        $totalUnitesEnStock = (int) Stock::sum('quantite');
        $produitsEnAlerte = Produit::whereHas('stock', function ($q) {
            $q->whereColumn('stocks.quantite', '<=', 'produits.stock_min')
                ->where('stocks.quantite', '>', 0);
        })->count();
        $produitsEnRupture = Produit::whereHas('stock', function ($q) {
            $q->where('stocks.quantite', '<=', 0);
        })->count();

        return view('stocks.index', compact(
            'produits',
            'categories',
            'totalUnitesEnStock',
            'produitsEnAlerte',
            'produitsEnRupture'
        ));
    }

    /**
     * Effectue un ajustement manuel du stock d'un produit.
     */
    public function ajuster(Request $request, Produit $produit): RedirectResponse
    {
        Gate::authorize('ajuster-stock');

        $validated = $request->validate([
            'quantite' => ['required', 'integer', 'min:0'],
            'motif' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->stockService->ajusterStock(
                $produit,
                $request->user(),
                (int) $validated['quantite'],
                $validated['motif']
            );

            return redirect()->route('stocks.index')
                ->with('success', "Le stock du produit \"{$produit->nom}\" a été ajusté à {$validated['quantite']} {$produit->unite_base}(s).");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Journal d'audit complet de tous les mouvements de stock.
     */
    public function mouvements(Request $request): View
    {
        $query = MouvementStock::with(['produit.categorie', 'user', 'conditionnement']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('motif', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('produit', function ($pq) use ($search) {
                        $pq->where('nom', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->input('date_fin'));
        }

        $mouvements = $query->latest()->paginate(15)->withQueryString();

        return view('stocks.mouvements', compact('mouvements'));
    }
}
