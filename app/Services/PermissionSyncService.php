<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class PermissionSyncService
{
    public function sync(?callable $output = null): void
    {
        $permissions = $this->permissionDefinitions();
        $roleTemplates = $this->roleTemplates($permissions);

        $this->info($output, 'Syncing permissions, role templates, and stagingpurpose superadmin user...');

        DB::transaction(function () use ($permissions, $roleTemplates, $output): void {
            $this->seedPermissions($permissions, $output);
            $this->pruneRemovedPermissions($permissions, $output);
            $this->seedRoleTemplates($roleTemplates, $output);
            $this->seedSuperAdminUser($output);
        });

        $this->info($output, 'Roles, permissions, and stagingpurpose superadmin synced successfully.');
        $this->info($output, 'Superadmin credentials:');
        $this->info($output, 'Username: stagingpurpose');
        $this->info($output, 'Password: P@ssw0rd1938');
        $this->info($output, 'PIN: 123456');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function permissionDefinitions(): array
    {
        $modules = config('permission.permissions', []);
        $permissions = [];

        foreach ($modules as $module => $definition) {
            $items = $definition['items'] ?? [];

            foreach ($items as $item) {
                $permissions[] = array_merge(
                    [
                        'module' => $module,
                        'parent' => null,
                    ],
                    $item
                );
            }
        }

        $names = array_column($permissions, 'name');
        $duplicates = array_keys(array_filter(array_count_values($names), static fn (int $count): bool => $count > 1));

        if ($duplicates !== []) {
            throw new InvalidArgumentException('Duplicate permission names found in config/permission.php: '.implode(', ', $duplicates));
        }

        return $permissions;
    }

    /**
     * @param  array<int, array<string, mixed>>  $permissions
     * @return array<int, array<string, mixed>>
     */
    protected function roleTemplates(array $permissions): array
    {
        $availablePermissions = array_column($permissions, 'name');
        $templates = config('permission.role_templates', []);
        $templateNames = array_column($templates, 'name');
        $duplicateTemplates = array_keys(array_filter(array_count_values($templateNames), static fn (int $count): bool => $count > 1));

        if ($duplicateTemplates !== []) {
            throw new InvalidArgumentException('Duplicate role template names found in config/permission.php: '.implode(', ', $duplicateTemplates));
        }

        foreach ($templates as &$template) {
            $templatePermissions = $template['permissions'] ?? [];

            if ($templatePermissions === ['*']) {
                $template['permissions'] = $availablePermissions;
                continue;
            }

            $unknownPermissions = array_values(array_diff($templatePermissions, $availablePermissions));

            if ($unknownPermissions !== []) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Role template [%s] references undefined permissions: %s',
                        $template['name'] ?? 'unknown',
                        implode(', ', $unknownPermissions)
                    )
                );
            }
        }
        unset($template);

        return $templates;
    }

    /**
     * @param  array<int, array<string, mixed>>  $permissions
     */
    protected function seedPermissions(array $permissions, ?callable $output = null): void
    {
        $this->info($output, 'Syncing permissions...');

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                Arr::except($permission, ['name'])
            );
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $permissions
     */
    protected function pruneRemovedPermissions(array $permissions, ?callable $output = null): void
    {
        $configuredPermissionNames = array_column($permissions, 'name');
        $permissionsToPrune = Permission::query()
            ->whereNotIn('name', $configuredPermissionNames)
            ->pluck('name');

        if ($permissionsToPrune->isEmpty()) {
            return;
        }

        $this->info($output, 'Pruning removed permissions: '.$permissionsToPrune->implode(', '));

        Permission::query()
            ->whereIn('name', $permissionsToPrune->all())
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $templates
     */
    protected function seedRoleTemplates(array $templates, ?callable $output = null): void
    {
        $this->info($output, 'Syncing role templates...');

        $permissionsByName = Permission::query()->get()->keyBy('name');

        foreach ($templates as $template) {
            $role = Role::updateOrCreate(
                ['name' => $template['name']],
                Arr::only($template, ['display_name', 'description'])
            );

            $permissionIds = collect($template['permissions'])
                ->map(fn (string $permissionName) => $permissionsByName->get($permissionName)?->id)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }

    protected function seedSuperAdminUser(?callable $output = null): void
    {
        $this->info($output, 'Syncing stagingpurpose superadmin user...');

        $superAdmin = config('permission.super_admin_user', []);
        $superAdminUser = User::withTrashed()->firstWhere('username', $superAdmin['username']);

        if ($superAdminUser) {
            if ($superAdminUser->trashed()) {
                $superAdminUser->restore();
            }

            $superAdminUser->fill([
                'name' => $superAdmin['name'],
                'email' => $superAdmin['email'],
                'password' => Hash::make($superAdmin['password']),
                'email_verified_at' => now(),
            ]);
            $superAdminUser->save();
        } else {
            $superAdminUser = User::create([
                'name' => $superAdmin['name'],
                'username' => $superAdmin['username'],
                'email' => $superAdmin['email'],
                'password' => Hash::make($superAdmin['password']),
                'email_verified_at' => now(),
                'created_by' => null,
                'updated_by' => null,
            ]);
        }

        $superAdminRole = Role::where('name', $superAdmin['role'])->first();

        if ($superAdminRole) {
            $superAdminUser->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }

    protected function info(?callable $output, string $message): void
    {
        if ($output !== null) {
            $output($message);
        }
    }
}
