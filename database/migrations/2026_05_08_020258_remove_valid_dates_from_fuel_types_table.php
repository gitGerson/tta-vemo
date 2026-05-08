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
        Schema::table('fuel_types', function (Blueprint $table) {
            $table->dropUnique('fuel_types_name_valid_start_unique');
            $table->dropColumn(['valid_start', 'valid_end']);
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_types', function (Blueprint $table) {
            $table->dropUnique('fuel_types_name_unique');
            $table->date('valid_start')->after('price');
            $table->date('valid_end')->nullable()->after('valid_start');
            $table->unique(['name', 'valid_start']);
        });
    }
};
