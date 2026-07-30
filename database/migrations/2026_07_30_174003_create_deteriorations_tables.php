<?php

use App\Enums\StatutDeterioration;
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
        Schema::create('deteriorations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users');
            $table->string('numero', 50)->unique();
            $table->dateTime('date');
            $table->string('statut', 30)->default(StatutDeterioration::BROUILLON->value);
            $table->foreignUlid('valide_par')->nullable()->constrained('users');
            $table->dateTime('date_validation')->nullable();
            $table->decimal('total_perte', 12, 2)->default(0);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index('statut');
        });

        Schema::create('deterioration_details', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('deterioration_id')->constrained('deteriorations')->cascadeOnDelete();
            $table->foreignUlid('produit_id')->constrained('produits');
            $table->foreignUlid('conditionnement_id')->constrained('conditionnements');
            $table->unsignedInteger('quantite_conditionnement');
            $table->unsignedInteger('coefficient_conversion');
            $table->unsignedInteger('quantite_unite_base');
            $table->decimal('cout_unitaire', 12, 2);
            $table->decimal('valeur_perte', 12, 2);
            $table->string('cause', 50);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['deterioration_id', 'produit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deterioration_details');
        Schema::dropIfExists('deteriorations');
    }
};
