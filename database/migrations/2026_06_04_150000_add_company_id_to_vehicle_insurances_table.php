<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_insurances', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('vehicle_id')->constrained('companies')->nullOnDelete();
            $table->index(['company_id']);
        });

        // Backfill company_id from each insurance record's parent vehicle.
        // Correlated subquery for portability across MySQL and SQLite.
        DB::table('vehicle_insurances')
            ->whereNull('company_id')
            ->update([
                'company_id' => DB::raw('(select company_id from vehicles where vehicles.id = vehicle_insurances.vehicle_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('vehicle_insurances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
