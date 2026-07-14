<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = function (Blueprint $table): void {
            $table->date('bpkb_due_date')->nullable()->after('bpkb_image');
            $table->date('stnk_due_date')->nullable()->after('stnk_image');
            $table->date('kir_due_date')->nullable()->after('kir_image');
        };

        Schema::table('vehicles', $columns);
        Schema::table('vehicle_snapshots', $columns);
    }

    public function down(): void
    {
        $drop = function (Blueprint $table): void {
            $table->dropColumn(['bpkb_due_date', 'stnk_due_date', 'kir_due_date']);
        };

        Schema::table('vehicles', $drop);
        Schema::table('vehicle_snapshots', $drop);
    }
};
