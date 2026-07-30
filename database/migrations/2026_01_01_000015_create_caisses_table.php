<?php

use App\Enums\StatutCaisse;
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
        Schema::create('caisses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('date_ouverture');
            $table->dateTime('date_fermeture')->nullable();
            $table->decimal('solde_initial', 12, 2)->default(0);
            $table->decimal('solde_final', 12, 2)->nullable();
            $table->decimal('ecart', 12, 2)->default(0);
            $table->string('statut')->default(StatutCaisse::OUVERTE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
