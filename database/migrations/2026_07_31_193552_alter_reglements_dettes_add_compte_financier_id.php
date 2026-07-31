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
        Schema::table('reglements_dettes', function (Blueprint $table) {
            $table->foreignUlid('compte_financier_id')->nullable()->after('mode')
                ->constrained('comptes_financiers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglements_dettes', function (Blueprint $table) {
            $table->dropForeign(['compte_financier_id']);
            $table->dropColumn('compte_financier_id');
        });
    }
};
