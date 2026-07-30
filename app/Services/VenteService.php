<?php

namespace App\Services;

use App\Enums\ModePaiement;
use App\Enums\MouvementType;
use App\Enums\StatutVente;
use App\Models\Client;
use App\Models\Conditionnement;
use App\Models\DetailVente;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VenteService
{
    /**
     * Enregistre une vente, contrôle la disponibilité en stock, décrémente les stocks en unités de base et enregistre le règlement (avec gestion automatique du crédit).
     */
    public function creerVente(User $user, array $data, array $items): Vente
    {
        return DB::transaction(function () use ($user, $data, $items) {
            $numero = 'VTE-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
            $modePaiement = $data['mode_paiement'] ?? ModePaiement::ESPECES->value;

            $client = null;
            if (! empty($data['client_id'])) {
                $client = Client::where('id', $data['client_id'])->lockForUpdate()->first();
            }

            $vente = Vente::create([
                'client_id' => $client?->id,
                'user_id' => $user->id,
                'numero' => $numero,
                'date' => $data['date'] ?? now(),
                'statut' => StatutVente::EN_ATTENTE,
                'sous_total' => 0,
                'remise' => $data['remise_globale'] ?? 0,
                'tva' => $data['tva'] ?? 0,
                'total' => 0,
                'montant_paye' => 0,
                'monnaie' => 0,
            ]);

            $sousTotal = 0;

            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $conditionnement = Conditionnement::where('id', $item['conditionnement_id'])
                    ->where('produit_id', $produit->id)
                    ->firstOrFail();

                $quantiteCond = (int) $item['quantite_conditionnement'];
                $coeff = (int) $conditionnement->quantite_unite_base;
                $quantiteUniteBase = $quantiteCond * $coeff;

                // Verrouiller le stock pour contrôle strict
                $stock = Stock::where('produit_id', $produit->id)->lockForUpdate()->first();

                if (! $stock || $stock->quantite < $quantiteUniteBase) {
                    $dispo = $stock?->quantite ?? 0;
                    throw ValidationException::withMessages([
                        'stock' => "Stock insuffisant pour '{$produit->nom}'. Disponible: {$dispo} {$produit->unite_base}(s), Requis: {$quantiteUniteBase}.",
                    ]);
                }

                $prixVente = isset($item['prix'])
                    ? (float) $item['prix']
                    : (float) ($conditionnement->prix_vente ?? ($produit->prix_vente * $coeff));

                $remiseLigne = (float) ($item['remise'] ?? 0);
                $totalLigne = max(0, ($quantiteCond * $prixVente) - $remiseLigne);
                $sousTotal += $totalLigne;

                DetailVente::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $produit->id,
                    'conditionnement_id' => $conditionnement->id,
                    'quantite' => $quantiteUniteBase,
                    'quantite_conditionnement' => $quantiteCond,
                    'coefficient_conversion' => $coeff,
                    'prix' => $prixVente,
                    'remise' => $remiseLigne,
                    'total' => $totalLigne,
                ]);

                // Décrémenter le stock en unité de base
                $stockAvant = $stock->quantite;
                $stockApres = $stockAvant - $quantiteUniteBase;
                $stock->update(['quantite' => $stockApres]);

                // Mouvement de stock
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'user_id' => $user->id,
                    'conditionnement_id' => $conditionnement->id,
                    'type' => MouvementType::SORTIE,
                    'quantite' => -$quantiteUniteBase,
                    'quantite_conditionnement' => $quantiteCond,
                    'coefficient_conversion' => $coeff,
                    'stock_avant' => $stockAvant,
                    'stock_apres' => $stockApres,
                    'motif' => "Vente {$vente->numero}",
                    'reference' => $vente->numero,
                ]);
            }

            $remiseGlobale = (float) ($data['remise_globale'] ?? 0);
            $tva = (float) ($data['tva'] ?? 0);
            $totalVente = max(0, $sousTotal - $remiseGlobale + $tva);

            $montantPaye = $modePaiement === ModePaiement::CREDIT->value
                ? (float) ($data['montant_paye'] ?? 0)
                : (float) ($data['montant_paye'] ?? $totalVente);

            $monnaie = max(0, $montantPaye - $totalVente);
            $dette = max(0, $totalVente - $montantPaye);

            // Si vente à crédit ou paiement partiel
            if ($dette > 0) {
                if (! $client) {
                    throw ValidationException::withMessages([
                        'client_id' => 'Un client enregistré doit être sélectionné pour toute vente à crédit ou avec solde restant.',
                    ]);
                }

                if ($client->depassePlafondCredit($dette)) {
                    $plafondFormate = number_format($client->plafond_credit, 0, ',', ' ');
                    $soldeFormate = number_format($client->solde, 0, ',', ' ');
                    throw ValidationException::withMessages([
                        'credit' => "Cette vente (crédit de {$dette} FCFA) dépasse le plafond autorisé pour {$client->nom}. Solde actuel: {$soldeFormate} FCFA, Plafond: {$plafondFormate} FCFA.",
                    ]);
                }

                // Incrémenter le solde débiteur (crédit / dette) du client
                $client->increment('solde', $dette);
            }

            $statutVente = ($dette <= 0.01) ? StatutVente::PAYEE : StatutVente::EN_ATTENTE;

            $vente->update([
                'sous_total' => $sousTotal,
                'total' => $totalVente,
                'montant_paye' => $montantPaye,
                'monnaie' => $monnaie,
                'statut' => $statutVente,
            ]);

            // Enregistrer le règlement partiel ou total
            if ($montantPaye > 0) {
                Paiement::create([
                    'vente_id' => $vente->id,
                    'mode' => $modePaiement,
                    'montant' => min($montantPaye, $totalVente),
                    'reference' => $data['reference_paiement'] ?? null,
                    'date' => now(),
                ]);
            }

            return $vente->load('details.produit', 'details.conditionnement', 'client', 'paiements');
        });
    }
}
