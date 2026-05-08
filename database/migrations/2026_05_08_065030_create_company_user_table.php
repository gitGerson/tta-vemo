<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
        });

        DB::table('users')
            ->select(['id', 'company_id'])
            ->whereNotNull('company_id')
            ->orderBy('id')
            ->chunk(100, function ($users): void {
                $rows = [];
                $now = now();

                foreach ($users as $user) {
                    $rows[] = [
                        'company_id' => $user->company_id,
                        'user_id' => $user->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('company_user')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
