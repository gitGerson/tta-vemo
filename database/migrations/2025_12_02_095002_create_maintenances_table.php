<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('code_mt')->unique();
            $table->string('no_pol');
            $table->foreignId('get_request')->constrained('users');
            $table->foreignId('get_approval')->nullable()->constrained('users');
            $table->double('total_amount', 15, 2)->default(0);
            $table->date('date_in');
            $table->date('date_check')->nullable();
            $table->date('date_out')->nullable();
            $table->string('mileage')->nullable();
            $table->text('description')->nullable();
            $table->boolean('isServiceRoutine');
            $table->enum('status', ['NEW', 'PENDING', 'APPROVED', 'CANCEL'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('requested_by_name')->nullable();
            $table->string('approved_by_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
