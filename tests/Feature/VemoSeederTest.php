<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\VemoDummyUserSeeder;
use Database\Seeders\VemoRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_can_be_seeded_repeatedly_without_changing_existing_permissions(): void
    {
        $this->seed(VemoRoleSeeder::class);
        $role = Role::where('name', 'Kasir')->firstOrFail();
        $permission = Permission::create([
            'name' => 'dashboard.access',
            'display_name' => 'Dashboard',
            'module' => 'dashboard',
        ]);
        $role->assignPermission($permission);

        $this->seed(VemoRoleSeeder::class);

        $this->assertDatabaseCount('roles', 4);
        $this->assertSame($role->id, Role::where('name', 'Kasir')->firstOrFail()->id);
        $this->assertTrue($role->fresh()->hasPermission('dashboard.access'));
        $this->assertDatabaseCount('role_permission', 1);
    }

    public function test_dummy_seeder_runs_standalone_and_is_repeatable_in_local(): void
    {
        $this->app->instance('env', 'local');
        $this->seed(VemoDummyUserSeeder::class);
        $originalIds = User::orderBy('email')->pluck('id', 'email')->all();
        $this->seed(VemoDummyUserSeeder::class);

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('roles', 4);
        $this->assertDatabaseCount('user_role', 4);
        $this->assertSame($originalIds, User::orderBy('email')->pluck('id', 'email')->all());

        foreach ([
            'kasir@test.local' => 'Kasir',
            'maintenance@test.local' => 'Admin Maintenance',
            'manager@test.local' => 'Manager',
            'supervisor@test.local' => 'Supervisor',
        ] as $email => $roleName) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertSame($email, $user->username);
            $this->assertTrue(Hash::check('P@ssw0rd1938', $user->password));
            $this->assertTrue($user->hasRole($roleName));
            $this->assertNull($user->guid);
            $this->assertNull($user->domain);
            $this->assertNotNull(DB::table('user_role')->where('user_id', $user->id)->value('assigned_at'));
        }
    }

    public function test_dummy_seeder_restores_deleted_users_without_duplicates(): void
    {
        $this->app->instance('env', 'local');
        $this->seed(VemoDummyUserSeeder::class);
        $user = User::where('email', 'kasir@test.local')->firstOrFail();
        $user->delete();

        $this->seed(VemoDummyUserSeeder::class);

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('user_role', 4);
        $this->assertSame($user->id, User::where('email', 'kasir@test.local')->firstOrFail()->id);
    }

    public function test_dummy_seeder_does_not_write_outside_local(): void
    {
        foreach (['production', 'staging', 'testing'] as $environment) {
            $this->app->instance('env', $environment);
            $this->artisan('db:seed', [
                '--class' => VemoDummyUserSeeder::class,
                '--force' => true,
            ])->assertSuccessful();

            $this->assertDatabaseCount('users', 0);
            $this->assertDatabaseCount('roles', 0);
            $this->assertDatabaseCount('user_role', 0);
        }
    }
}
