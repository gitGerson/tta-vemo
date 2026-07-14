<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('entry_year', 5)->nullable();
            $table->string('mileage')->nullable();
            $table->string('plat_new', 20)->nullable();
            $table->string('status')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('mtc')->nullable();
            $table->boolean('isprivate')->nullable();
            $table->text('desc')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'created_at']);
        });

        // Baseline snapshot of every existing vehicle's current state.
        $companyNames = DB::table('companies')->pluck('name', 'id');

        DB::table('vehicles')->orderBy('id')->chunkById(500, function ($vehicles) use ($companyNames): void {
            $now = now();

            $rows = $vehicles->map(fn ($vehicle): array => [
                'vehicle_id' => $vehicle->id,
                'company_name' => $vehicle->company_id ? ($companyNames[$vehicle->company_id] ?? null) : null,
                'type' => $vehicle->type,
                'entry_year' => $vehicle->entry_year,
                'mileage' => $vehicle->mileage,
                'plat_new' => $vehicle->plat_new,
                'status' => $vehicle->status,
                'fuel_type' => $vehicle->fuel_type,
                'mtc' => $vehicle->mtc,
                'isprivate' => $vehicle->isprivate,
                'desc' => $vehicle->desc,
                'changed_by' => null,
                'created_at' => $vehicle->created_at ?? $now,
                'updated_at' => $now,
            ])->all();

            DB::table('vehicle_snapshots')->insert($rows);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_snapshots');
    }
};
