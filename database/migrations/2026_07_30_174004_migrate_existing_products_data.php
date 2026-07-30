<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Crée un conditionnement "Bouteille" par défaut pour chaque produit existant
     * et lie les historiques de détails d'approvisionnements et de ventes.
     */
    public function up(): void
    {
        $produits = DB::table('produits')->get();

        foreach ($produits as $produit) {
            $conditionnementId = strtolower((string) Str::ulid());

            // Créer le conditionnement par défaut "Bouteille"
            DB::table('conditionnements')->insert([
                'id' => $conditionnementId,
                'produit_id' => $produit->id,
                'nom' => 'Bouteille',
                'quantite_unite_base' => 1,
                'prix_achat' => $produit->prix_achat,
                'prix_vente' => $produit->prix_vente,
                'code_barre' => $produit->code_barre,
                'is_achat' => true,
                'is_vente' => true,
                'is_par_defaut' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Lier les historiques d'approvisionnements existants
            DB::table('detail_approvisionnements')
                ->where('produit_id', $produit->id)
                ->whereNull('conditionnement_id')
                ->update([
                    'conditionnement_id' => $conditionnementId,
                    'quantite_conditionnement' => DB::raw('quantite'),
                    'coefficient_conversion' => 1,
                ]);

            // Lier les historiques de ventes existants
            DB::table('detail_ventes')
                ->where('produit_id', $produit->id)
                ->whereNull('conditionnement_id')
                ->update([
                    'conditionnement_id' => $conditionnementId,
                    'quantite_conditionnement' => DB::raw('quantite'),
                    'coefficient_conversion' => 1,
                ]);

            // Lier les mouvements de stock existants
            DB::table('mouvements_stock')
                ->where('produit_id', $produit->id)
                ->whereNull('conditionnement_id')
                ->update([
                    'conditionnement_id' => $conditionnementId,
                    'quantite_conditionnement' => DB::raw('quantite'),
                    'coefficient_conversion' => 1,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dissocier les historiques
        DB::table('detail_approvisionnements')->update([
            'conditionnement_id' => null,
            'quantite_conditionnement' => null,
            'coefficient_conversion' => 1,
        ]);

        DB::table('detail_ventes')->update([
            'conditionnement_id' => null,
            'quantite_conditionnement' => null,
            'coefficient_conversion' => 1,
        ]);

        DB::table('mouvements_stock')->update([
            'conditionnement_id' => null,
            'quantite_conditionnement' => null,
            'coefficient_conversion' => 1,
        ]);

        // Supprimer tous les conditionnements par défaut
        DB::table('conditionnements')->where('is_par_defaut', true)->delete();
    }
};
