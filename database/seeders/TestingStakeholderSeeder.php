<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestingStakeholderSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('TestingStakeholderSeeder is disabled in production.');

            return;
        }

        $roles = [
            'kasir' => [
                'display_name' => 'Kasir',
                'description' => 'Testing stakeholder role for cashier operations.',
                'permissions' => ['record.view', 'record.create', 'reports.daily.view'],
            ],
            'admin_maintenance' => [
                'display_name' => 'Admin Maintenance',
                'description' => 'Testing stakeholder role for maintenance administration.',
                'permissions' => [
                    'maintenances.view', 'maintenances.create', 'maintenances.edit',
                    'master.vehicles.view', 'master.type_maintenances.view',
                ],
            ],
            'manager' => [
                'display_name' => 'Manager',
                'description' => 'Testing stakeholder role for operational reporting.',
                'permissions' => [
                    'record.view', 'maintenances.view',
                    'reports.daily.view', 'reports.daily.export',
                    'reports.monthly.view', 'reports.monthly.export',
                    'reports.maintenances.daily.view', 'reports.maintenances.daily.export',
                    'reports.maintenances.year.view', 'reports.maintenances.year.export',
                ],
            ],
            'supervisor' => [
                'display_name' => 'Supervisor',
                'description' => 'Testing stakeholder role for maintenance approval.',
                'permissions' => [
                    'record.view', 'maintenances.view', 'maintenances.approve',
                    'reports.daily.view', 'reports.monthly.view',
                    'reports.maintenances.daily.view', 'reports.maintenances.year.view',
                ],
            ],
        ];

        $users = [
            ['email' => 'kasir.test@example.com', 'username' => 'testing.kasir', 'name' => 'Testing Kasir', 'role' => 'kasir'],
            ['email' => 'maintenance.test@example.com', 'username' => 'testing.maintenance', 'name' => 'Testing Admin Maintenance', 'role' => 'admin_maintenance'],
            ['email' => 'manager.test@example.com', 'username' => 'testing.manager', 'name' => 'Testing Manager', 'role' => 'manager'],
            ['email' => 'supervisor.test@example.com', 'username' => 'testing.supervisor', 'name' => 'Testing Supervisor', 'role' => 'supervisor'],
        ];

        DB::transaction(function () use ($roles, $users): void {
            $roleModels = [];

            foreach ($roles as $name => $attributes) {
                $role = Role::firstOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $attributes['display_name'],
                        'description' => $attributes['description'],
                        'is_active' => true,
                    ],
                );

                // Existing role configuration is intentionally left untouched.
                if ($role->wasRecentlyCreated) {
                    $permissions = Permission::query()
                        ->whereIn('name', $attributes['permissions'])
                        ->get();

                    $role->permissions()->syncWithoutDetaching($permissions->pluck('id')->all());

                    $missingPermissions = array_diff($attributes['permissions'], $permissions->pluck('name')->all());
                    if ($missingPermissions !== []) {
                        $this->command?->warn(
                            "Role {$name} was created without missing permissions: ".implode(', ', $missingPermissions),
                        );
                    }
                }

                $roleModels[$name] = $role;
            }

            foreach ($users as $attributes) {
                $user = User::updateOrCreate(
                    ['email' => $attributes['email']],
                    [
                        'name' => $attributes['name'],
                        'username' => $attributes['username'],
                        'password' => Hash::make('P@ssw0rd1938'),
                        'email_verified_at' => now(),
                    ],
                );

                $role = $roleModels[$attributes['role']];
                if (! $user->roles()->whereKey($role->id)->exists()) {
                    $user->roles()->attach($role->id, [
                        'assigned_by' => null,
                        'assigned_at' => now(),
                    ]);
                }
            }
        });
    }
}
