<?php

namespace App\Http\Controllers;

use App\Enums\StatutCaisse;
use App\Models\Caisse;
use App\Models\CompteFinancier;
use App\Models\Depense;
use App\Models\Paiement;
use App\Models\ReglementDette;
use App\Services\CaisseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaisseController extends Controller
{
    public function __construct(
        protected CaisseService $caisseService
    ) {}

    /**
     * Tableau de bord de la caisse et historique des sessions.
     */
    public function index(Request $request): View
    {
        $caisseOuverte = $this->caisseService->getCaisseOuverte($request->user());
        $statsOuverte = $caisseOuverte ? $this->caisseService->getStatistiquesCaisse($caisseOuverte) : null;

        // Soldes reconduits pour le modal d'ouverture (pré-remplissage automatique)
        $soldesReconduction = $this->caisseService->getDerniersSoldesFinaux($request->user());

        $comptesActifs = CompteFinancier::actif()->orderBy('nom')->get();

        $query = Caisse::with('user');

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('date')) {
            $query->whereDate('date_ouverture', $request->input('date'));
        }

        $caisses = $query->latest('date_ouverture')->paginate(10)->withQueryString();

        $totalCaisses = Caisse::count();
        $totalEcarts = Caisse::where('statut', StatutCaisse::FERMEE)->sum('ecart');

        return view('caisses.index', compact(
            'caisseOuverte',
            'statsOuverte',
            'soldesReconduction',
            'comptesActifs',
            'caisses',
            'totalCaisses',
            'totalEcarts'
        ));
    }

    /**
     * Ouvrir une nouvelle session de caisse avec soldes initiaux par compte.
     */
    public function ouvrir(Request $request): RedirectResponse
    {
        $comptesActifs = CompteFinancier::actif()->pluck('id')->toArray();

        $rules = [];
        foreach ($comptesActifs as $compteId) {
            $rules["soldes_initiaux.{$compteId}"] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);
        $soldesInitiaux = $validated['soldes_initiaux'] ?? [];

        try {
            $caisse = $this->caisseService->ouvrirCaisse($request->user(), $soldesInitiaux);

            return redirect()->route('caisses.index')
                ->with('success', 'La caisse a été ouverte avec succès avec un solde total de '.number_format($caisse->solde_initial, 0, ',', ' ').' FCFA.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Clôturer une session de caisse avec soldes finaux par compte.
     */
    public function fermer(Request $request, Caisse $caisse): RedirectResponse
    {
        $details = $caisse->details_comptes['comptes'] ?? [];
        $rules = [];
        foreach (array_keys($details) as $compteId) {
            $rules["soldes_finaux.{$compteId}"] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);
        $soldesFinaux = $validated['soldes_finaux'] ?? [];

        try {
            $caisseCloturee = $this->caisseService->fermerCaisse($caisse, $soldesFinaux);

            return redirect()->route('caisses.show', $caisseCloturee)
                ->with('success', 'La session de caisse a été clôturée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Affiche le rapport détaillé d'une session de caisse.
     */
    public function show(Caisse $caisse): View
    {
        $caisse->load('user');
        $stats = $this->caisseService->getStatistiquesCaisse($caisse);

        $dateDebut = $caisse->date_ouverture;
        $dateFin = $caisse->date_fermeture ?? now();

        $ventes = Paiement::with(['vente.client', 'vente.user'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->latest()
            ->get();

        $reglements = ReglementDette::with(['client', 'user'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->latest()
            ->get();

        $depenses = Depense::with(['user', 'compteFinancier'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->latest()
            ->get();

        return view('caisses.show', compact('caisse', 'stats', 'ventes', 'reglements', 'depenses'));
    }
}
