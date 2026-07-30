<?php

namespace App\Http\Controllers;

use App\Enums\StatutApprovisionnement;
use App\Models\Approvisionnement;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Services\ApprovisionnementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovisionnementController extends Controller
{
    public function __construct(
        protected ApprovisionnementService $approvisionnementService
    ) {}

    /**
     * Liste des approvisionnements avec statistiques et recherche.
     */
    public function index(Request $request): View
    {
        $query = Approvisionnement::with(['fournisseur', 'user', 'details.produit']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhereHas('fournisseur', function ($fq) use ($search) {
                        $fq->where('nom', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->input('fournisseur_id'));
        }

        $approvisionnements = $query->latest('date')->paginate(10)->withQueryString();
        $fournisseurs = Fournisseur::orderBy('nom')->get();

        $totalApprovisionnements = Approvisionnement::count();
        $totalMontantReçus = Approvisionnement::where('statut', StatutApprovisionnement::RECEPTIONNE)->sum('total');
        $enAttenteCount = Approvisionnement::where('statut', StatutApprovisionnement::EN_ATTENTE)->count();

        return view('approvisionnements.index', compact(
            'approvisionnements',
            'fournisseurs',
            'totalApprovisionnements',
            'totalMontantReçus',
            'enAttenteCount'
        ));
    }

    /**
     * Formulaire de création d'un nouvel approvisionnement.
     */
    public function create(): View
    {
        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $produits = Produit::with(['conditionnements', 'stock'])->where('actif', true)->orderBy('nom')->get();
        $statuts = StatutApprovisionnement::cases();

        return view('approvisionnements.create', compact('fournisseurs', 'produits', 'statuts'));
    }

    /**
     * Enregistre un nouvel approvisionnement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fournisseur_id' => ['required', 'exists:fournisseurs,id'],
            'date' => ['nullable', 'date'],
            'statut' => ['required', 'string'],
            'remise' => ['nullable', 'numeric', 'min:0'],
            'tva' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produit_id' => ['required', 'exists:produits,id'],
            'items.*.conditionnement_id' => ['required', 'exists:conditionnements,id'],
            'items.*.quantite_conditionnement' => ['required', 'integer', 'min:1'],
            'items.*.prix_achat' => ['required', 'numeric', 'min:0'],
        ]);

        $approvisionnement = $this->approvisionnementService->creerApprovisionnement(
            $request->user(),
            $validated,
            $request->input('items')
        );

        return redirect()->route('approvisionnements.show', $approvisionnement)
            ->with('success', "L'approvisionnement {$approvisionnement->numero} a été créé avec succès.");
    }

    /**
     * Affiche les détails d'un approvisionnement.
     */
    public function show(Approvisionnement $approvisionnement): View
    {
        $approvisionnement->load(['fournisseur', 'user', 'details.produit', 'details.conditionnement']);

        return view('approvisionnements.show', compact('approvisionnement'));
    }

    /**
     * Réceptionne un approvisionnement et met à jour le stock en unité de base.
     */
    public function receptionner(Request $request, Approvisionnement $approvisionnement): RedirectResponse
    {
        try {
            $this->approvisionnementService->receptionner($approvisionnement, $request->user());

            return redirect()->route('approvisionnements.show', $approvisionnement)
                ->with('success', "L'approvisionnement {$approvisionnement->numero} a été réceptionné. Le stock a été mis à jour.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
