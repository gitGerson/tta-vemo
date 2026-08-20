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
        Schema::create('record_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('records')->cascadeOnDelete();
            $table->string('driver_1');
            $table->string('driver_2')->nullable();
            $table->string('vehicle_plat');
            $table->string('destination');
            $table->string('company_name')->nullable();
            $table->string('fuel_type_name')->nullable();
            $table->string('start_km')->nullable();
            $table->string('finish_km');
            $table->string('totals');
            $table->string('bbm');
            $table->string('idr');
            $table->string('scale');
            $table->date('date');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['record_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_snapshots');
    }
};
