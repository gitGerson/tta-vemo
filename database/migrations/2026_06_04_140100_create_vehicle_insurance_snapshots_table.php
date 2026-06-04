<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_insurance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_insurance_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_plat', 20)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('insurer_name')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('insurance_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('coverage_value', 15, 2)->nullable();
            $table->decimal('premium', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_insurance_id', 'created_at'], 'vis_insurance_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_insurance_snapshots');
    }
};
