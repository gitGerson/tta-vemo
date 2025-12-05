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
            $table->string('maintenance_name');
            $table->double('price', 15, 2);
            $table->string('qty', 50)->nullable();
            $table->text('material')->nullable();
            $table->foreignId('maintenance_id')->constrained('maintenances')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances_details');
    }
};
