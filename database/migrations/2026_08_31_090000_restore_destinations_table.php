<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restore the standalone destinations table dropped in v3.0.0.
     *
     * Databases upgraded from v2.0.3 may still carry the original table (its
     * create migration was deleted from the repo without a drop migration), so
     * the existing table and its rows are kept and only reshaped.
     */
    public function up(): void
    {
        if (! Schema::hasTable('destinations')) {
            Schema::create('destinations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->text('desc')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });

            return;
        }

        // Legacy v2.0.3 table: column was `destination`, and the audit columns
        // were never added. Uniqueness stays validation-only because legacy rows
        // may already hold duplicates.
        if (Schema::hasColumn('destinations', 'destination') && ! Schema::hasColumn('destinations', 'name')) {
            Schema::table('destinations', function (Blueprint $table) {
                $table->renameColumn('destination', 'name');
            });
        }

        Schema::table('destinations', function (Blueprint $table) {
            if (! Schema::hasColumn('destinations', 'desc')) {
                $table->text('desc')->nullable();
            }

            if (! Schema::hasColumn('destinations', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('destinations', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('destinations', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('destinations', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
