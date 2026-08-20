<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\TypeMaintenances;
use App\Models\User;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypeMaintenanceModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_type_maintenance_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithTypeMaintenancePermissions());

        $this->get(route('type-maintenances.index'))
            ->assertOk()
            ->assertSee('Daftar Tipe Pemeliharaan');
    }

    public function test_authorized_user_can_create_type_maintenance(): void
    {
        $this->actingAs($this->userWithTypeMaintenancePermissions());

        $this->post(route('type-maintenances.store'), ['name' => 'engine oil', 'jenis' => 'barang'])
            ->assertRedirect(route('type-maintenances.index'));

        $this->assertDatabaseHas('type_maintenances', [
            'name' => 'ENGINE OIL',
            'jenis' => 'barang',
        ]);
    }

    public function test_authorized_user_can_update_type_maintenance(): void
    {
        $this->actingAs($this->userWithTypeMaintenancePermissions());
        $typeMaintenance = TypeMaintenances::factory()->create();

        $this->put(route('type-maintenances.update', $typeMaintenance), ['name' => 'brake service', 'jenis' => 'jasa'])
            ->assertRedirect(route('type-maintenances.index'));

        $this->assertDatabaseHas('type_maintenances', [
            'id' => $typeMaintenance->id,
            'name' => 'BRAKE SERVICE',
            'jenis' => 'jasa',
        ]);
    }

    public function test_authorized_user_can_view_type_maintenance_detail_fragment(): void
    {
        $this->actingAs($this->userWithTypeMaintenancePermissions());
        $typeMaintenance = TypeMaintenances::factory()->create(['name' => 'DETAIL SERVICE']);

        $this->get(route('type-maintenances.show', $typeMaintenance))
            ->assertOk()
            ->assertSee('DETAIL SERVICE');
    }

    public function test_type_maintenance_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithTypeMaintenancePermissions());
        TypeMaintenances::factory()->create(['name' => 'DATA SERVICE']);

        $this->get(route('type-maintenances.data'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'DATA SERVICE'])
            ->assertSee('btn-edit-type-maintenance', false);
    }

    public function test_type_maintenance_import_requires_a_file(): void
    {
        $this->actingAs($this->userWithTypeMaintenancePermissions());

        $this->post(route('type-maintenances.import'))
            ->assertSessionHasErrors('excelFile');
    }

    public function test_authorized_user_can_soft_delete_and_restore_type_maintenance(): void
    {
        $this->actingAs($this->userWithTypeMaintenancePermissions());
        $typeMaintenance = TypeMaintenances::factory()->create();

        $this->delete(route('type-maintenances.destroy', $typeMaintenance))
            ->assertRedirect(route('type-maintenances.index'));

        $this->assertSoftDeleted('type_maintenances', ['id' => $typeMaintenance->id]);

        $this->post(route('type-maintenances.restore', $typeMaintenance))
            ->assertRedirect(route('type-maintenances.trash'));

        $this->assertDatabaseHas('type_maintenances', [
            'id' => $typeMaintenance->id,
            'deleted_at' => null,
        ]);
    }

    public function test_type_maintenance_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithTypeMaintenancePermissions();
        $this->actingAs($user);

        $menuItems = app(MenuService::class)->itemsForContext('desktop', $user);

        $this->assertContains('type-maintenances-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_type_maintenance_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.type_maintenances.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.type_maintenances.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.type_maintenances.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.type_maintenances.delete']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.type_maintenances.restore']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.type_maintenances.force_delete']);
    }

    private function userWithTypeMaintenancePermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'type-maintenance-manager',
            'display_name' => 'Type Maintenance Manager',
        ]);

        collect([
            'master.type_maintenances.view',
            'master.type_maintenances.create',
            'master.type_maintenances.edit',
            'master.type_maintenances.delete',
            'master.type_maintenances.restore',
            'master.type_maintenances.force_delete',
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
