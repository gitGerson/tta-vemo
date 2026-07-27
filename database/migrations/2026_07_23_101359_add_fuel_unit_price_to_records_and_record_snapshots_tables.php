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
        Schema::table('records', function (Blueprint $table) {
            $table->decimal('fuel_unit_price', 15, 2)->nullable()->after('bbm');
        });

        Schema::table('record_snapshots', function (Blueprint $table) {
            $table->decimal('fuel_unit_price', 15, 2)->nullable()->after('bbm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_snapshots', function (Blueprint $table) {
            $table->dropColumn('fuel_unit_price');
        });

        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn('fuel_unit_price');
        });
    }
};
