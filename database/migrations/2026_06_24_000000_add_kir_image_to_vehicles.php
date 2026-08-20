<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = function (Blueprint $table): void {
            $table->json('kir_image')->nullable()->after('stnk_image');
        };

        Schema::table('vehicles', $columns);
        Schema::table('vehicle_snapshots', $columns);
    }

    public function down(): void
    {
        $drop = function (Blueprint $table): void {
            $table->dropColumn('kir_image');
        };

        Schema::table('vehicles', $drop);
        Schema::table('vehicle_snapshots', $drop);
    }
};
