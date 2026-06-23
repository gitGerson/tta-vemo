<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\VehicleType;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTypeModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_vehicle_type_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithVehicleTypePermissions());

        $this->get(route('vehicle-types.index'))
            ->assertOk()
            ->assertSee('Daftar Tipe Kendaraan');
    }

    public function test_authorized_user_can_create_vehicle_type(): void
    {
        $this->actingAs($this->userWithVehicleTypePermissions());

        $this->post(route('vehicle-types.store'), [
            'code' => 'box',
            'name' => 'box truck',
            'descriptions' => 'General box vehicle.',
        ])->assertRedirect(route('vehicle-types.index'));

        $this->assertDatabaseHas('vehicle_types', [
            'code' => 'BOX',
            'name' => 'BOX TRUCK',
            'descriptions' => 'General box vehicle.',
        ]);
    }

    public function test_authorized_user_can_update_vehicle_type(): void
    {
        $this->actingAs($this->userWithVehicleTypePermissions());
        $vehicleType = VehicleType::factory()->create();

        $this->put(route('vehicle-types.update', $vehicleType), [
            'code' => $vehicleType->code,
            'name' => 'pickup',
            'descriptions' => 'Updated description.',
        ])->assertRedirect(route('vehicle-types.index'));

        $this->assertDatabaseHas('vehicle_types', [
            'id' => $vehicleType->id,
            'name' => 'PICKUP',
            'descriptions' => 'Updated description.',
        ]);
    }

    public function test_vehicle_type_requires_unique_code_and_name(): void
    {
        $this->actingAs($this->userWithVehicleTypePermissions());
        VehicleType::factory()->create([
            'code' => 'TRK',
            'name' => 'TRUCK',
        ]);

        $this->post(route('vehicle-types.store'), [
            'code' => 'TRK',
            'name' => 'TRUCK',
        ])->assertSessionHasErrors(['code', 'name']);
    }

    public function test_authorized_user_can_view_vehicle_type_detail_fragment(): void
    {
        $this->actingAs($this->userWithVehicleTypePermissions());
        $vehicleType = VehicleType::factory()->create([
            'code' => 'BOX',
            'name' => 'BOX TRUCK',
            'descriptions' => 'Detail description.',
        ]);

        $this->get(route('vehicle-types.show', $vehicleType))
            ->assertOk()
            ->assertSee('BOX')
            ->assertSee('BOX TRUCK')
            ->assertSee('Detail description.');
    }

    public function test_vehicle_type_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithVehicleTypePermissions());
        VehicleType::factory()->create([
            'code' => 'DT',
            'name' => 'DATA TYPE',
        ]);

        $this->get(route('vehicle-types.data'))
            ->assertOk()
            ->assertJsonFragment(['code' => 'DT'])
            ->assertJsonFragment(['name' => 'DATA TYPE'])
            ->assertSee('btn-edit-vehicle-type', false);
    }

    public function test_authorized_user_can_delete_vehicle_type(): void
    {
        $this->actingAs($this->userWithVehicleTypePermissions());
        $vehicleType = VehicleType::factory()->create();

        $this->delete(route('vehicle-types.destroy', $vehicleType))
            ->assertRedirect(route('vehicle-types.index'));

        $this->assertSoftDeleted('vehicle_types', ['id' => $vehicleType->id]);
    }

    public function test_vehicle_type_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithVehicleTypePermissions();
        $this->actingAs($user);

        // Vehicle Type lives in the vehicle index "Referensi" dropdown, not the
        // desktop sidebar; it remains reachable via the search palette.
        $menuItems = app(MenuService::class)->itemsForContext('search', $user);

        $this->assertContains('vehicle-types-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_vehicle_type_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_types.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_types.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_types.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.vehicle_types.delete']);
    }

    private function userWithVehicleTypePermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'vehicle-type-manager',
            'display_name' => 'Vehicle Type Manager',
        ]);

        collect([
            'master.vehicle_types.view',
            'master.vehicle_types.create',
            'master.vehicle_types.edit',
            'master.vehicle_types.delete',
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
