<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\MenuService;
use App\Services\PermissionSyncService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_destination_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());

        $this->get(route('destinations.index'))
            ->assertOk()
            ->assertSee('Daftar Tujuan');
    }

    public function test_destination_index_is_denied_without_permission(): void
    {
        $this->actingAs(User::factory()->create());

        // CheckPermission redirects back with an error for HTML requests and
        // answers 403 only for JSON.
        $this->get(route('destinations.index'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->getJson(route('destinations.data'))->assertForbidden();
    }

    public function test_authorized_user_can_create_destination(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());

        $this->post(route('destinations.store'), [
            'name' => '  Gudang Pusat  ',
            'desc' => 'Kawasan Industri Cikarang.',
        ])->assertRedirect(route('destinations.index'));

        $this->assertDatabaseHas('destinations', [
            'name' => 'Gudang Pusat',
            'desc' => 'Kawasan Industri Cikarang.',
        ]);
    }

    public function test_destination_name_must_be_unique(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());
        Destination::factory()->create(['name' => 'Gudang Pusat']);

        $this->post(route('destinations.store'), [
            'name' => 'Gudang Pusat',
            'desc' => null,
        ])->assertSessionHasErrors('name');

        $this->assertSame(1, Destination::query()->where('name', 'Gudang Pusat')->count());
    }

    public function test_authorized_user_can_update_destination(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());
        $destination = Destination::factory()->create();

        $this->put(route('destinations.update', $destination), [
            'name' => 'Pelabuhan Tanjung Priok',
            'desc' => 'Pengiriman kontainer.',
        ])->assertRedirect(route('destinations.index'));

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'name' => 'Pelabuhan Tanjung Priok',
            'desc' => 'Pengiriman kontainer.',
        ]);
    }

    public function test_authorized_user_can_view_destination_detail_fragment(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());
        $destination = Destination::factory()->create([
            'name' => 'Detail Destination',
            'desc' => 'Detail keterangan.',
        ]);

        $this->get(route('destinations.show', $destination))
            ->assertOk()
            ->assertSee('Detail Destination')
            ->assertSee('Detail keterangan.');
    }

    public function test_destination_data_endpoint_returns_rows_with_actions(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());
        Destination::factory()->create(['name' => 'Data Destination']);

        $this->get(route('destinations.data'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Data Destination'])
            ->assertSee('btn-edit-destination', false);
    }

    public function test_authorized_user_can_soft_delete_and_restore_destination(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());
        $destination = Destination::factory()->create();

        $this->delete(route('destinations.destroy', $destination))
            ->assertRedirect(route('destinations.index'));

        $this->assertSoftDeleted('destinations', ['id' => $destination->id]);

        $this->get(route('destinations.trash'))->assertOk()->assertSee('Sampah Tujuan');
        $this->get(route('destinations.trash.data'))
            ->assertOk()
            ->assertJsonFragment(['name' => $destination->name]);

        $this->post(route('destinations.restore', $destination))
            ->assertRedirect(route('destinations.trash'));

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_user_can_force_delete_destination(): void
    {
        $this->actingAs($this->userWithDestinationPermissions());
        $destination = Destination::factory()->create();
        $destination->delete();

        $this->delete(route('destinations.force-delete', $destination))
            ->assertRedirect(route('destinations.trash'));

        $this->assertDatabaseMissing('destinations', ['id' => $destination->id]);
    }

    public function test_destination_is_registered_in_menu_for_permitted_user(): void
    {
        $user = $this->userWithDestinationPermissions();
        $this->actingAs($user);

        $menuItems = app(MenuService::class)->itemsForContext('desktop', $user);

        $this->assertContains('destinations-index', collect($menuItems)->pluck('key'));
    }

    public function test_permission_sync_registers_destination_permissions(): void
    {
        app(PermissionSyncService::class)->sync();

        $this->assertDatabaseHas('permissions', ['name' => 'master.destinations.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.destinations.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.destinations.edit']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.destinations.delete']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.destinations.restore']);
        $this->assertDatabaseHas('permissions', ['name' => 'master.destinations.force_delete']);
    }

    private function userWithDestinationPermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'destination-manager',
            'display_name' => 'Destination Manager',
        ]);

        collect([
            'master.destinations.view',
            'master.destinations.create',
            'master.destinations.edit',
            'master.destinations.delete',
            'master.destinations.restore',
            'master.destinations.force_delete',
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
