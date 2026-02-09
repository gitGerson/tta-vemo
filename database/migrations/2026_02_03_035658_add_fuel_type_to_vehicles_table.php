<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('fuel_type')->default('BBM')->after('status');
        });

        // Set all existing vehicles to BBM (as confirmed by user)
        DB::statement("UPDATE vehicles SET fuel_type = 'BBM'");
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('fuel_type');
        });
    }
};
