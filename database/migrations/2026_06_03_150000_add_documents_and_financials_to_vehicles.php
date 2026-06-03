<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = function (Blueprint $table): void {
            $table->string('machine_number', 100)->nullable()->after('plat_new');
            $table->string('vin_number', 100)->nullable()->after('machine_number');
            $table->json('bpkb_image')->nullable()->after('vin_number');
            $table->json('stnk_image')->nullable()->after('bpkb_image');
            $table->decimal('purchase_price', 15, 2)->nullable()->after('stnk_image');
            $table->decimal('selling_price', 15, 2)->nullable()->after('purchase_price');
            $table->date('purchase_date')->nullable()->after('selling_price');
            $table->date('sale_date')->nullable()->after('purchase_date');
        };

        Schema::table('vehicles', $columns);
        Schema::table('vehicle_snapshots', $columns);
    }

    public function down(): void
    {
        $drop = function (Blueprint $table): void {
            $table->dropColumn([
                'machine_number',
                'vin_number',
                'bpkb_image',
                'stnk_image',
                'purchase_price',
                'selling_price',
                'purchase_date',
                'sale_date',
            ]);
        };

        Schema::table('vehicles', $drop);
        Schema::table('vehicle_snapshots', $drop);
    }
};
