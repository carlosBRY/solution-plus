<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Groupement logique des permissions pour l'interface de gestion.
     */
    protected array $permissionGroups = [
        'Stock & Inventaire' => [
            'gérer-stocks' => 'Consulter l\'état des stocks',
            'ajuster-stock' => '🔒 Ajuster manuellement les quantités de stock',
            'gérer-produits' => 'Créer et éditer les produits',
            'gérer-categories' => 'Gérer les catégories de produits',
            'gérer-inventaires' => 'Créer et effectuer les inventaires',
            'valider-détérioration' => '🔒 Valider les pertes et casses',
        ],
        'Ventes, Caisses & Achats' => [
            'gérer-ventes' => 'Effectuer et consulter les ventes',
            'annuler-vente' => '🔒 Annuler ou modifier une vente validée',
            'gérer-caisses' => 'Gérer les sessions de caisse',
            'gérer-depenses' => 'Enregistrer et consulter les dépenses',
            'gérer-clients' => 'Gérer les clients et leurs crédits',
            'gérer-approvisionnements' => 'Saisir les approvisionnements',
            'gérer-fournisseurs' => 'Gérer les fournisseurs et tarifs',
        ],
        'Finances & Comptes' => [
            'modifier-solde-compte' => '🔒 Réinitialiser / Ajuster le solde des comptes financiers',
            'gérer-parametres' => 'Accéder aux paramètres généraux',
        ],
        'Administration & Utilisateurs' => [
            'voir-dashboard' => 'Accéder au tableau de bord',
            'gérer-utilisateurs' => 'Créer et gérer les comptes utilisateurs',
            'gérer-roles' => '🔒 Gérer les Rôles et les Permissions',
        ],
    ];

    /**
     * Liste des rôles et de leurs permissions associées.
     */
    public function index(): View
    {
        Gate::authorize('gérer-roles');

        $roles = Role::with(['permissions', 'users'])->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $permissionGroups = $this->permissionGroups;

        return view('parametres.roles.index', compact('roles', 'permissions', 'permissionGroups'));
    }

    /**
     * Enregistrer un nouveau rôle avec ses permissions.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('gérer-roles');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('parametres.roles.index')
            ->with('success', "Le rôle '{$role->name}' a été créé avec succès.");
    }

    /**
     * Mettre à jour un rôle et ses permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('gérer-roles');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('parametres.roles.index')
            ->with('success', "Le rôle '{$role->name}' et ses permissions ont été mis à jour.");
    }

    /**
     * Supprimer un rôle personnalisé (sauf Administrateur).
     */
    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('gérer-roles');

        if ($role->name === 'Administrateur') {
            return redirect()->route('parametres.roles.index')
                ->with('error', 'Le rôle Administrateur système ne peut pas être supprimé.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('parametres.roles.index')
                ->with('error', "Impossible de supprimer le rôle '{$role->name}' car il est attribué à des utilisateurs.");
        }

        $nom = $role->name;
        $role->delete();

        return redirect()->route('parametres.roles.index')
            ->with('success', "Le rôle '{$nom}' a été supprimé avec succès.");
    }
}
