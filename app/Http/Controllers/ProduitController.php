<?php

namespace App\Http\Controllers;

use App\Enums\MouvementType;
use App\Models\Categorie;
use App\Models\Conditionnement;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProduitController extends Controller
{
    /**
     * Affiche la liste des produits avec leurs conditionnements et stocks.
     */
    public function index(Request $request): View
    {
        $query = Produit::with(['categorie', 'stock', 'conditionnements']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('code_barre', 'like', "%{$search}%")
                    ->orWhere('marque', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->input('categorie_id'));
        }

        if ($request->filled('stock_status')) {
            if ($request->input('stock_status') === 'alerte') {
                $query->whereHas('stock', function ($q) {
                    $q->whereColumn('stocks.quantite', '<=', 'produits.stock_min');
                });
            } elseif ($request->input('stock_status') === 'epuise') {
                $query->whereHas('stock', function ($q) {
                    $q->where('stocks.quantite', '<=', 0);
                });
            }
        }

        $produits = $query->latest()->paginate(10)->withQueryString();
        $categories = Categorie::orderBy('nom')->get();

        $totalProduits = Produit::count();
        $totalStockBouteilles = Stock::sum('quantite');
        $produitsAlerteCount = Produit::whereHas('stock', function ($q) {
            $q->whereColumn('stocks.quantite', '<=', 'produits.stock_min');
        })->count();

        return view('produits.index', compact(
            'produits',
            'categories',
            'totalProduits',
            'totalStockBouteilles',
            'produitsAlerteCount'
        ));
    }

    /**
     * Enregistre un nouveau produit, ses conditionnements et trace le stock initial par mouvement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categorie_id' => ['required', 'exists:categories,id'],
            'nom' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100', 'unique:produits,reference'],
            'code_barre' => ['nullable', 'string', 'max:100'],
            'marque' => ['nullable', 'string', 'max:100'],
            'unite_base' => ['required', 'string', 'max:50'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'prix_vente' => ['nullable', 'numeric', 'min:0'],
            'stock_min' => ['required', 'integer', 'min:0'],
            'quantite_initiale' => ['nullable', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if (empty($validated['reference'])) {
            $validated['reference'] = 'PRD-'.strtoupper(Str::random(6));
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('produits', 'public');
        }

        $validated['prix_achat'] = (float) ($validated['prix_achat'] ?? 0);
        $validated['prix_vente'] = (float) ($validated['prix_vente'] ?? 0);
        $quantiteInitiale = (int) ($validated['quantite_initiale'] ?? 0);
        unset($validated['quantite_initiale']);

        DB::transaction(function () use ($validated, $quantiteInitiale, $request) {
            $produit = Produit::create($validated);

            // Conditionnement par défaut "Bouteille" (ou nom basé sur l'unité de base)
            $nomConditionnementDefaut = ucfirst(strtolower($produit->unite_base));
            $conditionnementDefaut = Conditionnement::create([
                'produit_id' => $produit->id,
                'nom' => $nomConditionnementDefaut,
                'quantite_unite_base' => 1,
                'prix_achat' => $produit->prix_achat,
                'prix_vente' => $produit->prix_vente,
                'code_barre' => $produit->code_barre,
                'is_achat' => true,
                'is_vente' => true,
                'is_par_defaut' => true,
            ]);

            // Initialiser le stock à 0
            $stock = Stock::create([
                'produit_id' => $produit->id,
                'quantite' => 0,
            ]);

            // Si une quantité initiale a été renseignée, elle DOIT produire un mouvement de type STOCK_INITIAL
            if ($quantiteInitiale > 0) {
                $stock->update(['quantite' => $quantiteInitiale]);

                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'user_id' => $request->user()->id,
                    'conditionnement_id' => $conditionnementDefaut->id,
                    'type' => MouvementType::STOCK_INITIAL,
                    'quantite' => $quantiteInitiale,
                    'quantite_conditionnement' => $quantiteInitiale,
                    'coefficient_conversion' => 1,
                    'stock_avant' => 0,
                    'stock_apres' => $quantiteInitiale,
                    'motif' => 'Initialisation du stock de départ lors de la création',
                    'reference' => $produit->reference,
                ]);
            }
        });

        return redirect()->route('produits.index')
            ->with('success', "Le produit '{$validated['nom']}' a été créé avec succès avec son conditionnement de référence.");
    }

    /**
     * Met à jour un produit existant.
     */
    public function update(Request $request, Produit $produit): RedirectResponse
    {
        $validated = $request->validate([
            'categorie_id' => ['required', 'exists:categories,id'],
            'nom' => ['required', 'string', 'max:255'],
            'reference' => ['required', 'string', 'max:100', 'unique:produits,reference,'.$produit->id],
            'code_barre' => ['nullable', 'string', 'max:100'],
            'marque' => ['nullable', 'string', 'max:100'],
            'unite_base' => ['required', 'string', 'max:50'],
            'prix_achat' => ['nullable', 'numeric', 'min:0'],
            'prix_vente' => ['nullable', 'numeric', 'min:0'],
            'stock_min' => ['required', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'actif' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('photo')) {
            if ($produit->photo && Storage::disk('public')->exists($produit->photo)) {
                Storage::disk('public')->delete($produit->photo);
            }
            $validated['photo'] = $request->file('photo')->store('produits', 'public');
        }

        $validated['actif'] = $request->has('actif');
        $validated['prix_achat'] = (float) ($validated['prix_achat'] ?? 0);
        $validated['prix_vente'] = (float) ($validated['prix_vente'] ?? 0);

        $produit->update($validated);

        // Mettre à jour également le conditionnement par défaut
        $conditionnementDefaut = $produit->conditionnementParDefaut;
        if ($conditionnementDefaut) {
            $conditionnementDefaut->update([
                'prix_achat' => $produit->prix_achat,
                'prix_vente' => $produit->prix_vente,
                'code_barre' => $produit->code_barre,
            ]);
        }

        return redirect()->route('produits.index')
            ->with('success', "Le produit '{$produit->nom}' a été mis à jour avec succès.");
    }

    /**
     * Ajoute un nouveau conditionnement à un produit (ex: Pack de 6, Caisse de 12).
     */
    public function storeConditionnement(Request $request, Produit $produit): RedirectResponse
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

        return redirect()->route('produits.index')
            ->with('success', "Conditionnement '{$validated['nom']}' ajouté pour {$produit->nom}.");
    }

    /**
     * Supprime un produit.
     */
    public function destroy(Produit $produit): RedirectResponse
    {
        if ($produit->photo && Storage::disk('public')->exists($produit->photo)) {
            Storage::disk('public')->delete($produit->photo);
        }

        $nom = $produit->nom;
        $produit->delete();

        return redirect()->route('produits.index')
            ->with('success', "Le produit '{$nom}' a été supprimé.");
    }
}
