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
        Schema::table('approvisionnements', function (Blueprint $table) {
            $table->decimal('bic', 12, 2)->default(0)->after('tva');
            $table->decimal('fsp', 12, 2)->default(0)->after('bic');
            $table->decimal('autres_taxes', 12, 2)->default(0)->after('fsp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvisionnements', function (Blueprint $table) {
            $table->dropColumn(['bic', 'fsp', 'autres_taxes']);
        });
    }
};
