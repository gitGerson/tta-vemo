<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve existing "previous plate" values in the history table before the
        // column is dropped: record each as a past plat_new change (old -> new).
        DB::table('vehicles')
            ->whereNotNull('plat_old')
            ->where('plat_old', '!=', '')
            ->orderBy('id')
            ->chunkById(500, function ($vehicles): void {
                $now = now();

                $rows = $vehicles->map(fn ($vehicle): array => [
                    'vehicle_id' => $vehicle->id,
                    'field' => 'plat_new',
                    'old_value' => $vehicle->plat_old,
                    'new_value' => $vehicle->plat_new,
                    'changed_by' => null,
                    'created_at' => $vehicle->created_at ?? $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('vehicle_histories')->insert($rows);
            });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropUnique(['plat_old']);
            $table->dropColumn('plat_old');
        });
    }

    public function down(): void
    {
        // Column is restored (data cannot be recovered; it lives in vehicle_histories).
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->string('plat_old', 20)->nullable()->unique()->after('plat_new');
        });
    }
};
