<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\VemoDummyUserSeeder;
use Database\Seeders\VemoRoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class VemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_is_repeatable_and_removes_unintended_permissions_from_target_roles(): void
    {
        $this->seed(VemoRoleSeeder::class);
        $role = Role::where('name', 'Kasir')->firstOrFail();
        $permission = Permission::create([
            'name' => 'management.users.create',
            'display_name' => 'Create users',
            'module' => 'management',
        ]);
        $role->assignPermission($permission);
        $expectedPermissions = $role->permissions()->where('permissions.id', '!=', $permission->id)->pluck('permissions.id')->sort()->values()->all();
        $permissionCount = Permission::count();
        $pivotCount = DB::table('role_permission')->count();

        $this->seed(VemoRoleSeeder::class);

        $this->assertDatabaseCount('roles', 4);
        $this->assertSame($role->id, Role::where('name', 'Kasir')->firstOrFail()->id);
        $this->assertTrue($role->fresh()->hasPermission('dashboard.access'));
        $this->assertFalse($role->fresh()->hasPermission('management.users.create'));
        $this->assertSame($expectedPermissions, $role->permissions()->pluck('permissions.id')->sort()->values()->all());
        $this->assertSame($permissionCount, Permission::count());
        $this->assertDatabaseCount('role_permission', $pivotCount - 1);
    }

    public function test_sync_preserves_existing_templates_custom_roles_permissions_and_user_assignments(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $permission = Permission::create([
            'name' => 'custom.audit.view',
            'display_name' => 'Custom audit',
            'module' => 'custom',
        ]);
        $customRole = Role::create(['name' => 'custom_role', 'display_name' => 'Custom role']);
        $customRole->assignPermission($permission);
        $user = User::factory()->create();
        $user->assignRole($customRole);
        $kasir = Role::create(['name' => 'Kasir', 'display_name' => 'Existing cashier label', 'description' => 'Keep this description']);
        $user->assignRole($kasir);

        $existingRoleIds = Role::where('name', '!=', 'Kasir')->pluck('id');
        $rolesBefore = DB::table('roles')->whereIn('id', $existingRoleIds)->orderBy('id')->get()->toJson();
        $permissionsBefore = DB::table('permissions')->orderBy('id')->get()->toJson();
        $pivotsBefore = DB::table('role_permission')->whereIn('role_id', $existingRoleIds)->orderBy('id')->get()->toJson();
        $usersBefore = DB::table('users')->orderBy('id')->get()->toJson();
        $assignmentsBefore = DB::table('user_role')->orderBy('id')->get()->toJson();

        $this->seed(VemoRoleSeeder::class);

        $this->assertSame($rolesBefore, DB::table('roles')->whereIn('id', $existingRoleIds)->orderBy('id')->get()->toJson());
        $this->assertSame($permissionsBefore, DB::table('permissions')->orderBy('id')->get()->toJson());
        $this->assertSame($pivotsBefore, DB::table('role_permission')->whereIn('role_id', $existingRoleIds)->orderBy('id')->get()->toJson());
        $this->assertSame($usersBefore, DB::table('users')->orderBy('id')->get()->toJson());
        $this->assertSame($assignmentsBefore, DB::table('user_role')->orderBy('id')->get()->toJson());
        $this->assertSame('Existing cashier label', $kasir->fresh()->display_name);
        $this->assertSame('Keep this description', $kasir->fresh()->description);
    }

    public function test_role_permissions_follow_responsibilities_without_privileged_access(): void
    {
        $this->seed(VemoRoleSeeder::class);
        $roles = Role::all()->keyBy('name');

        foreach ($roles as $role) {
            $this->assertTrue($role->hasPermission('dashboard.access'));
            $this->assertTrue($role->hasPermission('master.access'));
            $this->assertTrue($role->hasPermission('operational.access'));
            $this->assertTrue($role->hasPermission('reports.access'));
            $this->assertFalse($role->permissions()->where('permissions.name', 'like', 'management.%')->exists());
            $this->assertFalse($role->permissions()->where('permissions.name', 'like', '%.delete')->exists());
            $this->assertFalse($role->permissions()->where('permissions.name', 'like', '%.force_delete')->exists());
        }

        $this->assertTrue($roles['Kasir']->hasPermission('operational.records.create'));
        $this->assertTrue($roles['Kasir']->hasPermission('operational.records.edit'));
        $this->assertFalse($roles['Kasir']->hasPermission('operational.maintenances.create'));
        $this->assertTrue($roles['Admin Maintenance']->hasPermission('operational.maintenances.create'));
        $this->assertTrue($roles['Admin Maintenance']->hasPermission('operational.maintenances.edit'));
        $this->assertTrue($roles['Admin Maintenance']->hasPermission('operational.vehicle_photos.create'));
        $this->assertFalse($roles['Admin Maintenance']->hasPermission('operational.records.create'));

        foreach ($roles as $name => $role) {
            $this->assertSame($name === 'Supervisor', $role->hasPermission('operational.maintenances.approve'));
            $this->assertSame($name === 'Manager', $role->hasPermission('dashboard.financials.view'));
        }

        $this->assertFalse($roles['Manager']->permissions()->where('permissions.name', 'like', '%.create')->exists());
        $this->assertFalse($roles['Manager']->permissions()->where('permissions.name', 'like', '%.edit')->exists());
        $this->assertTrue($roles['Manager']->hasPermission('reports.monthly.export'));
        $this->assertTrue($roles['Manager']->hasPermission('reports.maintenances.year.export'));
        $this->assertFalse($roles['Supervisor']->hasPermission('operational.maintenances.edit'));
    }

    public function test_undefined_permission_stops_seeding_without_partial_changes(): void
    {
        $modules = config('permission.permissions');
        $modules['operational']['items'] = array_values(array_filter(
            $modules['operational']['items'],
            fn (array $permission): bool => $permission['name'] !== 'operational.records.create',
        ));
        config(['permission.permissions' => $modules]);

        try {
            $this->seed(VemoRoleSeeder::class);
            $this->fail('Undefined permissions must stop the seeder.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('operational.records.create', $exception->getMessage());
        }

        $this->assertDatabaseCount('roles', 0);
        $this->assertDatabaseCount('permissions', 0);
        $this->assertDatabaseCount('role_permission', 0);
    }

    public function test_seeded_roles_enforce_permission_checks_on_actual_routes(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance('env', 'local');
        $this->seed(VemoDummyUserSeeder::class);

        $this->actingAs(User::where('email', 'kasir@test.local')->firstOrFail());
        $this->postJson(route('records.store'), [])->assertUnprocessable();
        $this->postJson(route('maintenances.store'), [])->assertForbidden();
        $this->getJson(route('users.index'))->assertForbidden();

        $this->actingAs(User::where('email', 'maintenance@test.local')->firstOrFail());
        $this->postJson(route('maintenances.store'), [])->assertUnprocessable();
        $this->postJson(route('records.store'), [])->assertForbidden();

        $this->actingAs(User::where('email', 'manager@test.local')->firstOrFail());
        $this->postJson(route('records.store'), [])->assertForbidden();
        $this->postJson(route('maintenances.store'), [])->assertForbidden();
    }

    public function test_only_supervisor_can_confirm_and_company_boundaries_remain_in_effect(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance('env', 'local');
        $this->seed(VemoDummyUserSeeder::class);
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);
        Vehicle::factory()->create(['company_id' => $otherCompany->id]);
        $maintenance = Maintenance::create([
            'code_mt' => 'MT-PERMISSION-TEST',
            'no_pol' => $vehicle->plat_new,
            'date_in' => now()->toDateString(),
            'mileage' => '1000',
            'total_amount' => 0,
            'status' => Maintenance::STATUS_COMPLETED,
        ]);

        foreach (['kasir@test.local', 'maintenance@test.local', 'manager@test.local'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $user->companies()->attach($company);
            $this->actingAs($user);
            $this->postJson(route('maintenances.confirm', $maintenance), [])->assertForbidden();
            $this->assertFalse($maintenance->fresh()->is_confirmed);
        }

        $supervisor = User::where('email', 'supervisor@test.local')->firstOrFail();
        $this->actingAs($supervisor);
        $this->assertSame(0, Vehicle::count());
        $this->postJson(route('maintenances.confirm', $maintenance), [])->assertNotFound();

        $supervisor->companies()->attach($company);
        $this->assertSame([$vehicle->id], Vehicle::pluck('id')->all());
        $this->postJson(route('maintenances.confirm', $maintenance), [])->assertRedirect();
        $this->assertTrue($maintenance->fresh()->is_confirmed);
        $this->assertSame($supervisor->id, $maintenance->fresh()->confirmed_by);
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
