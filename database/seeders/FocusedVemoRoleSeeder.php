<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Explicit VEMO role migration. Run separately from DatabaseSeeder.
 * Back up existing user-role assignments before executing.
 */
class FocusedVemoRoleSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = collect(config('permission.permissions', []))
            ->flatMap(fn (array $group): array => $group['items'] ?? [])
            ->keyBy('name');
        $matrix = $this->rolePermissions($definitions->keys()->all());
        $missing = collect($matrix)->flatten()->unique()->diff($definitions->keys());

        if ($missing->isNotEmpty()) {
            throw new InvalidArgumentException('Unknown permissions: '.$missing->implode(', '));
        }

        DB::transaction(function () use ($matrix): void {
            foreach ($matrix as $name => $permissionNames) {
                $role = Role::firstOrCreate(
                    ['name' => $name],
                    ['display_name' => $name, 'guard_name' => 'web', 'is_active' => true],
                );

                $ids = Permission::whereIn('name', $permissionNames)->pluck('id')->all();
                $role->permissions()->sync($ids);
            }
        });
    }

    private function rolePermissions(array $available): array
    {
        $master = array_values(array_filter(
            $available,
            fn (string $name): bool => str_starts_with($name, 'master.'),
        ));

        return [
            'Admin' => [
                'dashboard.access', ...$master,
                'operational.access',
                'operational.maintenances.view',
                'operational.maintenances.create',
                'operational.maintenances.edit',
                'operational.maintenances.reminders',
                'reports.access',
                'reports.maintenances.daily.view',
                'reports.maintenances.monthly.view',
                'reports.analysis_maintenances_monthly.view',
                'reports.maintenances.year.view',
            ],
            'Kasir' => [
                'dashboard.access',
                'operational.access',
                'operational.records.view',
                'operational.records.create',
                'operational.records.edit',
                'management.tenant.bypass',
                'reports.access',
                'reports.daily.view',
                'reports.daily.export',
                'reports.monthly.view',
                'reports.monthly.export',
                'reports.analysis_monthly.view',
                'reports.analysis_monthly.export',
            ],
            'SPV' => [
                'dashboard.access',
                'operational.access',
                'master.access',
                'master.vehicles.view',
                'operational.maintenances.view',
                'operational.maintenances.approve',
                'reports.access',
                'reports.maintenances.daily.view',
                'reports.maintenances.monthly.view',
            ],
        ];
    }
}
