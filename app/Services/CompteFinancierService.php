<?php

namespace App\Services;

use App\Models\CompteFinancier;
use App\Models\MouvementCompte;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompteFinancierService
{
    /**
     * Récupère le compte actif pour un mode de paiement donné.
     */
    public function getCompteParMode(string $mode): ?CompteFinancier
    {
        return CompteFinancier::where('mode', $mode)->where('actif', true)->first();
    }

    /**
     * Crédite un compte (entrée d'argent).
     */
    public function crediter(
        CompteFinancier $compte,
        User $user,
        float $montant,
        string $motif,
        ?string $referenceId = null,
        ?string $referenceType = null
    ): MouvementCompte {
        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant à créditer doit être supérieur à 0.',
            ]);
        }

        return DB::transaction(function () use ($compte, $user, $montant, $motif, $referenceId, $referenceType) {
            $compteLock = CompteFinancier::where('id', $compte->id)->lockForUpdate()->first();
            $soldeAvant = (float) $compteLock->solde_courant;
            $soldeApres = $soldeAvant + $montant;

            $compteLock->update(['solde_courant' => $soldeApres]);

            return MouvementCompte::create([
                'compte_financier_id' => $compteLock->id,
                'user_id' => $user->id,
                'type' => 'CREDIT',
                'montant' => $montant,
                'solde_avant' => $soldeAvant,
                'solde_apres' => $soldeApres,
                'motif' => $motif,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
            ]);
        });
    }

    /**
     * Débite un compte (sortie d'argent).
     */
    public function debiter(
        CompteFinancier $compte,
        User $user,
        float $montant,
        string $motif,
        ?string $referenceId = null,
        ?string $referenceType = null
    ): MouvementCompte {
        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant à débiter doit être supérieur à 0.',
            ]);
        }

        return DB::transaction(function () use ($compte, $user, $montant, $motif, $referenceId, $referenceType) {
            $compteLock = CompteFinancier::where('id', $compte->id)->lockForUpdate()->first();
            $soldeAvant = (float) $compteLock->solde_courant;
            $soldeApres = $soldeAvant - $montant;

            $compteLock->update(['solde_courant' => $soldeApres]);

            return MouvementCompte::create([
                'compte_financier_id' => $compteLock->id,
                'user_id' => $user->id,
                'type' => 'DEBIT',
                'montant' => $montant,
                'solde_avant' => $soldeAvant,
                'solde_apres' => $soldeApres,
                'motif' => $motif,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
            ]);
        });
    }

    /**
     * Transfère un montant d'un compte source vers un compte destination.
     * Crée 2 mouvements liés par le même reference_id.
     *
     * @return array{debit: MouvementCompte, credit: MouvementCompte}
     */
    public function transferer(
        CompteFinancier $source,
        CompteFinancier $destination,
        User $user,
        float $montant,
        string $motif
    ): array {
        if ($source->id === $destination->id) {
            throw ValidationException::withMessages([
                'destination' => 'Le compte source et le compte destination doivent être différents.',
            ]);
        }

        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant du transfert doit être supérieur à 0.',
            ]);
        }

        $transfertId = (string) Str::ulid();

        return DB::transaction(function () use ($source, $destination, $user, $montant, $motif, $transfertId) {
            // Débiter la source
            $sourceLock = CompteFinancier::where('id', $source->id)->lockForUpdate()->first();
            $soldeAvantSource = (float) $sourceLock->solde_courant;
            $soldeApresSource = $soldeAvantSource - $montant;

            $sourceLock->update(['solde_courant' => $soldeApresSource]);

            $debit = MouvementCompte::create([
                'compte_financier_id' => $sourceLock->id,
                'user_id' => $user->id,
                'type' => 'DEBIT',
                'montant' => $montant,
                'solde_avant' => $soldeAvantSource,
                'solde_apres' => $soldeApresSource,
                'motif' => "Transfert → {$destination->nom} : {$motif}",
                'reference_id' => $transfertId,
                'reference_type' => 'transfert',
            ]);

            // Créditer la destination
            $destLock = CompteFinancier::where('id', $destination->id)->lockForUpdate()->first();
            $soldeAvantDest = (float) $destLock->solde_courant;
            $soldeApresDest = $soldeAvantDest + $montant;

            $destLock->update(['solde_courant' => $soldeApresDest]);

            $credit = MouvementCompte::create([
                'compte_financier_id' => $destLock->id,
                'user_id' => $user->id,
                'type' => 'CREDIT',
                'montant' => $montant,
                'solde_avant' => $soldeAvantDest,
                'solde_apres' => $soldeApresDest,
                'motif' => "Transfert ← {$source->nom} : {$motif}",
                'reference_id' => $transfertId,
                'reference_type' => 'transfert',
            ]);

            return ['debit' => $debit, 'credit' => $credit];
        });
    }

    /**
     * Initialise ou corrige le solde d'un compte.
     */
    public function initialiserSolde(CompteFinancier $compte, User $user, float $solde): MouvementCompte
    {
        return DB::transaction(function () use ($compte, $user, $solde) {
            $compteLock = CompteFinancier::where('id', $compte->id)->lockForUpdate()->first();
            $soldeAvant = (float) $compteLock->solde_courant;

            $compteLock->update([
                'solde_initial' => $solde,
                'solde_courant' => $solde,
            ]);

            $type = $solde >= $soldeAvant ? 'CREDIT' : 'DEBIT';
            $montantMouvement = abs($solde - $soldeAvant);

            return MouvementCompte::create([
                'compte_financier_id' => $compteLock->id,
                'user_id' => $user->id,
                'type' => $type,
                'montant' => $montantMouvement,
                'solde_avant' => $soldeAvant,
                'solde_apres' => $solde,
                'motif' => 'Initialisation du solde',
                'reference_type' => 'initialisation',
            ]);
        });
    }

    /**
     * Retourne la somme des soldes de tous les comptes actifs (Caisse Principale).
     */
    public function getSoldeTotal(): float
    {
        return (float) CompteFinancier::actif()->sum('solde_courant');
    }
}
