<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_vehicle_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());

        $this->get(route('vehicles.index'))
            ->assertOk()
            ->assertSee('Daftar Kendaraan');
    }

    public function test_authorized_user_can_create_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $company = Company::factory()->create();

        $payload = Vehicle::factory()->make([
            'company_id' => $company->id,
            'type' => 'Box',
            'entry_year' => '2026',
            'plat_new' => 'H 1234 TT',
            'plat_old' => null,
            'fuel_type' => 'BBM',
            'isprivate' => false,
        ])->toArray();

        $this->post(route('vehicles.store'), $payload)
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'company_id' => $company->id,
            'type' => 'Box',
            'plat_new' => 'H 1234 TT',
            'fuel_type' => 'BBM',
            'isprivate' => false,
        ]);
    }

    public function test_authorized_user_can_update_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create();
        $company = Company::factory()->create();

        $payload = Vehicle::factory()->make([
            'company_id' => $company->id,
            'type' => 'Updated Truck',
            'plat_new' => $vehicle->plat_new,
            'plat_old' => $vehicle->plat_old,
            'fuel_type' => 'Solar',
        ])->toArray();

        $this->put(route('vehicles.update', $vehicle), $payload)
            ->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'company_id' => $company->id,
            'type' => 'Updated Truck',
            'fuel_type' => 'Solar',
        ]);
    }

    public function test_authorized_user_can_view_vehicle_detail_fragment(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create([
            'type' => 'Detail Vehicle',
            'plat_new' => 'H 9999 QA',
        ]);

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('Detail Vehicle')
            ->assertSee('H 9999 QA');
    }

    public function test_vehicle_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        Vehicle::factory()->create([
            'type' => 'Data Vehicle',
            'plat_new' => 'H 1235 DT',
        ]);

        $this->get(route('vehicles.data'))
            ->assertOk()
            ->assertJsonFragment(['plat_new' => 'H 1235 DT'])
            ->assertSee('btn-edit-vehicle', false);
    }

    public function test_authorized_user_can_soft_delete_and_restore_vehicle(): void
    {
        $this->actingAs($this->userWithVehiclePermissions());
        $vehicle = Vehicle::factory()->create();

        $this->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'));

        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);

        $this->post(route('vehicles.restore', $vehicle))
            ->assertRedirect(route('vehicles.trash'));

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'deleted_at' => null,
        ]);
    }

    public function test_vehicle_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithVehiclePermissions();
        $this->actingAs($user);

        $menuItems = app(MenuService::class)->itemsForContext('desktop', $user);

        $this->assertContains('vehicles-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_vehicle_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.delete']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.restore']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicles.force_delete']);
    }

    private function userWithVehiclePermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'vehicle-manager',
            'display_name' => 'Vehicle Manager',
        ]);

        collect([
            'master.vehicles.view',
            'master.vehicles.create',
            'master.vehicles.edit',
            'master.vehicles.delete',
            'master.vehicles.restore',
            'master.vehicles.force_delete',
        ])->each(function (string $name) use ($role): void {
            $permission = Permission::create([
                'name' => $name,
                'display_name' => $name,
                'module' => 'master',
            ]);

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
