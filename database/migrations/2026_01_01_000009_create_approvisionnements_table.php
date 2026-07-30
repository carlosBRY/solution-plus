<?php

use App\Enums\StatutApprovisionnement;
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
        Schema::create('approvisionnements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->dateTime('date');
            $table->decimal('montant', 12, 2)->default(0);
            $table->decimal('remise', 12, 2)->default(0);
            $table->decimal('tva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('statut')->default(StatutApprovisionnement::EN_ATTENTE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvisionnements');
    }
};
