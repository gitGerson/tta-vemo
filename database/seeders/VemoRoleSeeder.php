<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Defines the permissions for these four roles; rerunning replaces their manual grants.
 * Existing permission definitions, other roles, and user assignments are preserved.
 */
class VemoRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = $this->permissionsByRole();
        $definitions = collect(config('permission.permissions', []))
            ->flatMap(fn (array $module): array => $module['items'] ?? [])
            ->keyBy('name');
        $requiredPermissions = collect($roles)->flatten()->unique()->values();
        $undefinedPermissions = $requiredPermissions->diff($definitions->keys());

        if ($undefinedPermissions->isNotEmpty()) {
            throw new InvalidArgumentException(
                'Permission VEMO tidak ditemukan di config/permission.php: '.$undefinedPermissions->implode(', '),
            );
        }

        DB::transaction(function () use ($roles, $definitions, $requiredPermissions): void {
            $permissions = $requiredPermissions->mapWithKeys(function (string $name) use ($definitions): array {
                $permission = Permission::firstOrCreate(
                    ['name' => $name],
                    Arr::except($definitions->get($name), ['name']),
                );

                return [$name => $permission->id];
            });

            foreach ($roles as $name => $permissionNames) {
                $role = Role::firstOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $name,
                        'guard_name' => 'web',
                        'is_active' => true,
                    ],
                );

                $role->permissions()->sync(
                    collect($permissionNames)->map(fn (string $name): int => $permissions->get($name))->all(),
                );
            }
        });
    }

    /** @return array<string, list<string>> */
    protected function permissionsByRole(): array
    {
        $common = [
            'dashboard.access',
            'master.access',
            'master.companies.view',
            'master.vehicles.view',
            'operational.access',
            'reports.access',
        ];

        $fuelReports = [
            'reports.daily.view',
            'reports.monthly.view',
            'reports.analysis_monthly.view',
        ];

        $maintenanceReports = [
            'reports.maintenances.daily.view',
            'reports.maintenances.monthly.view',
            'reports.analysis_maintenances_monthly.view',
            'reports.maintenances.year.view',
        ];

        $fuelExports = [
            'reports.daily.export',
            'reports.monthly.export',
            'reports.analysis_monthly.export',
        ];

        $maintenanceExports = [
            'reports.maintenances.daily.export',
            'reports.maintenances.monthly.export',
            'reports.analysis_maintenances_monthly.export',
            'reports.maintenances.year.export',
        ];

        return [
            'Kasir' => [
                ...$common,
                'master.drivers.view',
                'master.destinations.view',
                'master.fuel_types.view',
                'operational.records.view',
                'operational.records.create',
                'operational.records.edit',
                ...$fuelReports,
                ...$fuelExports,
            ],
            'Admin Maintenance' => [
                ...$common,
                'master.type_maintenances.view',
                'master.vehicles.reminders',
                'operational.maintenances.view',
                'operational.maintenances.create',
                'operational.maintenances.edit',
                'operational.maintenances.reminders',
                'operational.vehicle_photos.view',
                'operational.vehicle_photos.create',
                'operational.vehicle_photos.edit',
                'operational.vehicle_photos.reminders',
                ...$maintenanceReports,
                ...$maintenanceExports,
            ],
            'Manager' => [
                ...$common,
                'dashboard.financials.view',
                'master.drivers.view',
                'master.type_maintenances.view',
                'operational.records.view',
                'operational.maintenances.view',
                'operational.vehicle_photos.view',
                ...$fuelReports,
                ...$maintenanceReports,
                ...$fuelExports,
                ...$maintenanceExports,
            ],
            'Supervisor' => [
                ...$common,
                'master.drivers.view',
                'master.type_maintenances.view',
                'operational.records.view',
                'operational.maintenances.view',
                'operational.maintenances.approve',
                'operational.maintenances.reminders',
                'operational.vehicle_photos.view',
                ...$fuelReports,
                ...$maintenanceReports,
            ],
        ];
    }
}
