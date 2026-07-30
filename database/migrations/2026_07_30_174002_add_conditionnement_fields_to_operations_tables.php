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
        Schema::table('detail_approvisionnements', function (Blueprint $table) {
            $table->foreignUlid('conditionnement_id')->nullable()->after('produit_id')->constrained('conditionnements')->nullOnDelete();
            $table->unsignedInteger('quantite_conditionnement')->nullable()->after('quantite');
            $table->unsignedInteger('coefficient_conversion')->default(1)->after('quantite_conditionnement');
        });

        Schema::table('detail_ventes', function (Blueprint $table) {
            $table->foreignUlid('conditionnement_id')->nullable()->after('produit_id')->constrained('conditionnements')->nullOnDelete();
            $table->unsignedInteger('quantite_conditionnement')->nullable()->after('quantite');
            $table->unsignedInteger('coefficient_conversion')->default(1)->after('quantite_conditionnement');
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->foreignUlid('conditionnement_id')->nullable()->after('produit_id')->constrained('conditionnements')->nullOnDelete();
            $table->unsignedInteger('quantite_conditionnement')->nullable()->after('quantite');
            $table->unsignedInteger('coefficient_conversion')->default(1)->after('quantite_conditionnement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_approvisionnements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conditionnement_id');
            $table->dropColumn(['quantite_conditionnement', 'coefficient_conversion']);
        });

        Schema::table('detail_ventes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conditionnement_id');
            $table->dropColumn(['quantite_conditionnement', 'coefficient_conversion']);
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conditionnement_id');
            $table->dropColumn(['quantite_conditionnement', 'coefficient_conversion']);
        });
    }
};
