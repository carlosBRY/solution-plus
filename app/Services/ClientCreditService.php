<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ReglementDette;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientCreditService
{
    /**
     * Enregistre un règlement de dette / remboursement de crédit par un client.
     */
    public function reglerDette(Client $client, User $user, array $data): ReglementDette
    {
        $montant = (float) $data['montant'];

        if ($montant <= 0) {
            throw ValidationException::withMessages([
                'montant' => 'Le montant du règlement doit être supérieur à zéro.',
            ]);
        }

        if ($client->solde <= 0) {
            throw ValidationException::withMessages([
                'montant' => "Le client {$client->nom} n'a aucune dette en cours à régler.",
            ]);
        }

        return DB::transaction(function () use ($client, $user, $data, $montant) {
            // Verrouiller le client
            $clientLock = Client::where('id', $client->id)->lockForUpdate()->first();

            $numero = 'REG-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

            $reglement = ReglementDette::create([
                'client_id' => $clientLock->id,
                'user_id' => $user->id,
                'numero' => $numero,
                'montant' => $montant,
                'mode' => $data['mode'] ?? 'ESPECES',
                'reference' => $data['reference'] ?? null,
                'date' => $data['date'] ?? now(),
                'observation' => $data['observation'] ?? null,
            ]);

            // Décrémenter la dette / le solde du client (sans descendre en dessous de 0)
            $nouveauSolde = max(0, $clientLock->solde - $montant);
            $clientLock->update(['solde' => $nouveauSolde]);

            return $reglement->load('client', 'user');
        });
    }
}
