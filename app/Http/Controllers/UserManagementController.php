<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Affiche la liste des utilisateurs.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->input('statut') === 'actif');
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        $totalUsers = User::count();
        $activeUsers = User::where('actif', true)->count();
        $inactiveUsers = User::where('actif', false)->count();

        return view('parametres.users.index', compact(
            'users',
            'roles',
            'totalUsers',
            'activeUsers',
            'inactiveUsers'
        ));
    }

    /**
     * Affiche le formulaire de création d'un utilisateur.
     */
    public function create(): View
    {
        $roles = Role::all();

        return view('parametres.users.create', compact('roles'));
    }

    /**
     * Enregistre un nouvel utilisateur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telephone' => ['nullable', 'string', 'max:25'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'actif' => true,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('parametres.users.index')
            ->with('success', "L'utilisateur {$user->name} a été créé avec succès.");
    }

    /**
     * Affiche le formulaire de modification d'un utilisateur.
     */
    public function edit(User $user): View
    {
        $roles = Role::all();

        return view('parametres.users.edit', compact('user', 'roles'));
    }

    /**
     * Met à jour un utilisateur existant.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'telephone' => ['nullable', 'string', 'max:25'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('parametres.users.index')
            ->with('success', "L'utilisateur {$user->name} a été modifié avec succès.");
    }

    /**
     * Active ou désactive un utilisateur.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['actif' => ! $user->actif]);

        $status = $user->actif ? 'activé' : 'désactivé';

        return redirect()->route('parametres.users.index')
            ->with('success', "L'utilisateur {$user->name} a été {$status}.");
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('parametres.users.index')
            ->with('success', "Le mot de passe de {$user->name} a été réinitialisé avec succès.");
    }
}
