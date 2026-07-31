<?php

namespace App\Http\Controllers;

use App\Models\CompteFinancier;
use App\Models\Depense;
use App\Services\DepenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepenseController extends Controller
{
    public function __construct(
        protected DepenseService $depenseService
    ) {}

    /**
     * Liste des dépenses avec statistiques et filtres.
     */
    public function index(Request $request): View
    {
        $query = Depense::with(['user', 'compteFinancier']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                    ->orWhere('observation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->input('categorie'));
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date', '<=', $request->input('date_fin'));
        }

        $depenses = $query->latest('date')->paginate(10)->withQueryString();

        $categoriesDefaults = ['Transport', 'Factures', 'Fournitures', 'Maintenance', 'Salaires', 'Divers'];
        $categoriesDb = Depense::distinct()->whereNotNull('categorie')->pluck('categorie')->toArray();
        $categories = array_unique(array_merge($categoriesDefaults, $categoriesDb));

        $comptes = CompteFinancier::actif()->orderBy('nom')->get();

        $totalDepensesMois = Depense::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('montant');
        $totalDepensesJour = Depense::whereDate('date', today())->sum('montant');
        $totalCount = Depense::count();

        return view('depenses.index', compact(
            'depenses',
            'categories',
            'comptes',
            'totalDepensesMois',
            'totalDepensesJour',
            'totalCount'
        ));
    }

    /**
     * Enregistre une nouvelle dépense.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'reference_piece' => ['required', 'string', 'max:255'],
            'categorie' => ['required', 'string', 'max:100'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date'],
            'compte_financier_id' => ['nullable', 'exists:comptes_financiers,id'],
            'observation' => ['nullable', 'string'],
        ]);

        try {
            $depense = $this->depenseService->creerDepense($request->user(), $validated);

            return redirect()->route('depenses.index')
                ->with('success', "La dépense \"{$depense->libelle}\" de ".number_format($depense->montant, 0, ',', ' ').' FCFA a été enregistrée.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Met à jour une dépense existante.
     */
    public function update(Request $request, Depense $depense): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'reference_piece' => ['required', 'string', 'max:255'],
            'categorie' => ['required', 'string', 'max:100'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date'],
            'observation' => ['nullable', 'string'],
        ]);

        try {
            $this->depenseService->modifierDepense($depense, $validated);

            return redirect()->route('depenses.index')
                ->with('success', "La dépense \"{$depense->libelle}\" a été modifiée avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Supprime une dépense.
     */
    public function destroy(Depense $depense): RedirectResponse
    {
        try {
            $libelle = $depense->libelle;
            $this->depenseService->supprimerDepense($depense);

            return redirect()->route('depenses.index')
                ->with('success', "La dépense \"{$libelle}\" a été supprimée avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
