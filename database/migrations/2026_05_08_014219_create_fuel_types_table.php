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
        Schema::create('fuel_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->date('valid_start');
            $table->date('valid_end')->nullable();
            $table->timestamps();

            $table->unique(['name', 'valid_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_types');
    }
};
