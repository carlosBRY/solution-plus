<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conditionnements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->string('nom'); // ex: Bouteille, Pack de 6, Caisse de 12
            $table->unsignedInteger('quantite_unite_base')->default(1); // coefficient de conversion
            $table->decimal('prix_achat', 12, 2)->nullable();
            $table->decimal('prix_vente', 12, 2)->nullable();
            $table->string('code_barre', 100)->nullable()->index();
            $table->boolean('is_achat')->default(true);
            $table->boolean('is_vente')->default(true);
            $table->boolean('is_par_defaut')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['produit_id', 'is_par_defaut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conditionnements');
    }
};
