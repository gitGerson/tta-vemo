<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances_details', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('qty');
        });

        // Backfill existing records: calculate subtotal = price * qty
        DB::statement('UPDATE maintenances_details SET subtotal = price * COALESCE(qty, 1)');
    }

    public function down(): void
    {
        Schema::table('maintenances_details', function (Blueprint $table) {
            $table->dropColumn('subtotal');
        });
    }
};
