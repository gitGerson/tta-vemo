<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenances_id')->constrained('maintenances')->onUpdate('cascade')->onDelete('cascade');
            $table->string('image');
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note');
    }
};
