<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update permissions without deleting existing data
        $this->command->info('Creating/updating permissions...');
        $permissions = [

            // === MANAGEMENT (Header) ===
            ['name' => 'management.access', 'display_name' => 'Management', 'description' => 'Can access Management section', 'module' => 'management', 'parent' => null, 'sort_order' => 2],

            // Management > Users
            ['name' => 'management.users.view', 'display_name' => 'Users - View', 'description' => 'Can view users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 1],
            ['name' => 'management.users.create', 'display_name' => 'Users - Create', 'description' => 'Can create users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 2],
            ['name' => 'management.users.edit', 'display_name' => 'Users - Edit', 'description' => 'Can edit users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 3],
            ['name' => 'management.users.delete', 'display_name' => 'Users - Delete', 'description' => 'Can delete users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 4],
            ['name' => 'management.users.restore', 'display_name' => 'Users - Restore', 'description' => 'Can restore deleted users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 5],
            ['name' => 'management.users.force_delete', 'display_name' => 'Users - Force Delete', 'description' => 'Can permanently delete users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 6],
            ['name' => 'management.users.logs', 'display_name' => 'Users - View Logs', 'description' => 'Can view user activity logs', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 7],
            ['name' => 'management.users.permissions', 'display_name' => 'Users - Manage Permissions', 'description' => 'Can manage user permissions directly', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 8],

            // Management > Roles (Templates)
            ['name' => 'management.roles.view', 'display_name' => 'Role Templates - View', 'description' => 'Can view role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 9],
            ['name' => 'management.roles.create', 'display_name' => 'Role Templates - Create', 'description' => 'Can create role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 10],
            ['name' => 'management.roles.edit', 'display_name' => 'Role Templates - Edit', 'description' => 'Can edit role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 11],
            ['name' => 'management.roles.delete', 'display_name' => 'Role Templates - Delete', 'description' => 'Can delete role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 12],

            // === MASTER DATA (Header) ===
            ['name' => 'master.access', 'display_name' => 'Master Data', 'description' => 'Can access Master Data section', 'module' => 'master', 'parent' => null, 'sort_order' => 4],

            // Master > Companies
            ['name' => 'master.companies.view', 'display_name' => 'Companies - View', 'description' => 'Can view companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 1],
            ['name' => 'master.companies.create', 'display_name' => 'Companies - Create', 'description' => 'Can create companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 2],
            ['name' => 'master.companies.edit', 'display_name' => 'Companies - Edit', 'description' => 'Can edit companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 3],
            ['name' => 'master.companies.delete', 'display_name' => 'Companies - Delete', 'description' => 'Can delete companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 4],

            // Master > Drivers
            ['name' => 'master.drivers.view', 'display_name' => 'Drivers - View', 'description' => 'Can view drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 5],
            ['name' => 'master.drivers.create', 'display_name' => 'Drivers - Create', 'description' => 'Can create drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 6],
            ['name' => 'master.drivers.edit', 'display_name' => 'Drivers - Edit', 'description' => 'Can edit drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 7],
            ['name' => 'master.drivers.delete', 'display_name' => 'Drivers - Delete', 'description' => 'Can delete drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 8],

            // Master > Vehicles
            ['name' => 'master.vehicles.view', 'display_name' => 'Vehicles - View', 'description' => 'Can view vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 9],
            ['name' => 'master.vehicles.create', 'display_name' => 'Vehicles - Create', 'description' => 'Can create vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 10],
            ['name' => 'master.vehicles.edit', 'display_name' => 'Vehicles - Edit', 'description' => 'Can edit vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 11],
            ['name' => 'master.vehicles.delete', 'display_name' => 'Vehicles - Delete', 'description' => 'Can delete vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 12],

            // Master > Destinations
            ['name' => 'master.destinations.view', 'display_name' => 'Destinations - View', 'description' => 'Can view destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 13],
            ['name' => 'master.destinations.create', 'display_name' => 'Destinations - Create', 'description' => 'Can create destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 14],
            ['name' => 'master.destinations.edit', 'display_name' => 'Destinations - Edit', 'description' => 'Can edit destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 15],
            ['name' => 'master.destinations.delete', 'display_name' => 'Destinations - Delete', 'description' => 'Can delete destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 16],

            // Master > Type Maintenances
            ['name' => 'master.type_maintenances.view', 'display_name' => 'Type Maintenances - View', 'description' => 'Can view type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 17],
            ['name' => 'master.type_maintenances.create', 'display_name' => 'Type Maintenances - Create', 'description' => 'Can create type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 18],
            ['name' => 'master.type_maintenances.edit', 'display_name' => 'Type Maintenances - Edit', 'description' => 'Can edit type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 19],
            ['name' => 'master.type_maintenances.delete', 'display_name' => 'Type Maintenances - Delete', 'description' => 'Can delete type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 20],

            // === RECORD ===
            ['name' => 'record.access', 'display_name' => 'Record', 'description' => 'Can access Record section', 'module' => 'record', 'parent' => null, 'sort_order' => 5],
            ['name' => 'record.view', 'display_name' => 'Record - View', 'description' => 'Can view records', 'module' => 'record', 'parent' => 'record.access', 'sort_order' => 1],
            ['name' => 'record.create', 'display_name' => 'Record - Create', 'description' => 'Can create records', 'module' => 'record', 'parent' => 'record.access', 'sort_order' => 2],
            ['name' => 'record.edit', 'display_name' => 'Record - Edit', 'description' => 'Can edit records', 'module' => 'record', 'parent' => 'record.access', 'sort_order' => 3],
            ['name' => 'record.delete', 'display_name' => 'Record - Delete', 'description' => 'Can delete records', 'module' => 'record', 'parent' => 'record.access', 'sort_order' => 4],
            ['name' => 'record.restore', 'display_name' => 'Record - Restore', 'description' => 'Can restore deleted records', 'module' => 'record', 'parent' => 'record.access', 'sort_order' => 5],
            ['name' => 'record.force_delete', 'display_name' => 'Record - Force Delete', 'description' => 'Can permanently delete records', 'module' => 'record', 'parent' => 'record.access', 'sort_order' => 6],

            // === MAINTENANCES ===
            ['name' => 'maintenances.access', 'display_name' => 'Maintenances', 'description' => 'Can access Maintenances section', 'module' => 'maintenances', 'parent' => null, 'sort_order' => 6],
            ['name' => 'maintenances.view', 'display_name' => 'Maintenances - View', 'description' => 'Can view maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 1],
            ['name' => 'maintenances.create', 'display_name' => 'Maintenances - Create', 'description' => 'Can create maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 2],
            ['name' => 'maintenances.edit', 'display_name' => 'Maintenances - Edit', 'description' => 'Can edit maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 3],
            ['name' => 'maintenances.approve', 'display_name' => 'Maintenances - Approve', 'description' => 'Can approve maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 4],
            ['name' => 'maintenances.delete', 'display_name' => 'Maintenances - Delete', 'description' => 'Can delete maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 5],

            // === REPORTS ===
            ['name' => 'reports.access', 'display_name' => 'Reports', 'description' => 'Can access Reports section', 'module' => 'reports', 'parent' => null, 'sort_order' => 7],
            ['name' => 'reports.daily.view', 'display_name' => 'Reports - Daily View', 'description' => 'Can view daily reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 1],
            ['name' => 'reports.daily.export', 'display_name' => 'Reports - Daily Export', 'description' => 'Can export daily reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 2],
            ['name' => 'reports.monthly.view', 'display_name' => 'Reports - Monthly View', 'description' => 'Can view monthly reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 3],
            ['name' => 'reports.monthly.export', 'display_name' => 'Reports - Monthly Export', 'description' => 'Can export monthly reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 4],
            ['name' => 'reports.maintenances.daily.view', 'display_name' => 'Reports - Maintenances Daily View', 'description' => 'Can view maintenances daily reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 5],
            ['name' => 'reports.maintenances.daily.export', 'display_name' => 'Reports - Maintenances Daily Export', 'description' => 'Can export maintenances daily reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 6],
            ['name' => 'reports.maintenances.year.view', 'display_name' => 'Reports - Maintenances Year View', 'description' => 'Can view maintenances yearly reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 7],
            ['name' => 'reports.maintenances.year.export', 'display_name' => 'Reports - Maintenances Year Export', 'description' => 'Can export maintenances yearly reports', 'module' => 'reports', 'parent' => 'reports.access', 'sort_order' => 8],

        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create role templates for user creation
        $this->command->info('Creating role templates...');
        $roles = [
            [
                'name' => 'super_admin_template',
                'display_name' => 'Super Administrator Template',
                'description' => 'Superadmin - full access',
                'permissions' => Permission::all()->pluck('name')->toArray()
            ],
            [
                'name' => 'admin_template',
                'display_name' => 'Administrator Template',
                'description' => 'Admin - full CRUD, no approval',
                'permissions' => [
                    'management.access',
                    'management.users.view',
                    'management.users.create',
                    'management.users.edit',
                    'management.users.delete',
                    'management.users.restore',
                    'management.users.logs',
                    'management.roles.view',
                    'management.roles.create',
                    'management.roles.edit',
                    'master.access',
                    'master.companies.view',
                    'master.companies.create',
                    'master.companies.edit',
                    'master.companies.delete',
                    'master.drivers.view',
                    'master.drivers.create',
                    'master.drivers.edit',
                    'master.drivers.delete',
                    'master.vehicles.view',
                    'master.vehicles.create',
                    'master.vehicles.edit',
                    'master.vehicles.delete',
                    'master.destinations.view',
                    'master.destinations.create',
                    'master.destinations.edit',
                    'master.destinations.delete',
                    'master.type_maintenances.view',
                    'master.type_maintenances.create',
                    'master.type_maintenances.edit',
                    'master.type_maintenances.delete',
                    'record.access',
                    'record.view',
                    'record.create',
                    'record.edit',
                    'record.delete',
                    'record.restore',
                    'record.force_delete',
                    'maintenances.access',
                    'maintenances.view',
                    'maintenances.create',
                    'maintenances.edit',
                    'maintenances.delete',
                    'reports.access',
                    'reports.daily.view',
                    'reports.daily.export',
                    'reports.monthly.view',
                    'reports.monthly.export',
                    'reports.maintenances.daily.view',
                    'reports.maintenances.daily.export',
                    'reports.maintenances.year.view',
                    'reports.maintenances.year.export',
                ]
            ],
            [
                'name' => 'approval_template',
                'display_name' => 'Approval Template',
                'description' => 'Approval - view all with approval',
                'permissions' => [
                    'management.access',
                    'management.users.view',
                    'master.access',
                    'master.companies.view',
                    'master.drivers.view',
                    'master.vehicles.view',
                    'master.destinations.view',
                    'master.type_maintenances.view',
                    'record.access',
                    'record.view',
                    'maintenances.access',
                    'maintenances.view',
                    'maintenances.approve',
                    'reports.access',
                    'reports.daily.view',
                    'reports.daily.export',
                    'reports.monthly.view',
                    'reports.monthly.export',
                    'reports.maintenances.daily.view',
                    'reports.maintenances.daily.export',
                    'reports.maintenances.year.view',
                    'reports.maintenances.year.export',
                ]
            ],
            [
                'name' => 'user_template',
                'display_name' => 'User Template',
                'description' => 'User - view all, add record, add maintenance, no delete, no approval',
                'permissions' => [
                    'management.access',
                    'management.users.view',
                    'master.access',
                    'master.companies.view',
                    'master.drivers.view',
                    'master.vehicles.view',
                    'master.destinations.view',
                    'master.type_maintenances.view',
                    'record.access',
                    'record.view',
                    'record.create',
                    'maintenances.access',
                    'maintenances.view',
                    'maintenances.create',
                    'reports.access',
                    'reports.daily.view',
                    'reports.daily.export',
                    'reports.monthly.view',
                    'reports.monthly.export',
                    'reports.maintenances.daily.view',
                    'reports.maintenances.daily.export',
                    'reports.maintenances.year.view',
                    'reports.maintenances.year.export',
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description']
                ]
            );

            // Ensure role has listed permissions (no removals)
            foreach ($roleData['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$role->hasPermission($permission)) {
                    $role->assignPermission($permission);
                }
            }
        }

        // Remove legacy templates not requested (only if unused)
        $legacyTemplates = ['user_manager_template', 'viewer_template'];
        foreach ($legacyTemplates as $legacyName) {
            $legacyRole = Role::where('name', $legacyName)->first();
            if (!$legacyRole) {
                continue;
            }

            if ($legacyRole->users()->exists()) {
                $this->command->info("Skipping deletion of {$legacyName}; role still assigned to users.");
                continue;
            }

            $legacyRole->permissions()->detach();
            $legacyRole->delete();
            $this->command->info("Deleted unused role template: {$legacyName}");
        }

        // Ensure super_admin_template always has all permissions
        $superAdminRole = Role::where('name', 'super_admin_template')->first();
        if ($superAdminRole) {
            foreach (Permission::all() as $permission) {
                if (!$superAdminRole->hasPermission($permission)) {
                    $superAdminRole->assignPermission($permission);
                }
            }
        }

        // Ensure stagingpurpose user exists and has super_admin_template
        $superAdminUser = User::firstOrCreate(
            ['username' => 'stagingpurpose'],
            [
                'name' => 'STAGING PURPOSE',
                'email' => 'staging@tongtji.com',
                'password' => Hash::make('P@ssw0rd1938'),
                'email_verified_at' => now(),
                'created_by' => null,
                'updated_by' => null,
            ]
        );

        if ($superAdminRole) {
            $superAdminUser->assignRole($superAdminRole);
        }
    }
}
