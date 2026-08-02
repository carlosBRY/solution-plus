<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Models\Produit;
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
     * Affiche la fiche détaillée d'un fournisseur avec son historique d'approvisionnements et ses tarifs par conditionnement.
     */
    public function show(Fournisseur $fournisseur): View
    {
        $fournisseur->load([
            'approvisionnements' => fn ($q) => $q->latest('date')->take(10),
            'tarifs',
        ]);
        $totalApprovisionnementsVal = $fournisseur->approvisionnements()->sum('total');
        $produitsWithConds = Produit::with('conditionnements')->orderBy('nom')->get();

        return view('fournisseurs.show', compact('fournisseur', 'totalApprovisionnementsVal', 'produitsWithConds'));
    }

    /**
     * Enregistre ou met à jour les tarifs d'achat par conditionnement pour ce fournisseur.
     */
    public function updateTarifs(Request $request, Fournisseur $fournisseur): RedirectResponse
    {
        $validated = $request->validate([
            'tarifs' => ['nullable', 'array'],
            'tarifs.*.produit_id' => ['required', 'exists:produits,id'],
            'tarifs.*.conditionnement_id' => ['required', 'exists:conditionnements,id'],
            'tarifs.*.prix_achat' => ['nullable', 'numeric', 'min:0'],
        ]);

        $syncData = [];
        if (! empty($validated['tarifs'])) {
            foreach ($validated['tarifs'] as $tarif) {
                if (isset($tarif['prix_achat']) && $tarif['prix_achat'] !== '' && $tarif['prix_achat'] !== null && (float) $tarif['prix_achat'] > 0) {
                    $syncData[$tarif['conditionnement_id']] = [
                        'produit_id' => $tarif['produit_id'],
                        'prix_achat' => (float) $tarif['prix_achat'],
                    ];
                }
            }
        }

        $fournisseur->tarifs()->sync($syncData);

        return redirect()->route('fournisseurs.show', $fournisseur)
            ->with('success', "Grille tarifaire des conditionnements mise à jour pour '{$fournisseur->nom}'.");
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
