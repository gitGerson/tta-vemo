<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VehicleSnapshotMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', fn (Blueprint $table) => $table->id());
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        (require database_path('migrations/2025_12_02_063254_create_vehicles_table.php'))->up();
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');

        parent::tearDown();
    }

    public function test_backfilling_legacy_vehicles_without_a_company_column(): void
    {
        Schema::table('vehicles', fn (Blueprint $table) => $table->dropConstrainedForeignId('company_id'));
        $vehicleId = $this->createVehicle();

        $this->migration()->up();

        $this->assertDatabaseHas('vehicle_snapshots', [
            'vehicle_id' => $vehicleId,
            'company_name' => null,
            'plat_new' => 'B 1 AAA',
            'status' => 'ACTIVE',
            'changed_by' => null,
        ]);
    }

    public function test_backfilling_company_names_and_unassigned_vehicles(): void
    {
        $companyId = DB::table('companies')->insertGetId(['name' => 'Test Company']);
        $assignedId = $this->createVehicle(['company_id' => $companyId]);
        $unassignedId = $this->createVehicle(['plat_new' => 'B 2 BBB']);

        $this->migration()->up();

        $this->assertDatabaseHas('vehicle_snapshots', ['vehicle_id' => $assignedId, 'company_name' => 'Test Company']);
        $this->assertDatabaseHas('vehicle_snapshots', ['vehicle_id' => $unassignedId, 'company_name' => null]);
    }

    public function test_retrying_a_partial_backfill_preserves_existing_snapshots(): void
    {
        $firstId = $this->createVehicle();
        $migration = $this->migration();
        $migration->up();
        DB::table('vehicle_snapshots')->where('vehicle_id', $firstId)->update(['desc' => 'Existing snapshot']);
        $secondId = $this->createVehicle(['plat_new' => 'B 2 BBB']);

        $migration->up();
        $migration->up();

        $this->assertDatabaseCount('vehicle_snapshots', 2);
        $this->assertDatabaseHas('vehicle_snapshots', ['vehicle_id' => $firstId, 'desc' => 'Existing snapshot']);
        $this->assertDatabaseHas('vehicle_snapshots', ['vehicle_id' => $secondId]);
    }

    private function createVehicle(array $attributes = []): int
    {
        return DB::table('vehicles')->insertGetId(array_merge([
            'type' => 'Truck',
            'entry_year' => '2024',
            'plat_new' => 'B 1 AAA',
            'status' => 'ACTIVE',
            'isprivate' => false,
            'created_at' => '2024-01-01 00:00:00',
        ], $attributes));
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_06_03_140000_create_vehicle_snapshots_table.php');
    }
}
