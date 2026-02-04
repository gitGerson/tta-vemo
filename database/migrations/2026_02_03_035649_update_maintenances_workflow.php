<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns
        Schema::table('maintenances', function (Blueprint $table) {
            $table->timestamp('viewed_at')->nullable()->after('status');
            $table->foreignId('viewed_by')->nullable()->after('viewed_at')->constrained('users')->nullOnDelete();
            $table->boolean('is_confirmed')->default(false)->after('viewed_by');
            $table->timestamp('confirmed_at')->nullable()->after('is_confirmed');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
        });

        // Step 2: Change status enum - MySQL requires recreating the column
        // First, add temporary column
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('status_new')->nullable()->after('status');
        });

        // Step 3: Map old status values to new values
        DB::statement("UPDATE maintenances SET status_new = CASE 
            WHEN status = 'NEW' THEN 'IN_PROGRESS'
            WHEN status = 'PENDING' THEN 'DRAFT'
            WHEN status = 'APPROVED' THEN 'COMPLETED'
            WHEN status = 'CANCEL' THEN 'CANCELLED'
            ELSE 'IN_PROGRESS'
        END");

        // Step 4: Drop old status column and rename new one
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // Step 5: Backfill confirmation data for previously approved records
        DB::statement("UPDATE maintenances SET 
            viewed_at = updated_at,
            viewed_by = get_approval,
            is_confirmed = 1,
            confirmed_at = updated_at,
            confirmed_by = get_approval
        WHERE status = 'COMPLETED' AND get_approval IS NOT NULL");
    }

    public function down(): void
    {
        // Reverse the status mapping
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('status_old')->nullable()->after('status');
        });

        DB::statement("UPDATE maintenances SET status_old = CASE 
            WHEN status = 'IN_PROGRESS' THEN 'NEW'
            WHEN status = 'DRAFT' THEN 'PENDING'
            WHEN status = 'COMPLETED' THEN 'APPROVED'
            WHEN status = 'CANCELLED' THEN 'CANCEL'
            ELSE 'NEW'
        END");

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });

        // Drop confirmation columns
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn('confirmed_by');
            $table->dropColumn('confirmed_at');
            $table->dropColumn('is_confirmed');
            $table->dropForeign(['viewed_by']);
            $table->dropColumn('viewed_by');
            $table->dropColumn('viewed_at');
        });
    }
};
