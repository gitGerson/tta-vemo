<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_logs', function (Blueprint $table): void {
            $table->string('auditable_type')->nullable()->after('target_user_id');
            $table->unsignedBigInteger('auditable_id')->nullable()->after('auditable_type');
            $table->index(['auditable_type', 'auditable_id', 'created_at'], 'user_logs_auditable_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('user_logs', function (Blueprint $table): void {
            $table->dropIndex('user_logs_auditable_created_at_index');
            $table->dropColumn(['auditable_type', 'auditable_id']);
        });
    }
};
