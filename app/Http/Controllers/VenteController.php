<?php

namespace App\Http\Controllers;

use App\Enums\ModePaiement;
use App\Enums\StatutVente;
use App\Models\Client;
use App\Models\CompteFinancier;
use App\Models\Produit;
use App\Models\Vente;
use App\Services\VenteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenteController extends Controller
{
    public function __construct(
        protected VenteService $venteService
    ) {}

    /**
     * Liste des ventes enregistrées avec indicateurs de caisse.
     */
    public function index(Request $request): View
    {
        $query = Vente::with(['client', 'user', 'paiements', 'details.produit']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        $ventes = $query->latest('date')->paginate(10)->withQueryString();

        $totalVentesCount = Vente::count();
        $totalChiffreAffaires = Vente::where('statut', StatutVente::PAYEE)->sum('total');
        $ventesAujourdhui = Vente::whereDate('date', today())->where('statut', StatutVente::PAYEE)->sum('total');

        return view('ventes.index', compact(
            'ventes',
            'totalVentesCount',
            'totalChiffreAffaires',
            'ventesAujourdhui'
        ));
    }

    /**
     * Interface de Caisse / Point de Vente (POS).
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('Administrateur');

        $clients = Client::orderBy('nom')->get();

        $query = Produit::with(['conditionnements', 'stock', 'categorie'])
            ->where('actif', true)
            ->whereHas('stock', fn ($q) => $q->where('quantite', '>', 0));

        if (! $isAdmin) {
            // Pour les utilisateurs non-administrateurs, filtrer pour masquer les produits en stock minimum
            $query->whereHas('stock', function ($q) {
                $q->whereColumn('stocks.quantite', '>', 'produits.stock_min');
            });
        }

        $produits = $query->orderBy('nom')->get();

        $comptesActifs = CompteFinancier::actif()->orderBy('nom')->get();

        $modesPaiement = $comptesActifs->map(function ($compte) {
            return (object) [
                'value' => $compte->mode,
                'label' => $compte->nom,
            ];
        })->values();

        $hasCredit = $modesPaiement->contains('value', ModePaiement::CREDIT->value);
        if (! $hasCredit) {
            $modesPaiement->push((object) [
                'value' => ModePaiement::CREDIT->value,
                'label' => 'Vente à Crédit',
            ]);
        }

        return view('ventes.create', compact('clients', 'produits', 'modesPaiement', 'isAdmin'));
    }

    /**
     * Traite et valide une nouvelle vente.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'required_if:mode_paiement,CREDIT', 'exists:clients,id'],
            'client_comptant_nom' => ['nullable', 'string', 'max:255'],
            'client_comptant_prenom' => ['nullable', 'string', 'max:255'],
            'client_comptant_contact' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'mode_paiement' => ['required', 'string'],
            'montant_paye' => ['nullable', 'numeric', 'min:0'],
            'remise_globale' => ['nullable', 'numeric', 'min:0'],
            'confirmer_vente_stock_min' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.produit_id' => ['required', 'exists:produits,id'],
            'items.*.conditionnement_id' => ['required', 'exists:conditionnements,id'],
            'items.*.quantite_conditionnement' => ['required', 'integer', 'min:1'],
            'items.*.prix' => ['nullable', 'numeric', 'min:0'],
            'items.*.remise' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $vente = $this->venteService->creerVente(
                $request->user(),
                $validated,
                $request->input('items')
            );

            return redirect()->route('ventes.show', $vente)
                ->with('success', "La vente {$vente->numero} a été enregistrée avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Affiche le reçu / ticket de vente.
     */
    public function show(Vente $vente): View
    {
        $vente->load(['client', 'user', 'paiements', 'details.produit', 'details.conditionnement']);

        return view('ventes.show', compact('vente'));
    }
}
