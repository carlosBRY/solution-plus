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
        Schema::create('mouvements_compte', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('compte_financier_id')->constrained('comptes_financiers')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // CREDIT | DEBIT
            $table->decimal('montant', 12, 2);
            $table->decimal('solde_avant', 12, 2);
            $table->decimal('solde_apres', 12, 2);
            $table->string('motif');
            $table->string('reference_id')->nullable();
            $table->string('reference_type')->nullable(); // vente, depense, transfert, initialisation
            $table->timestamps();

            $table->index(['compte_financier_id', 'created_at']);
            $table->index('reference_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvements_compte');
    }
};
