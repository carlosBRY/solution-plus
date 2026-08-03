<?php

namespace App\Http\Controllers;

use App\Models\CompteFinancier;
use App\Models\MouvementCompte;
use App\Services\CompteFinancierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompteFinancierController extends Controller
{
    public function __construct(
        protected CompteFinancierService $compteService
    ) {}

    /**
     * Dashboard Caisse Principale & Vue d'ensemble de tous les comptes.
     */
    public function index(): View
    {
        $comptes = CompteFinancier::actif()->orderBy('nom')->get();
        $soldeTotal = $this->compteService->getSoldeTotal();

        // Statistiques du jour
        $aujourdhui = now()->startOfDay();
        $totalCreditsJour = (float) MouvementCompte::where('type', 'CREDIT')
            ->where('created_at', '>=', $aujourdhui)
            ->sum('montant');
        $totalDebitsJour = (float) MouvementCompte::where('type', 'DEBIT')
            ->where('created_at', '>=', $aujourdhui)
            ->sum('montant');

        // Mouvements récents globaux (10 derniers)
        $mouvementsRecents = MouvementCompte::with(['compteFinancier', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('comptes.index', compact(
            'comptes',
            'soldeTotal',
            'totalCreditsJour',
            'totalDebitsJour',
            'mouvementsRecents'
        ));
    }

    /**
     * Journal d'audit et détails d'un compte financier spécifique.
     */
    public function show(CompteFinancier $compte, Request $request): View
    {
        $query = $compte->mouvementsCompte()->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->input('date_fin'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('motif', 'like', "%{$search}%");
        }

        $mouvements = $query->latest()->paginate(15)->withQueryString();

        // Statistiques propres à ce compte
        $totalEntrees = (float) $compte->mouvementsCompte()->where('type', 'CREDIT')->sum('montant');
        $totalSorties = (float) $compte->mouvementsCompte()->where('type', 'DEBIT')->sum('montant');

        return view('comptes.show', compact('compte', 'mouvements', 'totalEntrees', 'totalSorties'));
    }

    /**
     * Effectuer un transfert d'argent entre deux comptes financiers.
     */
    public function transferer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_id' => ['required', 'exists:comptes_financiers,id'],
            'destination_id' => ['required', 'exists:comptes_financiers,id', 'different:source_id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'motif' => ['required', 'string', 'max:255'],
        ]);

        $source = CompteFinancier::findOrFail($validated['source_id']);
        $destination = CompteFinancier::findOrFail($validated['destination_id']);

        if (! $source->actif || ! $destination->actif) {
            return redirect()->back()->withInput()
                ->with('error', 'Les deux comptes doivent être actifs pour effectuer un transfert.');
        }

        try {
            $this->compteService->transferer(
                $source,
                $destination,
                $request->user(),
                (float) $validated['montant'],
                $validated['motif']
            );

            return redirect()->route('comptes.index')
                ->with('success', 'Transfert de '.number_format($validated['montant'])." FCFA de {$source->nom} vers {$destination->nom} effectué avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Effectuer un dépôt (ajout d'argent) sur un compte financier / caisse.
     */
    public function deposer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'compte_id' => ['required', 'exists:comptes_financiers,id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'motif' => ['required', 'string', 'max:255'],
        ]);

        $compte = CompteFinancier::findOrFail($validated['compte_id']);

        if (! $compte->actif) {
            return redirect()->back()->withInput()
                ->with('error', 'Le compte sélectionné doit être actif.');
        }

        try {
            $this->compteService->crediter(
                $compte,
                $request->user(),
                (float) $validated['montant'],
                $validated['motif']
            );

            return redirect()->back()
                ->with('success', 'Ajout de '.number_format($validated['montant'], 0, ',', ' ')." FCFA sur la caisse {$compte->nom} effectué avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Effectuer un retrait (sortie d'argent) sur un compte financier / caisse.
     */
    public function retirer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'compte_id' => ['required', 'exists:comptes_financiers,id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'motif' => ['required', 'string', 'max:255'],
        ]);

        $compte = CompteFinancier::findOrFail($validated['compte_id']);

        if (! $compte->actif) {
            return redirect()->back()->withInput()
                ->with('error', 'Le compte sélectionné doit être actif.');
        }

        try {
            $this->compteService->debiter(
                $compte,
                $request->user(),
                (float) $validated['montant'],
                $validated['motif']
            );

            return redirect()->back()
                ->with('success', 'Retrait de '.number_format($validated['montant'], 0, ',', ' ')." FCFA sur la caisse {$compte->nom} effectué avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
