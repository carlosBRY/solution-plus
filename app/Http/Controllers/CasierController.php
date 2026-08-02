<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ConsignationCasier;
use App\Models\TypeCasier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CasierController extends Controller
{
    /**
     * Tableau de bord et gestion des casiers & bouteilles consignées.
     */
    public function index(Request $request): View
    {
        Gate::authorize('gérer-casiers');

        $typeCasiers = TypeCasier::orderBy('nom')->get();
        if ($typeCasiers->isEmpty()) {
            TypeCasier::create(['nom' => 'Casier 12 Bouteilles', 'capacite_bouteilles' => 12, 'quantite_casiers_cave' => 0, 'quantite_bouteilles_seules_cave' => 0]);
            TypeCasier::create(['nom' => 'Casier 24 Bouteilles', 'capacite_bouteilles' => 24, 'quantite_casiers_cave' => 0, 'quantite_bouteilles_seules_cave' => 0]);
            TypeCasier::create(['nom' => 'Casier 20 Bouteilles', 'capacite_bouteilles' => 20, 'quantite_casiers_cave' => 0, 'quantite_bouteilles_seules_cave' => 0]);
            $typeCasiers = TypeCasier::orderBy('nom')->get();
        }

        $clients = Client::orderBy('nom')->get();

        // Statistiques globales du parc de casiers
        $totalCasiersCave = $typeCasiers->sum('quantite_casiers_cave');
        $totalBouteillesSeulesCave = $typeCasiers->sum('quantite_bouteilles_seules_cave');

        // Total des casiers et bouteilles prêtés aux clients (en cours)
        $pretsEnCours = ConsignationCasier::where('statut', 'EN_COURS')
            ->where('type_mouvement', 'PRET_CLIENT')
            ->get();
        $totalCasiersPretes = $pretsEnCours->sum('nombre_casiers');
        $totalBouteillesPretees = $pretsEnCours->sum('nombre_bouteilles_seules');

        // Total des casiers et bouteilles déposés à la cave (en cours)
        $depotsEnCours = ConsignationCasier::where('statut', 'EN_COURS')
            ->where('type_mouvement', 'DEPOT_CAVE')
            ->get();
        $totalCasiersDeposes = $depotsEnCours->sum('nombre_casiers');
        $totalBouteillesDeposees = $depotsEnCours->sum('nombre_bouteilles_seules');

        // Liste des consignations avec filtres optionnels
        $query = ConsignationCasier::with(['typeCasier', 'client', 'user'])
            ->orderBy('date_mouvement', 'desc');

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('type_mouvement')) {
            $query->where('type_mouvement', $request->input('type_mouvement'));
        }

        $consignations = $query->get();

        return view('casiers.index', compact(
            'typeCasiers',
            'clients',
            'totalCasiersCave',
            'totalBouteillesSeulesCave',
            'totalCasiersPretes',
            'totalBouteillesPretees',
            'totalCasiersDeposes',
            'totalBouteillesDeposees',
            'consignations'
        ));
    }

    /**
     * Crée un nouveau type de casier.
     */
    public function storeType(Request $request): RedirectResponse
    {
        Gate::authorize('gérer-casiers');

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'capacite_bouteilles' => ['required', 'integer', 'min:1'],
            'quantite_casiers_cave' => ['required', 'integer', 'min:0'],
            'quantite_bouteilles_seules_cave' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        TypeCasier::create($validated);

        return redirect()->route('casiers.index')
            ->with('success', "Le type de casier '{$validated['nom']}' a été créé avec succès.");
    }

    /**
     * Initialise ou ajoute du stock physique d'emballages (casiers/bouteilles) dans la cave.
     */
    public function initialiserStockGlobal(Request $request): RedirectResponse
    {
        Gate::authorize('gérer-casiers');

        $validated = $request->validate([
            'type_casier_id' => ['required', 'exists:type_casiers,id'],
            'quantite_casiers_cave' => ['required', 'integer', 'min:0'],
            'quantite_bouteilles_seules_cave' => ['required', 'integer', 'min:0'],
            'mode_saisie' => ['required', 'in:DEFINIR,AJOUTER'],
        ]);

        $typeCasier = TypeCasier::findOrFail($validated['type_casier_id']);

        if ($validated['mode_saisie'] === 'AJOUTER') {
            $typeCasier->increment('quantite_casiers_cave', $validated['quantite_casiers_cave']);
            $typeCasier->increment('quantite_bouteilles_seules_cave', $validated['quantite_bouteilles_seules_cave']);
            $msg = "Ajout de {$validated['quantite_casiers_cave']} casier(s) et {$validated['quantite_bouteilles_seules_cave']} bouteille(s) au stock de '{$typeCasier->nom}'.";
        } else {
            $typeCasier->update([
                'quantite_casiers_cave' => $validated['quantite_casiers_cave'],
                'quantite_bouteilles_seules_cave' => $validated['quantite_bouteilles_seules_cave'],
            ]);
            $msg = "Initialisation du stock de '{$typeCasier->nom}' effectuée avec succès.";
        }

        return redirect()->route('casiers.index')->with('success', $msg);
    }

    /**
     * Met à jour le stock physique de casiers et bouteilles en cave.
     */
    public function adjustStock(Request $request, TypeCasier $typeCasier): RedirectResponse
    {
        Gate::authorize('gérer-casiers');

        $validated = $request->validate([
            'quantite_casiers_cave' => ['required', 'integer', 'min:0'],
            'quantite_bouteilles_seules_cave' => ['required', 'integer', 'min:0'],
        ]);

        $typeCasier->update($validated);

        return redirect()->route('casiers.index')
            ->with('success', "Le stock de '{$typeCasier->nom}' a été mis à jour.");
    }

    /**
     * Enregistre un mouvement de casiers (Prêt au client OU Dépôt chez la cave).
     */
    public function storeMouvement(Request $request): RedirectResponse
    {
        Gate::authorize('gérer-casiers');

        $validated = $request->validate([
            'type_casier_id' => ['required', 'exists:type_casiers,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'nom_personne' => ['nullable', 'string', 'max:255'],
            'contact_personne' => ['nullable', 'string', 'max:255'],
            'type_mouvement' => ['required', 'in:PRET_CLIENT,DEPOT_CAVE'],
            'nombre_casiers' => ['required', 'integer', 'min:0'],
            'nombre_bouteilles_seules' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['nombre_casiers'] <= 0 && $validated['nombre_bouteilles_seules'] <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vous devez indiquer au moins 1 casier ou 1 bouteille.');
        }

        DB::transaction(function () use ($validated, $request) {
            $typeCasier = TypeCasier::findOrFail($validated['type_casier_id']);

            $clientId = $validated['client_id'] ?? null;

            ConsignationCasier::create([
                'type_casier_id' => $typeCasier->id,
                'client_id' => $clientId,
                'nom_personne' => $clientId ? null : ($validated['nom_personne'] ?? null),
                'contact_personne' => $clientId ? null : ($validated['contact_personne'] ?? null),
                'type_mouvement' => $validated['type_mouvement'],
                'nombre_casiers' => $validated['nombre_casiers'],
                'nombre_bouteilles_seules' => $validated['nombre_bouteilles_seules'],
                'statut' => 'EN_COURS',
                'date_mouvement' => now(),
                'user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Mise à jour du stock physique en cave selon le mouvement :
            // PRET_CLIENT : Sortie du stock de la cave
            // DEPOT_CAVE : Entrée en dépôt dans la cave
            if ($validated['type_mouvement'] === 'PRET_CLIENT') {
                $typeCasier->decrement('quantite_casiers_cave', min($typeCasier->quantite_casiers_cave, $validated['nombre_casiers']));
                $typeCasier->decrement('quantite_bouteilles_seules_cave', min($typeCasier->quantite_bouteilles_seules_cave, $validated['nombre_bouteilles_seules']));
            } else {
                $typeCasier->increment('quantite_casiers_cave', $validated['nombre_casiers']);
                $typeCasier->increment('quantite_bouteilles_seules_cave', $validated['nombre_bouteilles_seules']);
            }
        });

        $libelle = $validated['type_mouvement'] === 'PRET_CLIENT'
            ? 'Vente / Consignation d\'emballages (sortie effectif du stock cave)'
            : 'Dépôt / Restitution d\'emballages en cave';

        return redirect()->route('casiers.index')
            ->with('success', "{$libelle} enregistré(e) avec succès.");
    }

    /**
     * Solde une consignation (Restitution du prêt OU Retrait du dépôt).
     */
    public function solderMouvement(Request $request, ConsignationCasier $consignation): RedirectResponse
    {
        Gate::authorize('gérer-casiers');

        if ($consignation->statut === 'SOLDE') {
            return redirect()->route('casiers.index')
                ->with('error', 'Ce mouvement est déjà soldé.');
        }

        DB::transaction(function () use ($consignation) {
            $typeCasier = $consignation->typeCasier;

            // Mettre à jour le statut du mouvement
            $consignation->update(['statut' => 'SOLDE']);

            // Ajustement du stock physique lors du solde :
            // Si c'était un PRET_CLIENT -> Le client ramène les emballages (Ré-intégration en cave)
            // Si c'était un DEPOT_CAVE -> Le propriétaire reprend ses emballages (Sortie de la cave)
            if ($consignation->type_mouvement === 'PRET_CLIENT') {
                $typeCasier->increment('quantite_casiers_cave', $consignation->nombre_casiers);
                $typeCasier->increment('quantite_bouteilles_seules_cave', $consignation->nombre_bouteilles_seules);
            } else {
                $typeCasier->decrement('quantite_casiers_cave', min($typeCasier->quantite_casiers_cave, $consignation->nombre_casiers));
                $typeCasier->decrement('quantite_bouteilles_seules_cave', min($typeCasier->quantite_bouteilles_seules_cave, $consignation->nombre_bouteilles_seules));
            }
        });

        return redirect()->route('casiers.index')
            ->with('success', 'La consignation a été marquée comme soldée et le stock cave mis à jour.');
    }

    /**
     * Annule et supprime un mouvement erroné, en inversant l'impact sur le stock cave.
     */
    public function destroyMouvement(ConsignationCasier $consignation): RedirectResponse
    {
        Gate::authorize('gérer-casiers');

        DB::transaction(function () use ($consignation) {
            $typeCasier = $consignation->typeCasier;

            // Inverser l'impact du mouvement sur le stock cave (uniquement si non soldé)
            if ($consignation->statut === 'EN_COURS') {
                if ($consignation->type_mouvement === 'PRET_CLIENT') {
                    // Le prêt avait sorti du stock → on ré-intègre
                    $typeCasier->increment('quantite_casiers_cave', $consignation->nombre_casiers);
                    $typeCasier->increment('quantite_bouteilles_seules_cave', $consignation->nombre_bouteilles_seules);
                } else {
                    // Le dépôt avait ajouté au stock → on retire
                    $typeCasier->decrement('quantite_casiers_cave', min($typeCasier->quantite_casiers_cave, $consignation->nombre_casiers));
                    $typeCasier->decrement('quantite_bouteilles_seules_cave', min($typeCasier->quantite_bouteilles_seules_cave, $consignation->nombre_bouteilles_seules));
                }
            }

            $consignation->delete();
        });

        return redirect()->route('casiers.index')
            ->with('success', 'Le mouvement erroné a été annulé et le stock cave corrigé.');
    }
}
