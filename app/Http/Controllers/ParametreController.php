<?php

namespace App\Http\Controllers;

use App\Models\CompteFinancier;
use App\Models\Conditionnement;
use App\Models\Parametre;
use App\Models\Produit;
use App\Services\CompteFinancierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

    /**
     * Liste et gestion des comptes financiers / moyens de paiement.
     */
    public function comptes(): View
    {
        $comptes = CompteFinancier::latest()->get();
        $modesDisponibles = [
            'ESPECES' => 'Espèces',
            'ORANGE_MONEY' => 'Orange Money',
            'MOOV_MONEY' => 'Moov Money',
            'WAVE' => 'Wave',
            'CARTE' => 'Carte Bancaire',
            'VIREMENT' => 'Virement / Chèque',
            'CREDIT' => 'Compte Crédit Client',
        ];

        return view('parametres.comptes', compact('comptes', 'modesDisponibles'));
    }

    /**
     * Ajouter un compte financier / moyen de paiement.
     */
    public function storeCompte(Request $request, CompteFinancierService $service): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'string', 'max:50', 'unique:comptes_financiers,mode'],
            'solde_initial' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $compte = CompteFinancier::create([
            'nom' => $validated['nom'],
            'mode' => strtoupper($validated['mode']),
            'solde_initial' => $validated['solde_initial'],
            'solde_courant' => $validated['solde_initial'],
            'actif' => true,
            'description' => $validated['description'] ?? null,
        ]);

        if ($validated['solde_initial'] > 0) {
            $service->initialiserSolde($compte, $request->user(), (float) $validated['solde_initial']);
        }

        return redirect()->route('parametres.comptes.index')
            ->with('success', "Le compte '{$compte->nom}' a été créé avec un solde initial de ".number_format($compte->solde_initial).' FCFA.');
    }

    /**
     * Modifier un compte financier.
     */
    public function updateCompte(Request $request, CompteFinancier $compte): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $compte->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
            'actif' => $request->has('actif'),
        ]);

        return redirect()->route('parametres.comptes.index')
            ->with('success', "Le compte '{$compte->nom}' a été mis à jour.");
    }

    /**
     * Re-initialiser le solde d'un compte financier.
     */
    public function initialiserSolde(Request $request, CompteFinancier $compte, CompteFinancierService $service): RedirectResponse
    {
        Gate::authorize('modifier-solde-compte');

        $validated = $request->validate([
            'solde' => ['required', 'numeric', 'min:0'],
        ]);

        $service->initialiserSolde($compte, $request->user(), (float) $validated['solde']);

        return redirect()->route('parametres.comptes.index')
            ->with('success', "Le solde du compte '{$compte->nom}' a été réinitialisé à ".number_format($validated['solde']).' FCFA.');
    }
}
