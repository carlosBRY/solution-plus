<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompteFinancier;
use App\Services\ClientCreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        protected ClientCreditService $clientCreditService
    ) {}

    /**
     * Liste des clients avec filtres et indicateurs de crédit.
     */
    public function index(Request $request): View
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('dette_filter')) {
            if ($request->input('dette_filter') === 'avec_dette') {
                $query->where('solde', '>', 0);
            } elseif ($request->input('dette_filter') === 'sans_dette') {
                $query->where('solde', '<=', 0);
            }
        }

        $clients = $query->latest()->paginate(10)->withQueryString();

        $totalClients = Client::count();
        $totalCreances = Client::sum('solde');
        $clientsEndettesCount = Client::where('solde', '>', 0)->count();

        return view('clients.index', compact(
            'clients',
            'totalClients',
            'totalCreances',
            'clientsEndettesCount'
        ));
    }

    /**
     * Enregistre un nouveau client.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'plafond_credit' => ['nullable', 'numeric', 'min:0'],
            'solde_initial' => ['nullable', 'numeric', 'min:0'],
        ]);

        $soldeInitial = (float) ($validated['solde_initial'] ?? 0);

        $client = Client::create([
            'nom' => $validated['nom'],
            'telephone' => $validated['telephone'] ?? null,
            'email' => $validated['email'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'plafond_credit' => $validated['plafond_credit'] ?? 0,
            'solde' => $soldeInitial,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', "Le client '{$client->nom}' a été créé avec succès.");
    }

    /**
     * Affiche la fiche détaillée d'un client (historique des ventes, état du crédit, remboursements).
     */
    public function show(Client $client): View
    {
        $client->load([
            'ventes' => fn ($q) => $q->latest('date')->take(10),
            'reglementsDettes' => fn ($q) => $q->with('user')->latest('date')->take(10),
            'ajustementsCredit' => fn ($q) => $q->with('user')->latest('date')->take(10),
        ]);

        $totalAchats = $client->ventes()->sum('total');
        $comptes = CompteFinancier::actif()->orderBy('nom')->get();
        $isAdmin = $client->id ? auth()->user()->hasRole('Administrateur') : false;

        return view('clients.show', compact('client', 'totalAchats', 'comptes', 'isAdmin'));
    }

    /**
     * Met à jour les informations d'un client.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'plafond_credit' => ['required', 'numeric', 'min:0'],
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', "Informations du client '{$client->nom}' mises à jour.");
    }

    /**
     * Enregistre un règlement de dette pour un client.
     */
    public function reglerDette(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'montant' => ['required', 'numeric', 'min:1'],
            'mode' => ['nullable', 'string'],
            'compte_financier_id' => ['required', 'exists:comptes_financiers,id'],
            'reference' => ['nullable', 'string', 'max:100'],
            'observation' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $reglement = $this->clientCreditService->reglerDette($client, $request->user(), $validated);

            return redirect()->route('clients.show', $client)
                ->with('success', "Règlement de {$reglement->montant} FCFA enregistré avec succès (Reçu N° {$reglement->numero}).");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ajoute un crédit (dette) à un client sans passer par une vente.
     * Accessible aux Administrateurs et Gérants.
     */
    public function ajouterCredit(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'montant' => ['required', 'numeric', 'min:1'],
            'motif' => ['required', 'string', 'max:500'],
        ]);

        try {
            $ajustement = $this->clientCreditService->ajouterCredit($client, $request->user(), $validated);
            $montantFormate = number_format($ajustement->montant, 0, ',', ' ');

            return redirect()->route('clients.show', $client)
                ->with('success', "Crédit de {$montantFormate} FCFA ajouté avec succès pour {$client->nom}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ajuste (corrige) le solde d'un client.
     * Réservé exclusivement aux Administrateurs.
     */
    public function ajusterCredit(Request $request, Client $client): RedirectResponse
    {
        if (! $request->user()->hasRole('Administrateur')) {
            abort(403, 'Seul un administrateur peut ajuster le solde d\'un client.');
        }

        $validated = $request->validate([
            'nouveau_solde' => ['required', 'numeric', 'min:0'],
            'motif' => ['required', 'string', 'max:500'],
        ]);

        try {
            $ajustement = $this->clientCreditService->ajusterCredit($client, $request->user(), $validated);
            $soldeFormate = number_format($ajustement->solde_apres, 0, ',', ' ');

            return redirect()->route('clients.show', $client)
                ->with('success', "Solde du client ajusté à {$soldeFormate} FCFA.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Supprime un client.
     */
    public function destroy(Client $client): RedirectResponse
    {
        if ($client->solde > 0) {
            return redirect()->back()
                ->with('error', "Impossible de supprimer le client '{$client->nom}' car il possède une dette de {$client->solde} FCFA.");
        }

        $nom = $client->nom;
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Le client '{$nom}' a été supprimé.");
    }
}
