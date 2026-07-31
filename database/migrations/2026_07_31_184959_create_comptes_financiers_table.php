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
        Schema::create('comptes_financiers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nom');
            $table->string('mode');
            $table->decimal('solde_initial', 12, 2)->default(0);
            $table->decimal('solde_courant', 12, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique('mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes_financiers');
    }
};
