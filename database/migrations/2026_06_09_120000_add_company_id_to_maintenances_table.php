<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('no_pol')->constrained('companies')->nullOnDelete();
            $table->index(['company_id']);
        });

        // Backfill company_id from the vehicle that owns each maintenance's plate.
        // Maintenances link to vehicles by plate (no_pol), not an FK. Correlated
        // subquery for portability across MySQL and SQLite.
        DB::table('maintenances')
            ->whereNull('company_id')
            ->update([
                'company_id' => DB::raw('(select company_id from vehicles where vehicles.plat_new = maintenances.no_pol limit 1)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
