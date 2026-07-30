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
        Schema::create('reglements_dettes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users');
            $table->string('numero', 50)->unique();
            $table->decimal('montant', 12, 2);
            $table->string('mode', 50)->default('ESPECES');
            $table->string('reference', 100)->nullable();
            $table->dateTime('date');
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglements_dettes');
    }
};
