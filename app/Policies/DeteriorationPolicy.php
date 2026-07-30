<?php

namespace App\Policies;

use App\Models\Deterioration;
use App\Models\User;

class DeteriorationPolicy
{
    /**
     * Voir la liste des détériorations.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('gérer-stocks') || $user->hasRole('Administrateur');
    }

    /**
     * Voir les détails d'une détérioration.
     */
    public function view(User $user, Deterioration $deterioration): bool
    {
        return $user->can('gérer-stocks') || $user->hasRole('Administrateur');
    }

    /**
     * Créer une détérioration.
     */
    public function create(User $user): bool
    {
        return $user->can('gérer-stocks') || $user->hasRole('Administrateur');
    }

    /**
     * Modifier une détérioration (interdit si VALIDEE).
     */
    public function update(User $user, Deterioration $deterioration): bool
    {
        if ($deterioration->isEstValidee()) {
            return false;
        }

        return $user->can('gérer-stocks') || $user->hasRole('Administrateur');
    }

    /**
     * Valider une détérioration.
     */
    public function validate(User $user, Deterioration $deterioration): bool
    {
        if ($deterioration->isEstValidee()) {
            return false;
        }

        return $user->can('gérer-stocks') || $user->hasRole('Administrateur');
    }

    /**
     * Supprimer une détérioration (interdit si VALIDEE).
     */
    public function delete(User $user, Deterioration $deterioration): bool
    {
        if ($deterioration->isEstValidee()) {
            return false;
        }

        return $user->can('gérer-stocks') || $user->hasRole('Administrateur');
    }
}
