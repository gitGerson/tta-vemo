<?php

namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelTypeModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_fuel_type_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());

        $this->get(route('fuel-types.index'))
            ->assertOk()
            ->assertSee('Daftar Fuel Type')
            ->assertSee('Rp')
            ->assertSee('Bensin')
            ->assertSee('Solar');
    }

    public function test_authorized_user_can_create_fuel_type(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());

        $this->post(route('fuel-types.store'), [
            'name' => 'pertamax',
            'type' => 'BENSIN',
            'price' => 'Rp 13,500',
            'merk' => 'pertamina',
        ])->assertRedirect(route('fuel-types.index'));

        $this->assertDatabaseHas('fuel_types', [
            'name' => 'PERTAMAX',
            'type' => 'bensin',
            'price' => 13500,
            'merk' => 'Pertamina',
        ]);
    }

    public function test_authorized_user_can_update_fuel_type(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());
        $fuelType = FuelType::factory()->create([
            'name' => 'PERTALITE',
            'merk' => 'Pertamina',
        ]);

        $this->put(route('fuel-types.update', $fuelType), [
            'name' => 'shell super',
            'type' => 'BENSIN',
            'price' => '14,500.50',
            'merk' => 'shell',
        ])->assertRedirect(route('fuel-types.index'));

        $this->assertDatabaseHas('fuel_types', [
            'id' => $fuelType->id,
            'name' => 'SHELL SUPER',
            'type' => 'bensin',
            'price' => 14500.50,
            'merk' => 'Shell',
        ]);
    }

    public function test_fuel_type_requires_valid_merk(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());

        $this->post(route('fuel-types.store'), [
            'name' => 'Invalid Fuel',
            'type' => 'bensin',
            'price' => 10000,
            'merk' => 'Other',
        ])->assertSessionHasErrors('merk');
    }

    public function test_fuel_type_requires_valid_type(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());

        $this->post(route('fuel-types.store'), [
            'name' => 'Invalid Fuel',
            'type' => 'avtur',
            'price' => 10000,
            'merk' => 'Pertamina',
        ])->assertSessionHasErrors('type');
    }

    public function test_authorized_user_can_view_fuel_type_detail_fragment(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());
        $fuelType = FuelType::factory()->create([
            'name' => 'DETAIL FUEL',
            'type' => 'solar',
            'price' => 12000,
            'merk' => 'BP',
        ]);

        $this->get(route('fuel-types.show', $fuelType))
            ->assertOk()
            ->assertSee('DETAIL FUEL')
            ->assertSee('solar')
            ->assertSee('BP');
    }

    public function test_fuel_type_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());
        FuelType::factory()->create([
            'name' => 'DATA FUEL',
            'merk' => 'Vivo',
        ]);

        $this->get(route('fuel-types.data'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'DATA FUEL'])
            ->assertJsonFragment(['merk' => 'Vivo'])
            ->assertSee('btn-edit-fuel-type', false);
    }

    public function test_authorized_user_can_delete_fuel_type(): void
    {
        $this->actingAs($this->userWithFuelTypePermissions());
        $fuelType = FuelType::factory()->create();

        $this->delete(route('fuel-types.destroy', $fuelType))
            ->assertRedirect(route('fuel-types.index'));

        $this->assertSoftDeleted('fuel_types', ['id' => $fuelType->id]);
    }

    public function test_fuel_type_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithFuelTypePermissions();
        $this->actingAs($user);

        $menuItems = app(MenuService::class)->itemsForContext('desktop', $user);

        $this->assertContains('fuel-types-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_fuel_type_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.fuel_types.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.fuel_types.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.fuel_types.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.fuel_types.delete']);
    }

    private function userWithFuelTypePermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'fuel-type-manager',
            'display_name' => 'Fuel Type Manager',
        ]);

        collect([
            'master.fuel_types.view',
            'master.fuel_types.create',
            'master.fuel_types.edit',
            'master.fuel_types.delete',
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
