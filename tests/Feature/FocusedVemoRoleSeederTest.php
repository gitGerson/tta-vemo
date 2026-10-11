<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\FocusedVemoRoleSeeder;
use Database\Seeders\PruneLegacyVemoRolesSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FocusedVemoRoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_focused_roles_have_separated_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(FocusedVemoRoleSeeder::class);

        $admin = Role::where('name', 'Admin')->firstOrFail();
        $kasir = Role::where('name', 'Kasir')->firstOrFail();
        $spv = Role::where('name', 'SPV')->firstOrFail();

        $this->assertTrue($admin->hasPermission('operational.maintenances.create'));
        $this->assertFalse($admin->hasPermission('operational.maintenances.approve'));
        $this->assertFalse($admin->hasPermission('operational.records.create'));

        $this->assertTrue($kasir->hasPermission('operational.records.create'));
        $this->assertTrue($kasir->hasPermission('management.tenant.bypass'));
        $this->assertTrue($kasir->hasPermission('reports.monthly.view'));
        $this->assertFalse($kasir->hasPermission('operational.maintenances.create'));

        $this->assertTrue($spv->hasPermission('operational.maintenances.approve'));
        $this->assertFalse($spv->hasPermission('operational.maintenances.edit'));
        $this->assertFalse($spv->hasPermission('operational.records.create'));
    }

    public function test_prune_retains_superadmin_and_three_roles_without_deleting_users(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(FocusedVemoRoleSeeder::class);

        $retired = Role::create(['name' => 'retired_role', 'display_name' => 'Retired role']);
        $user = User::factory()->create();
        $user->assignRole($retired);

        $this->seed(PruneLegacyVemoRolesSeeder::class);

        $this->assertEqualsCanonicalizing(
            ['super_admin_template', 'Admin', 'Kasir', 'SPV'],
            Role::pluck('name')->all()
        );
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('user_role', ['role_id' => $retired->id]);
    }

    public function test_reseeding_focused_roles_is_repeatable(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(FocusedVemoRoleSeeder::class);
        $this->seed(PruneLegacyVemoRolesSeeder::class);
        $this->seed(FocusedVemoRoleSeeder::class);

        $this->assertSame(4, Role::count());
    }
}
