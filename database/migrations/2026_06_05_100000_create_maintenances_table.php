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
            $table->double('total_amount', 15, 2)->default(0);
            $table->date('date_in');
            $table->date('date_check')->nullable();
            $table->date('date_out')->nullable();
            $table->string('mileage')->nullable();
            $table->text('description')->nullable();
            $table->boolean('isServiceRoutine')->default(true);
            $table->string('status')->default('DRAFT');
            $table->timestamp('viewed_at')->nullable();
            $table->foreignId('viewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('confirmation_notes')->nullable();
            $table->string('requested_by_name')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'date_in']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
