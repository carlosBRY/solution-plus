<?php

namespace App\Services;

use App\Enums\MouvementType;
use App\Enums\StatutApprovisionnement;
use App\Models\Approvisionnement;
use App\Models\CompteFinancier;
use App\Models\Conditionnement;
use App\Models\DetailApprovisionnement;
use App\Models\Fournisseur;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovisionnementService
{
    public function __construct(
        protected CompteFinancierService $compteService
    ) {}

    /**
     * Crée un nouvel approvisionnement. Si le statut est RECEPTIONNE, met immédiatement à jour le stock.
     */
    public function creerApprovisionnement(User $user, array $data, array $items): Approvisionnement
    {
        return DB::transaction(function () use ($user, $data, $items) {
            $numero = 'APP-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
            $statut = $data['statut'] ?? StatutApprovisionnement::EN_ATTENTE->value;

            $compte = null;
            if (! empty($data['compte_financier_id'])) {
                $compte = CompteFinancier::find($data['compte_financier_id']);
            } elseif (! empty($data['mode'])) {
                $compte = $this->compteService->getCompteParMode($data['mode']);
            }

            $approvisionnement = Approvisionnement::create([
                'fournisseur_id' => $data['fournisseur_id'],
                'user_id' => $user->id,
                'numero' => $numero,
                'reference_facture' => $data['reference_facture'] ?? null,
                'date' => $data['date'] ?? now(),
                'statut' => $statut,
                'montant' => 0,
                'remise' => $data['remise'] ?? 0,
                'tva' => $data['tva'] ?? 0,
                'total' => 0,
                'mode' => $compte?->mode ?? ($data['mode'] ?? null),
                'compte_financier_id' => $compte?->id,
            ]);

            $montantSousTotal = 0;

            foreach ($items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $conditionnement = Conditionnement::where('id', $item['conditionnement_id'])
                    ->where('produit_id', $produit->id)
                    ->firstOrFail();

                $quantiteCond = (int) $item['quantite_conditionnement'];
                $coeff = (int) $conditionnement->quantite_unite_base;
                $quantiteUniteBase = $quantiteCond * $coeff;

                $prixAchat = isset($item['prix_achat'])
                    ? (float) $item['prix_achat']
                    : (float) ($conditionnement->prix_achat ?? $produit->prix_achat);

                $totalLigne = $quantiteCond * $prixAchat;
                $montantSousTotal += $totalLigne;

                // Mettre à jour le tarif d'achat de ce fournisseur pour ce conditionnement
                $fournisseur = Fournisseur::find($approvisionnement->fournisseur_id);
                if ($fournisseur) {
                    $fournisseur->tarifs()->syncWithoutDetaching([
                        $conditionnement->id => [
                            'produit_id' => $produit->id,
                            'prix_achat' => $prixAchat,
                        ],
                    ]);
                }

                DetailApprovisionnement::create([
                    'approvisionnement_id' => $approvisionnement->id,
                    'produit_id' => $produit->id,
                    'conditionnement_id' => $conditionnement->id,
                    'quantite' => $quantiteUniteBase,
                    'quantite_conditionnement' => $quantiteCond,
                    'coefficient_conversion' => $coeff,
                    'prix_achat' => $prixAchat,
                    'total' => $totalLigne,
                ]);

                // Si directement réceptionné lors de la création
                if ($statut === StatutApprovisionnement::RECEPTIONNE->value || $statut === StatutApprovisionnement::RECEPTIONNE) {
                    $this->appliquerEntreeStock($approvisionnement, $produit, $conditionnement, $quantiteUniteBase, $quantiteCond, $coeff, $user);
                }
            }

            $remise = (float) ($data['remise'] ?? 0);
            $tva = (float) ($data['tva'] ?? 0);
            $totalFinal = max(0, $montantSousTotal - $remise + $tva);

            $approvisionnement->update([
                'montant' => $montantSousTotal,
                'total' => $totalFinal,
            ]);

            // Débiter le compte financier si un compte est sélectionné et si totalFinal > 0
            if ($compte && $totalFinal > 0) {
                $refFact = $approvisionnement->reference_facture ? " (Facture: {$approvisionnement->reference_facture})" : '';
                $this->compteService->debiter(
                    $compte,
                    $user,
                    $totalFinal,
                    "Approvisionnement {$approvisionnement->numero}{$refFact}",
                    $approvisionnement->id,
                    'approvisionnement'
                );
            }

            return $approvisionnement->load('details.produit', 'details.conditionnement', 'fournisseur', 'compteFinancier');
        });
    }

    /**
     * Réceptionne un approvisionnement en attente et incrémente les stocks en unités de base.
     */
    public function receptionner(Approvisionnement $approvisionnement, User $user): Approvisionnement
    {
        if ($approvisionnement->statut === StatutApprovisionnement::RECEPTIONNE || $approvisionnement->statut === StatutApprovisionnement::RECEPTIONNE->value) {
            throw ValidationException::withMessages([
                'statut' => 'Cet approvisionnement a déjà été réceptionné.',
            ]);
        }

        if ($approvisionnement->statut === StatutApprovisionnement::ANNULE || $approvisionnement->statut === StatutApprovisionnement::ANNULE->value) {
            throw ValidationException::withMessages([
                'statut' => 'Impossible de réceptionner un approvisionnement annulé.',
            ]);
        }

        return DB::transaction(function () use ($approvisionnement, $user) {
            $approvisionnement->load('details.produit', 'details.conditionnement');

            foreach ($approvisionnement->details as $detail) {
                $this->appliquerEntreeStock(
                    $approvisionnement,
                    $detail->produit,
                    $detail->conditionnement,
                    $detail->quantite, // quantité déjà calculée en unité de base
                    $detail->quantite_conditionnement,
                    $detail->coefficient_conversion,
                    $user
                );
            }

            $approvisionnement->update([
                'statut' => StatutApprovisionnement::RECEPTIONNE,
            ]);

            return $approvisionnement;
        });
    }

    /**
     * Incrémente le stock d'un produit et crée le mouvement d'entrée.
     */
    protected function appliquerEntreeStock(
        Approvisionnement $approvisionnement,
        Produit $produit,
        ?Conditionnement $conditionnement,
        int $quantiteUniteBase,
        int $quantiteCond,
        int $coeff,
        User $user
    ): void {
        $stock = Stock::where('produit_id', $produit->id)->lockForUpdate()->first();

        if (! $stock) {
            $stock = Stock::create([
                'produit_id' => $produit->id,
                'quantite' => 0,
            ]);
        }

        $stockAvant = $stock->quantite;
        $stockApres = $stockAvant + $quantiteUniteBase;

        $stock->update(['quantite' => $stockApres]);

        MouvementStock::create([
            'produit_id' => $produit->id,
            'user_id' => $user->id,
            'conditionnement_id' => $conditionnement?->id,
            'type' => MouvementType::ENTREE,
            'quantite' => $quantiteUniteBase,
            'quantite_conditionnement' => $quantiteCond,
            'coefficient_conversion' => $coeff,
            'stock_avant' => $stockAvant,
            'stock_apres' => $stockApres,
            'motif' => "Approvisionnement {$approvisionnement->numero}",
            'reference' => $approvisionnement->numero,
        ]);
    }
}
