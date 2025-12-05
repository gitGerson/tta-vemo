<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('nik')->nullable();
            $table->string('name');
            $table->string('address');
            $table->string('no_identity', 16)->unique();
            $table->string('residence');
            $table->date('date_birth');
            $table->string('no_phone_1', 15);
            $table->string('no_phone_2', 15);
            $table->date('entry_date');
            $table->date('out_date')->nullable();
            $table->enum('status', ['ACTIVE', 'PASSIVE']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
