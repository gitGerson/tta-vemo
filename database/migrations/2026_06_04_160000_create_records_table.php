<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->string('driver_1');
            $table->string('driver_2')->nullable();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnUpdate();
            $table->string('no_pol');
            $table->string('destination');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate();
            $table->foreignId('fuel_type_id')->nullable()->constrained('fuel_types')->nullOnDelete()->cascadeOnUpdate();
            $table->string('start_km')->nullable();
            $table->string('finish_km');
            $table->string('totals');
            $table->string('bbm');
            $table->string('idr');
            $table->string('scale');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
