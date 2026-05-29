<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_driver_index_renders_for_authorized_user(): void
    {
        $this->actingAs($this->userWithDriverPermissions());

        $this->get(route('drivers.index'))
            ->assertOk()
            ->assertSee('Daftar Driver');
    }

    public function test_authorized_user_can_create_driver(): void
    {
        $this->actingAs($this->userWithDriverPermissions());

        $payload = Driver::factory()->make([
            'nik' => null,
            'no_identity' => '1234567890123456',
            'no_phone_1' => '081234567890',
            'no_phone_2' => '089876543210',
        ])->toArray();

        $this->post(route('drivers.store'), $payload)
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'name' => $payload['name'],
            'no_identity' => '1234567890123456',
        ]);
    }

    public function test_authorized_user_can_update_driver(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $driver = Driver::factory()->create(['nik' => null]);

        $payload = Driver::factory()->make([
            'nik' => null,
            'name' => 'Updated Driver',
            'no_identity' => $driver->no_identity,
            'no_phone_1' => $driver->no_phone_1,
            'no_phone_2' => $driver->no_phone_2,
        ])->toArray();

        $this->put(route('drivers.update', $driver), $payload)
            ->assertRedirect(route('drivers.index'));

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'name' => 'Updated Driver',
        ]);
    }

    public function test_authorized_user_can_soft_delete_and_restore_driver(): void
    {
        $this->actingAs($this->userWithDriverPermissions());
        $driver = Driver::factory()->create();

        $this->delete(route('drivers.destroy', $driver))
            ->assertRedirect(route('drivers.index'));

        $this->assertSoftDeleted('drivers', ['id' => $driver->id]);

        $this->post(route('drivers.restore', $driver))
            ->assertRedirect(route('drivers.trash'));

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'deleted_at' => null,
        ]);
    }

    private function userWithDriverPermissions(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'driver-manager',
            'display_name' => 'Driver Manager',
        ]);

        collect([
            'master.drivers.view',
            'master.drivers.create',
            'master.drivers.edit',
            'master.drivers.delete',
            'master.drivers.restore',
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
