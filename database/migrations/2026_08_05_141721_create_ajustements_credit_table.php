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
        Schema::create('ajustements_credit', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users');
            $table->string('type', 20); // AJOUT | AJUSTEMENT
            $table->decimal('montant', 12, 2);
            $table->decimal('solde_avant', 12, 2);
            $table->decimal('solde_apres', 12, 2);
            $table->text('motif');
            $table->dateTime('date');
            $table->timestamps();

            $table->index(['client_id', 'date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustements_credit');
    }
};
