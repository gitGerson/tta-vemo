<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * One-time role migration: retain only the privileged template and focused roles.
 *
 * Usage:
 * php artisan db:seed --class=RolePermissionSeeder
 * php artisan db:seed --class=FocusedVemoRoleSeeder
 * php artisan db:seed --class=PruneLegacyVemoRolesSeeder
 *
 * This removes retired role assignments through FK cascades, but not users.
 * Take a DB backup and reassign affected users to their intended new roles.
 */
class PruneLegacyVemoRolesSeeder extends Seeder
{
    private const KEEP = ['super_admin_template', 'Admin', 'Kasir', 'SPV'];

    public function run(): void
    {
        DB::transaction(function (): void {
            $present = Role::whereIn('name', self::KEEP)->pluck('name')->all();

            if (count($present) !== count(self::KEEP)) {
                throw new RuntimeException(
                    'Required roles missing. Run RolePermissionSeeder and FocusedVemoRoleSeeder before pruning.'
                );
            }

            $retired = Role::whereNotIn('name', self::KEEP)->get();

            foreach ($retired as $role) {
                $this->command?->warn('Removing retired role: '.$role->name);
                $role->delete();
            }
        });
    }
}
