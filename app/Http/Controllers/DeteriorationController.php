<?php

namespace App\Http\Controllers;

use App\Enums\DeteriorationCause;
use App\Enums\StatutDeterioration;
use App\Http\Requests\StoreDeteriorationRequest;
use App\Models\Deterioration;
use App\Models\Produit;
use App\Services\DeteriorationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DeteriorationController extends Controller
{
    public function __construct(
        protected DeteriorationService $deteriorationService
    ) {}

    /**
     * Liste des déclarations de détérioration.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Deterioration::class);

        $query = Deterioration::with(['user', 'validateur', 'details.produit']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('numero', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        $deteriorations = $query->latest('date')->paginate(10)->withQueryString();

        $totalDeteriorations = Deterioration::count();
        $valideesCount = Deterioration::where('statut', StatutDeterioration::VALIDEE)->count();
        $brouillonsCount = Deterioration::where('statut', StatutDeterioration::BROUILLON)->count();
        $totalValeurPerte = Deterioration::where('statut', StatutDeterioration::VALIDEE)->sum('total_perte');

        return view('deteriorations.index', compact(
            'deteriorations',
            'totalDeteriorations',
            'valideesCount',
            'brouillonsCount',
            'totalValeurPerte'
        ));
    }

    /**
     * Formulaire de création d'une détérioration.
     */
    public function create(): View
    {
        Gate::authorize('create', Deterioration::class);

        $produits = Produit::with(['conditionnements', 'stock'])->where('actif', true)->orderBy('nom')->get();
        $causes = DeteriorationCause::cases();

        return view('deteriorations.create', compact('produits', 'causes'));
    }

    /**
     * Enregistre une détérioration en BROUILLON.
     */
    public function store(StoreDeteriorationRequest $request): RedirectResponse
    {
        Gate::authorize('create', Deterioration::class);

        $deterioration = $this->deteriorationService->creerBrouillon(
            $request->user(),
            $request->validated(),
            $request->input('items')
        );

        return redirect()->route('deteriorations.show', $deterioration)
            ->with('success', "La déclaration de détérioration {$deterioration->numero} a été créée en brouillon.");
    }

    /**
     * Affiche les détails d'une détérioration.
     */
    public function show(Deterioration $deterioration): View
    {
        Gate::authorize('view', $deterioration);

        $deterioration->load(['user', 'validateur', 'details.produit', 'details.conditionnement']);

        return view('deteriorations.show', compact('deterioration'));
    }

    /**
     * Valide la détérioration et décrémente le stock en unités de base.
     */
    public function valider(Request $request, Deterioration $deterioration): RedirectResponse
    {
        Gate::authorize('validate', $deterioration);

        try {
            $this->deteriorationService->valider($deterioration, $request->user());

            return redirect()->route('deteriorations.show', $deterioration)
                ->with('success', "La détérioration {$deterioration->numero} a été validée avec succès. Le stock a été décrémenté.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Supprime une détérioration en brouillon.
     */
    public function destroy(Deterioration $deterioration): RedirectResponse
    {
        Gate::authorize('delete', $deterioration);

        try {
            $numero = $deterioration->numero;
            $this->deteriorationService->supprimer($deterioration);

            return redirect()->route('deteriorations.index')
                ->with('success', "La détérioration {$numero} a été supprimée.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
