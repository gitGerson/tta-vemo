<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_insurances', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('insurer_name');
            $table->string('policy_number');
            $table->string('insurance_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('coverage_value', 15, 2)->nullable();
            $table->decimal('premium', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['vehicle_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_insurances');
    }
};
