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
        Schema::table('ventes', function (Blueprint $table) {
            $table->string('client_comptant_nom')->nullable()->after('client_id');
            $table->string('client_comptant_prenom')->nullable()->after('client_comptant_nom');
            $table->string('client_comptant_contact')->nullable()->after('client_comptant_prenom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropColumn(['client_comptant_nom', 'client_comptant_prenom', 'client_comptant_contact']);
        });
    }
};
