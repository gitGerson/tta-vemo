<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('entry_year', 5);
            $table->string('mileage')->nullable();
            $table->string('plat_new', 20)->unique();
            $table->string('plat_old', 20)->unique()->nullable();
            $table->enum('status', ['ACTIVE', 'PASSIVE']);
            $table->enum('mtc', ['NO', 'YES'])->default('NO');
            $table->text('desc')->nullable();
            $table->boolean('isprivate');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
