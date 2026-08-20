<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('maintenance_name');
            $table->double('price', 15, 2);
            $table->string('qty', 50)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('material')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances_details');
    }
};
