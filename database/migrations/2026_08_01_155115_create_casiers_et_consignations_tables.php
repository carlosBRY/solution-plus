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
        Schema::create('type_casiers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nom'); // ex: "Casier 12 places", "Casier 24 places", "Casier 20 places"
            $table->integer('capacite_bouteilles')->default(12);
            $table->integer('quantite_casiers_cave')->default(0);
            $table->integer('quantite_bouteilles_seules_cave')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('consignations_casiers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('type_casier_id')->constrained('type_casiers')->cascadeOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('nom_personne')->nullable();
            $table->string('contact_personne')->nullable();
            $table->string('type_mouvement'); // PRET_CLIENT (prêt au client), RETOUR_PRET (restitution client), DEPOT_CAVE (déposé à la cave), RETRAIT_DEPOT (retrait du dépôt)
            $table->integer('nombre_casiers')->default(0);
            $table->integer('nombre_bouteilles_seules')->default(0);
            $table->string('statut')->default('EN_COURS'); // EN_COURS, SOLDE
            $table->dateTime('date_mouvement');
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignations_casiers');
        Schema::dropIfExists('type_casiers');
    }
};
