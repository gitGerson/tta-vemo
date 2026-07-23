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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('isprivate');
        });

        Schema::table('vehicle_snapshots', function (Blueprint $table) {
            $table->dropColumn('isprivate');
        });
    }

    /**
     * Reverse the migrations. Previously stored ownership values cannot be restored.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('isprivate')->default(false);
        });

        Schema::table('vehicle_snapshots', function (Blueprint $table) {
            $table->boolean('isprivate')->nullable();
        });
    }
};
