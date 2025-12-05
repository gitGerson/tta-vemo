<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_service', function (Blueprint $table) {
            $table->id();
            $table->string('no_pol');
            $table->boolean('read')->default(false);
            $table->string('from_km');
            $table->string('to_km');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_service');
    }
};
