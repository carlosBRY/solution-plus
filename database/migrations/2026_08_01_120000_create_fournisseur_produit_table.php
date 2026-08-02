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
        Schema::create('fournisseur_produit', function (Blueprint $table) {
            $table->foreignUlid('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->foreignUlid('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->decimal('prix_achat', 12, 2);
            $table->timestamps();

            $table->primary(['fournisseur_id', 'produit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseur_produit');
    }
};
