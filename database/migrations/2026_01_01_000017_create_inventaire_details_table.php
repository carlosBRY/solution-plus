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
        Schema::create('inventaire_details', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->foreignUlid('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->integer('stock_theorique')->default(0);
            $table->integer('stock_physique')->default(0);
            $table->integer('ecart')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaire_details');
    }
};
