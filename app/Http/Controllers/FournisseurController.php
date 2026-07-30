<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FournisseurController extends Controller
{
    /**
     * Liste des fournisseurs avec recherche et métriques.
     */
    public function index(Request $request): View
    {
        $query = Fournisseur::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ville', 'like', "%{$search}%");
            });
        }

        $fournisseurs = $query->latest()->paginate(10)->withQueryString();
        $totalFournisseurs = Fournisseur::count();

        return view('fournisseurs.index', compact('fournisseurs', 'totalFournisseurs'));
    }

    /**
     * Enregistre un nouveau fournisseur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:100'],
            'pays' => ['nullable', 'string', 'max:100'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);

        $fournisseur = Fournisseur::create($validated);

        return redirect()->route('fournisseurs.show', $fournisseur)
            ->with('success', "Le fournisseur '{$fournisseur->nom}' a été créé avec succès.");
    }

    /**
     * Affiche la fiche détaillée d'un fournisseur avec son historique d'approvisionnements.
     */
    public function show(Fournisseur $fournisseur): View
    {
        $fournisseur->load(['approvisionnements' => fn ($q) => $q->latest('date')->take(10)]);
        $totalApprovisionnementsVal = $fournisseur->approvisionnements()->sum('total');

        return view('fournisseurs.show', compact('fournisseur', 'totalApprovisionnementsVal'));
    }

    /**
     * Met à jour un fournisseur.
     */
    public function update(Request $request, Fournisseur $fournisseur): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:100'],
            'pays' => ['nullable', 'string', 'max:100'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);

        $fournisseur->update($validated);

        return redirect()->route('fournisseurs.show', $fournisseur)
            ->with('success', "Informations du fournisseur '{$fournisseur->nom}' mises à jour.");
    }

    /**
     * Supprime un fournisseur.
     */
    public function destroy(Fournisseur $fournisseur): RedirectResponse
    {
        $nom = $fournisseur->nom;
        $fournisseur->delete();

        return redirect()->route('fournisseurs.index')
            ->with('success', "Le fournisseur '{$nom}' a été supprimé.");
    }
}
