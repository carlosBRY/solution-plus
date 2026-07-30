<?php

namespace App\Http\Controllers;

use App\Models\Conditionnement;
use App\Models\Parametre;
use App\Models\Produit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ParametreController extends Controller
{
    /**
     * Page principale des paramètres généraux de la cave.
     */
    public function index(): View
    {
        $parametre = Parametre::first() ?? Parametre::create([
            'nom_cave' => 'Cave Prestige d\'Or',
            'devise' => 'FCFA',
            'tva' => 18.00,
            'stock_min_global' => 5,
        ]);

        $totalConditionnements = Conditionnement::count();
        $totalProduits = Produit::count();

        return view('parametres.index', compact('parametre', 'totalConditionnements', 'totalProduits'));
    }

    /**
     * Mettre à jour les paramètres généraux.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $parametre = Parametre::firstOrFail();

        $validated = $request->validate([
            'nom_cave' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'devise' => ['required', 'string', 'max:20'],
            'tva' => ['required', 'numeric', 'min:0', 'max:100'],
            'stock_min_global' => ['required', 'integer', 'min:0'],
            'message_ticket' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($parametre->logo && Storage::disk('public')->exists($parametre->logo)) {
                Storage::disk('public')->delete($parametre->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $parametre->update($validated);

        return redirect()->route('parametres.index')
            ->with('success', 'Les paramètres généraux de la cave ont été mis à jour avec succès.');
    }

    /**
     * Gestion centralisée des conditionnements dans l'onglet Paramètres.
     */
    public function conditionnements(Request $request): View
    {
        $query = Conditionnement::with('produit.categorie');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nom', 'like', "%{$search}%")
                ->orWhere('code_barre', 'like', "%{$search}%")
                ->orWhereHas('produit', function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");
                });
        }

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->input('produit_id'));
        }

        $conditionnements = $query->latest()->paginate(12)->withQueryString();
        $produits = Produit::orderBy('nom')->get();

        $totalConditionnements = Conditionnement::count();
        $conditionnementsVenteCount = Conditionnement::where('is_vente', true)->count();
        $conditionnementsAchatCount = Conditionnement::where('is_achat', true)->count();

        return view('parametres.conditionnements', compact(
            'conditionnements',
            'produits',
            'totalConditionnements',
            'conditionnementsVenteCount',
            'conditionnementsAchatCount'
        ));
    }

    /**
     * Ajouter un conditionnement depuis les Paramètres.
     */
    public function storeConditionnement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produit_id' => ['required', 'exists:produits,id'],
            'nom' => ['required', 'string', 'max:255'],
            'quantite_unite_base' => ['required', 'integer', 'min:1'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'prix_vente' => ['nullable', 'numeric', 'min:0'],
            'code_barre' => ['nullable', 'string', 'max:100'],
            'is_achat' => ['nullable', 'boolean'],
            'is_vente' => ['nullable', 'boolean'],
        ]);

        $produit = Produit::findOrFail($validated['produit_id']);

        Conditionnement::create([
            'produit_id' => $produit->id,
            'nom' => $validated['nom'],
            'quantite_unite_base' => $validated['quantite_unite_base'],
            'prix_achat' => $validated['prix_achat'] ?? null,
            'prix_vente' => $validated['prix_vente'] ?? null,
            'code_barre' => $validated['code_barre'] ?? null,
            'is_achat' => $request->has('is_achat'),
            'is_vente' => $request->has('is_vente'),
            'is_par_defaut' => false,
        ]);

        return redirect()->route('parametres.conditionnements')
            ->with('success', "Conditionnement '{$validated['nom']}' créé avec succès pour le produit '{$produit->nom}'.");
    }

    /**
     * Mettre à jour un conditionnement depuis les Paramètres.
     */
    public function updateConditionnement(Request $request, Conditionnement $conditionnement): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'quantite_unite_base' => ['required', 'integer', 'min:1'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'prix_vente' => ['nullable', 'numeric', 'min:0'],
            'code_barre' => ['nullable', 'string', 'max:100'],
            'is_achat' => ['nullable', 'boolean'],
            'is_vente' => ['nullable', 'boolean'],
        ]);

        $validated['is_achat'] = $request->has('is_achat');
        $validated['is_vente'] = $request->has('is_vente');

        $conditionnement->update($validated);

        return redirect()->route('parametres.conditionnements')
            ->with('success', "Le conditionnement '{$conditionnement->nom}' a été mis à jour.");
    }

    /**
     * Supprimer un conditionnement.
     */
    public function destroyConditionnement(Conditionnement $conditionnement): RedirectResponse
    {
        if ($conditionnement->is_par_defaut) {
            return redirect()->route('parametres.conditionnements')
                ->with('error', 'Impossible de supprimer le conditionnement de référence par défaut.');
        }

        $nom = $conditionnement->nom;
        $conditionnement->delete();

        return redirect()->route('parametres.conditionnements')
            ->with('success', "Le conditionnement '{$nom}' a été supprimé.");
    }
}
