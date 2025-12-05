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
        // Clear existing data
        $this->command->info('Clearing existing roles, permissions, and user assignments...');

        // Disable foreign key checks temporarily
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        \DB::table('user_role')->delete();
        \DB::table('role_permission')->delete();
        Role::truncate();
        Permission::truncate();

        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create permissions following menu hierarchy structure
        $this->command->info('Creating permissions...');
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

            // === IMAGES ===
            ['name' => 'images.access', 'display_name' => 'Images', 'description' => 'Can access Images section', 'module' => 'images', 'parent' => null, 'sort_order' => 3],
            ['name' => 'images.view', 'display_name' => 'Images - View', 'description' => 'Can view images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 1],
            ['name' => 'images.create', 'display_name' => 'Images - Upload', 'description' => 'Can upload images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 2],
            ['name' => 'images.edit', 'display_name' => 'Images - Edit', 'description' => 'Can edit images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 3],
            ['name' => 'images.delete', 'display_name' => 'Images - Delete', 'description' => 'Can delete images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 4],
            ['name' => 'images.restore', 'display_name' => 'Images - Restore', 'description' => 'Can restore deleted images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 5],
            ['name' => 'images.force_delete', 'display_name' => 'Images - Force Delete', 'description' => 'Can permanently delete images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 6],

            // === MASTER DATA (Header) ===
            ['name' => 'master.access', 'display_name' => 'Master Data', 'description' => 'Can access Master Data section', 'module' => 'master', 'parent' => null, 'sort_order' => 4],

            // Master > Categories
            ['name' => 'master.categories.view', 'display_name' => 'Categories - View', 'description' => 'Can view categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 1],
            ['name' => 'master.categories.create', 'display_name' => 'Categories - Create', 'description' => 'Can create categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 2],
            ['name' => 'master.categories.edit', 'display_name' => 'Categories - Edit', 'description' => 'Can edit categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 3],
            ['name' => 'master.categories.delete', 'display_name' => 'Categories - Delete', 'description' => 'Can delete categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 4],
            ['name' => 'master.categories.restore', 'display_name' => 'Categories - Restore', 'description' => 'Can restore deleted categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 5],
            ['name' => 'master.categories.force_delete', 'display_name' => 'Categories - Force Delete', 'description' => 'Can permanently delete categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 6],

            // Master > Products
            ['name' => 'master.products.view', 'display_name' => 'Products - View', 'description' => 'Can view products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 7],
            ['name' => 'master.products.create', 'display_name' => 'Products - Create', 'description' => 'Can create products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 8],
            ['name' => 'master.products.edit', 'display_name' => 'Products - Edit', 'description' => 'Can edit products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 9],
            ['name' => 'master.products.delete', 'display_name' => 'Products - Delete', 'description' => 'Can delete products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 10],
            ['name' => 'master.products.restore', 'display_name' => 'Products - Restore', 'description' => 'Can restore deleted products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 11],
            ['name' => 'master.products.force_delete', 'display_name' => 'Products - Force Delete', 'description' => 'Can permanently delete products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 12],

            // Master > Companies
            ['name' => 'master.companies.view', 'display_name' => 'Companies - View', 'description' => 'Can view companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 13],
            ['name' => 'master.companies.create', 'display_name' => 'Companies - Create', 'description' => 'Can create companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 14],
            ['name' => 'master.companies.edit', 'display_name' => 'Companies - Edit', 'description' => 'Can edit companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 15],
            ['name' => 'master.companies.delete', 'display_name' => 'Companies - Delete', 'description' => 'Can delete companies', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 16],

            // Master > Drivers
            ['name' => 'master.drivers.view', 'display_name' => 'Drivers - View', 'description' => 'Can view drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 17],
            ['name' => 'master.drivers.create', 'display_name' => 'Drivers - Create', 'description' => 'Can create drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 18],
            ['name' => 'master.drivers.edit', 'display_name' => 'Drivers - Edit', 'description' => 'Can edit drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 19],
            ['name' => 'master.drivers.delete', 'display_name' => 'Drivers - Delete', 'description' => 'Can delete drivers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 20],

            // Master > Vehicles
            ['name' => 'master.vehicles.view', 'display_name' => 'Vehicles - View', 'description' => 'Can view vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 21],
            ['name' => 'master.vehicles.create', 'display_name' => 'Vehicles - Create', 'description' => 'Can create vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 22],
            ['name' => 'master.vehicles.edit', 'display_name' => 'Vehicles - Edit', 'description' => 'Can edit vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 23],
            ['name' => 'master.vehicles.delete', 'display_name' => 'Vehicles - Delete', 'description' => 'Can delete vehicles', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 24],

            // Master > Destinations
            ['name' => 'master.destinations.view', 'display_name' => 'Destinations - View', 'description' => 'Can view destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 25],
            ['name' => 'master.destinations.create', 'display_name' => 'Destinations - Create', 'description' => 'Can create destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 26],
            ['name' => 'master.destinations.edit', 'display_name' => 'Destinations - Edit', 'description' => 'Can edit destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 27],
            ['name' => 'master.destinations.delete', 'display_name' => 'Destinations - Delete', 'description' => 'Can delete destinations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 28],

            // Master > Type Maintenances
            ['name' => 'master.type_maintenances.view', 'display_name' => 'Type Maintenances - View', 'description' => 'Can view type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 29],
            ['name' => 'master.type_maintenances.create', 'display_name' => 'Type Maintenances - Create', 'description' => 'Can create type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 30],
            ['name' => 'master.type_maintenances.edit', 'display_name' => 'Type Maintenances - Edit', 'description' => 'Can edit type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 31],
            ['name' => 'master.type_maintenances.delete', 'display_name' => 'Type Maintenances - Delete', 'description' => 'Can delete type maintenances', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 32],

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
            ['name' => 'maintenances.delete', 'display_name' => 'Maintenances - Delete', 'description' => 'Can delete maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 4],
            ['name' => 'maintenances.restore', 'display_name' => 'Maintenances - Restore', 'description' => 'Can restore maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 5],
            ['name' => 'maintenances.force_delete', 'display_name' => 'Maintenances - Force Delete', 'description' => 'Can permanently delete maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 6],

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
            Permission::create($permission);
        }

        // Create role templates for user creation
        $this->command->info('Creating role templates...');
        $roles = [
            [
                'name' => 'super_admin_template',
                'display_name' => 'Super Administrator Template',
                'description' => 'Template with full system access - all permissions',
                'permissions' => Permission::all()->pluck('name')->toArray()
            ],
            [
                'name' => 'admin_template',
                'display_name' => 'Administrator Template',
                'description' => 'Template with management access and most features',
                'permissions' => [
                    'dashboard.view',
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
                    'images.access',
                    'images.view',
                    'images.create',
                    'images.edit',
                    'images.delete',
                    'images.restore',
                    'master.access',
                    'master.categories.view',
                    'master.categories.create',
                    'master.categories.edit',
                    'master.categories.delete',
                    'master.categories.restore',
                    'master.products.view',
                    'master.products.create',
                    'master.products.edit',
                    'master.products.delete',
                    'master.products.restore',
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
                    'maintenances.restore',
                    'maintenances.force_delete',
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
                'name' => 'user_manager_template',
                'display_name' => 'User Manager Template',
                'description' => 'Template for user management focus',
                'permissions' => [
                    'dashboard.view',
                    'management.access',
                    'management.users.view',
                    'management.users.create',
                    'management.users.edit',
                    'management.users.logs',
                    'management.roles.view',
                ]
            ],
            [
                'name' => 'viewer_template',
                'display_name' => 'Viewer Template',
                'description' => 'Template for read-only access',
                'permissions' => [
                    'dashboard.view',
                    'management.access',
                    'management.users.view',
                    'master.access',
                    'master.categories.view',
                    'master.products.view',
                    'master.companies.view',
                    'master.drivers.view',
                    'master.vehicles.view',
                    'master.destinations.view',
                    'master.type_maintenances.view',
                    'record.access',
                    'record.view',
                    'maintenances.access',
                    'maintenances.view',
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
            $role = Role::create([
                'name' => $roleData['name'],
                'display_name' => $roleData['display_name'],
                'description' => $roleData['description']
            ]);

            // Assign permissions to role
            foreach ($roleData['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$role->hasPermission($permission)) {
                    $role->assignPermission($permission);
                }
            }
        }

        // Create fresh stagingpurpose superadmin user
        $this->command->info('Creating fresh stagingpurpose superadmin user...');

        // Delete existing stagingpurpose user if exists
        User::where('username', 'stagingpurpose')->forceDelete();

        // Create new stagingpurpose user
        $superAdminUser = User::create([
            'name' => 'STAGING PURPOSE',
            'username' => 'stagingpurpose',
            'email' => 'staging@tongtji.com',
            'password' => Hash::make('P@ssw0rd1938'),
            'email_verified_at' => now(),
            'created_by' => null, // No creator for initial superadmin
            'updated_by' => null,
        ]);

        // Assign super_admin_template role to stagingpurpose user
        $superAdminRole = Role::where('name', 'super_admin_template')->first();
        if ($superAdminRole) {
            $superAdminUser->assignRole($superAdminRole);
        }

        $this->command->info('Fresh roles, permissions, and stagingpurpose superadmin created successfully!');
        $this->command->info('Superadmin credentials:');
        $this->command->info('Username: stagingpurpose');
        $this->command->info('Password: P@ssw0rd1938');
        $this->command->info('PIN: 123456');
    }
}
