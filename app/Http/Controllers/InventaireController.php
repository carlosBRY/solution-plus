<?php

namespace App\Http\Controllers;

use App\Models\Inventaire;
use App\Models\InventaireDetail;
use App\Models\Produit;
use App\Services\InventaireService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventaireController extends Controller
{
    public function __construct(
        protected InventaireService $inventaireService
    ) {}

    /**
     * Liste des inventaires enregistrés.
     */
    public function index(Request $request): View
    {
        $query = Inventaire::with(['user', 'inventaireDetails']);

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        $inventaires = $query->latest('date')->paginate(10)->withQueryString();

        $totalInventaires = Inventaire::count();
        $totalEcartsNegatifs = (int) InventaireDetail::where('ecart', '<', 0)->sum('ecart');
        $totalEcartsPositifs = (int) InventaireDetail::where('ecart', '>', 0)->sum('ecart');

        return view('inventaires.index', compact(
            'inventaires',
            'totalInventaires',
            'totalEcartsNegatifs',
            'totalEcartsPositifs'
        ));
    }

    /**
     * Interface de saisie d'un nouvel inventaire physique.
     */
    public function create(): View
    {
        $produits = Produit::with(['categorie', 'stock'])
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('inventaires.create', compact('produits'));
    }

    /**
     * Valide et régularise un inventaire.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'observation' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produit_id' => ['required', 'exists:produits,id'],
            'items.*.stock_physique' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $inventaire = $this->inventaireService->creerInventaire(
                $request->user(),
                $validated['items'],
                $validated['observation'] ?? null
            );

            return redirect()->route('inventaires.show', $inventaire)
                ->with('success', "L'inventaire du {$inventaire->date->format('d/m/Y H:i')} a été validé. Les stocks ont été régularisés.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Affiche le rapport détaillé d'un inventaire.
     */
    public function show(Inventaire $inventaire): View
    {
        $inventaire->load(['user', 'inventaireDetails.produit.categorie']);

        $totalTheorique = (int) $inventaire->inventaireDetails->sum('stock_theorique');
        $totalPhysique = (int) $inventaire->inventaireDetails->sum('stock_physique');
        $totalEcart = (int) $inventaire->inventaireDetails->sum('ecart');
        $manquantsCount = $inventaire->inventaireDetails->where('ecart', '<', 0)->count();
        $surplusCount = $inventaire->inventaireDetails->where('ecart', '>', 0)->count();

        return view('inventaires.show', compact(
            'inventaire',
            'totalTheorique',
            'totalPhysique',
            'totalEcart',
            'manquantsCount',
            'surplusCount'
        ));
    }
}
