<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_vehicle_records_a_baseline_snapshot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $vehicle = Vehicle::factory()->create(['plat_new' => 'B 1 AAA', 'status' => 'ACTIVE']);

        $this->assertDatabaseHas('vehicle_snapshots', [
            'vehicle_id' => $vehicle->id,
            'plat_new' => 'B 1 AAA',
            'status' => 'ACTIVE',
            'changed_by' => $user->id,
        ]);
        $this->assertSame(1, VehicleSnapshot::where('vehicle_id', $vehicle->id)->count());
    }

    public function test_updating_a_vehicle_records_a_new_full_snapshot(): void
    {
        $this->actingAs(User::factory()->create());

        $vehicle = Vehicle::factory()->create(['plat_new' => 'B 1 AAA', 'type' => 'Box']);

        $vehicle->update(['plat_new' => 'B 2 BBB', 'type' => 'Truck']);

        $this->assertSame(2, VehicleSnapshot::where('vehicle_id', $vehicle->id)->count());

        $latest = VehicleSnapshot::where('vehicle_id', $vehicle->id)->latest()->first();
        $this->assertSame('B 2 BBB', $latest->plat_new);
        $this->assertSame('Truck', $latest->type);
    }

    public function test_a_no_op_update_records_no_snapshot(): void
    {
        $this->actingAs(User::factory()->create());

        $vehicle = Vehicle::factory()->create(['type' => 'Box']);

        $vehicle->update(['type' => 'Box']);

        $this->assertSame(1, VehicleSnapshot::where('vehicle_id', $vehicle->id)->count());
    }

    public function test_show_page_renders_the_snapshot_table(): void
    {
        $this->actingAs($this->userWithVehicleView());

        $vehicle = Vehicle::factory()->create();

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('Detail Kendaraan')
            ->assertSee('Riwayat Perubahan');
    }

    private function userWithVehicleView(): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'vehicle-viewer', 'display_name' => 'Vehicle Viewer']);
        $permission = Permission::create([
            'name' => 'master.vehicles.view',
            'display_name' => 'master.vehicles.view',
            'module' => 'master',
        ]);
        $role->permissions()->attach($permission->id);
        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
