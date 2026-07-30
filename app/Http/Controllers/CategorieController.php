<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategorieController extends Controller
{
    /**
     * Affiche la liste des catégories avec la modale de création et d'édition.
     */
    public function index(Request $request): View
    {
        $query = Categorie::withCount('produits');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->latest()->paginate(10)->withQueryString();
        $totalCategories = Categorie::count();

        return view('categories.index', compact('categories', 'totalCategories'));
    }

    /**
     * Enregistre une nouvelle catégorie.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:categories,nom'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $categorie = Categorie::create($validated);

        return redirect()->route('categories.index')
            ->with('success', "La catégorie '{$categorie->nom}' a été créée avec succès.");
    }

    /**
     * Met à jour une catégorie existante.
     */
    public function update(Request $request, Categorie $categorie): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:categories,nom,'.$categorie->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $categorie->update($validated);

        return redirect()->route('categories.index')
            ->with('success', "La catégorie '{$categorie->nom}' a été mise à jour avec succès.");
    }

    /**
     * Supprime une catégorie.
     */
    public function destroy(Categorie $categorie): RedirectResponse
    {
        if ($categorie->produits()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', "Impossible de supprimer la catégorie '{$categorie->nom}' car elle contient des produits.");
        }

        $nom = $categorie->nom;
        $categorie->delete();

        return redirect()->route('categories.index')
            ->with('success', "La catégorie '{$nom}' a été supprimée.");
    }
}
