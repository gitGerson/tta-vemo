<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\OpenLDAP\User as LdapUser;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('users.index');
    }

    /**
     * Display a listing of soft deleted users.
     */
    public function trash()
    {
        return view('users.trash');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
        ]);

        try {
            // Query LDAP untuk mencari user berdasarkan username (sesuai format: uid=username)
            $ldapUser = LdapUser::where('uid', $request->username)->first();

            if (!$ldapUser) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Username tidak ditemukan di LDAP. Pastikan username sudah terdaftar di sistem LDAP.');
            }

            // Check if user already exists in our database
            $existingUser = User::where('username', $request->username)->first();
            if ($existingUser) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'User dengan username tersebut sudah ada di database.');
            }

            // Sync user dari LDAP ke database lokal
            $user = User::create([
                'name' => $ldapUser->cn[0] ?? $ldapUser->displayname[0] ?? $ldapUser->uid[0] ?? $request->username,
                'username' => $request->username,
                'email' => $ldapUser->mail[0] ?? $request->username . '@tongtji.com',
                'password' => Hash::make(str()->random(32)), // Random password since we use LDAP auth
                'domain' => $ldapUser->getDn(),
                'guid' => $ldapUser->entryuuid[0] ?? null,
            ]);

            UserLog::log(
                'CREATE_USER',
                "Created user from LDAP sync: {$user->name} ({$user->username})",
                $user,
                null,
                [
                    'username' => $request->username,
                    'ldap_dn' => $ldapUser->getDn(),
                    'ldap_guid' => $ldapUser->entryuuid[0] ?? null,
                ]
            );

            return redirect()->route('users.index')
                ->with('success', "User berhasil ditemukan di LDAP dan disinkronisasi ke database. User: {$user->name} ({$user->username})");
        } catch (\Exception $e) {
            Log::error('LDAP User Sync Error: ' . $e->getMessage(), [
                'username' => $request->username,
                'exception' => $e
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menghubungi LDAP server. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['creator', 'updater', 'deleter', 'roles']);

        UserLog::log(
            'VIEW_USER',
            "Viewed user: {$user->name} ({$user->username})",
            $user
        );

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $permissions = Permission::all();
        $roleTemplates = Role::where('name', 'like', '%_template')->get();

        return view('users.edit', compact('user', 'permissions', 'roleTemplates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $userName = $user->name;
        $userUsername = $user->username;

        $user->delete();

        UserLog::log(
            'DELETE_USER',
            "Soft deleted user: {$userName} ({$userUsername})",
            $user
        );

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }

    /**
     * Restore soft deleted user.
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->deleted_by = null;
        $user->restore();

        UserLog::log(
            'RESTORE_USER',
            "Restored user: {$user->name} ({$user->username})",
            $user
        );

        return redirect()->route('users.trash')->with('success', 'User berhasil dipulihkan');
    }

    /**
     * Permanently delete user.
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $userName = $user->name;
        $userUsername = $user->username;

        UserLog::log(
            'FORCE_DELETE_USER',
            "Permanently deleted user: {$userName} ({$userUsername})",
            $user
        );

        $user->forceDelete();

        return redirect()->route('users.trash')->with('success', 'User berhasil dihapus permanen');
    }

    /**
     * Get data
     */
    public function getData(Request $request)
    {
        $users = User::with('roles')->select(['id', 'name', 'email', 'username']);

        return datatables()->of($users)
            ->addIndexColumn()
            ->addColumn('roles', function ($user) {
                return $user->roles->pluck('display_name')->join(', ') ?: '-';
            })
            ->addColumn('action', function ($user) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                // View action - always available if user has users.view permission
                if (auth()->user()->hasPermission('management.users.view')) {
                    $actions .= '<a class="dropdown-item" href="' . route('users.show', $user->id) . '">
                        <i class="bx bx-show me-1"></i> Lihat
                    </a>';
                }

                // Activity logs
                if (auth()->user()->hasPermission('management.users.logs')) {
                    $actions .= '<a class="dropdown-item" href="' . route('users.user-logs', $user->id) . '">
                        <i class="bx bx-history me-1"></i> Activity Log
                    </a>';
                }

                // Edit action
                if (auth()->user()->hasPermission('management.users.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('users.edit', $user->id) . '">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>';
                    $actions .= '<a class="dropdown-item d-none" href="' . route('users.roles', $user->id) . '">
                        <i class="bx bx-shield me-1"></i> Kelola Role
                    </a>';
                }

                // Delete action
                if (auth()->user()->hasPermission('management.users.delete')) {
                    $actions .= '<form action="' . route('users.destroy', $user->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus user ini?\')">
                            <i class="bx bx-trash me-1"></i> Hapus
                        </button>
                    </form>';
                }

                $actions .= '</div></div>';

                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get trash data
     */
    public function getTrashData(Request $request)
    {
        $users = User::onlyTrashed()
            ->with(['creator', 'updater', 'deleter'])
            ->select(['id', 'name', 'email', 'username', 'deleted_at', 'created_by', 'updated_by', 'deleted_by']);

        return datatables()->of($users)
            ->addColumn('created_by_name', function ($user) {
                return $user->creator ? $user->creator->name : '-';
            })
            ->addColumn('updated_by_name', function ($user) {
                return $user->updater ? $user->updater->name : '-';
            })
            ->addColumn('deleted_by_name', function ($user) {
                return $user->deleter ? $user->deleter->name : '-';
            })
            ->addColumn('action', function ($user) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                // Restore action
                if (auth()->user()->hasPermission('management.users.restore')) {
                    $actions .= '<form action="' . route('users.restore', $user->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin memulihkan user ini?\')">
                            <i class="bx bx-refresh me-1"></i> Pulihkan
                        </button>
                    </form>';
                }

                // Force delete action
                if (auth()->user()->hasPermission('management.users.force_delete')) {
                    $actions .= '<form action="' . route('users.force-delete', $user->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus permanen user ini? Data tidak dapat dipulihkan!\')">
                            <i class="bx bx-trash me-1"></i> Hapus Permanen
                        </button>
                    </form>';
                }

                $actions .= '</div></div>';

                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Display user logs
     */
    public function logs()
    {
        return view('users.logs');
    }

    /**
     * Get user logs data
     */
    public function getLogsData(Request $request)
    {
        $logs = UserLog::with(['user', 'targetUser'])
            ->select(['id', 'user_id', 'target_user_id', 'action', 'description', 'ip_address', 'created_at'])
            ->orderBy('created_at', 'desc');

        return datatables()->of($logs)
            ->addColumn('user_name', function ($log) {
                return $log->user ? $log->user->name : 'System';
            })
            ->addColumn('target_user_name', function ($log) {
                return $log->targetUser ? $log->targetUser->name : '-';
            })
            ->addColumn('formatted_date', function ($log) {
                return $log->created_at->format('d M Y H:i:s');
            })
            ->addColumn('action_badge', function ($log) {
                $badges = [
                    'CREATE_USER' => 'success',
                    'UPDATE_USER' => 'warning',
                    'DELETE_USER' => 'danger',
                    'RESTORE_USER' => 'info',
                    'FORCE_DELETE_USER' => 'dark',
                    'VIEW_USER' => 'secondary',
                ];
                $badgeClass = $badges[$log->action] ?? 'primary';
                return '<span class="badge bg-' . $badgeClass . '">' . str_replace('_', ' ', $log->action) . '</span>';
            })
            ->rawColumns(['action_badge'])
            ->make(true);
    }

    /**
     * Display user logs for specific user
     */
    public function userLogs(User $user)
    {
        return view('users.user-logs', compact('user'));
    }

    /**
     * Get user logs data for specific user
     */
    public function getUserLogsData(Request $request, User $user)
    {
        $logs = UserLog::with(['user'])
            ->where('target_user_id', $user->id)
            ->select(['id', 'user_id', 'action', 'description', 'ip_address', 'created_at'])
            ->orderBy('created_at', 'desc');

        return datatables()->of($logs)
            ->addColumn('user_name', function ($log) {
                return $log->user ? $log->user->name : 'System';
            })
            ->addColumn('formatted_date', function ($log) {
                return $log->created_at->format('d M Y H:i:s');
            })
            ->addColumn('action_badge', function ($log) {
                $badges = [
                    'CREATE_USER' => 'success',
                    'UPDATE_USER' => 'warning',
                    'DELETE_USER' => 'danger',
                    'RESTORE_USER' => 'info',
                    'FORCE_DELETE_USER' => 'dark',
                    'VIEW_USER' => 'secondary',
                ];
                $badgeClass = $badges[$log->action] ?? 'primary';
                return '<span class="badge bg-' . $badgeClass . '">' . str_replace('_', ' ', $log->action) . '</span>';
            })
            ->rawColumns(['action_badge'])
            ->make(true);
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Get current permissions for logging
        $currentPermissions = $user->roles()->with('permissions')->get()
            ->pluck('permissions')->flatten()->pluck('name')->unique()->toArray();

        // Clear all current roles (since we're managing permissions directly)
        $user->roles()->detach();

        // Get selected permissions
        $selectedPermissionIds = $request->permissions ?? [];
        $selectedPermissions = Permission::whereIn('id', $selectedPermissionIds)->get();

        if ($selectedPermissions->count() > 0) {
            // Create a dynamic role for this user with selected permissions
            $dynamicRoleName = 'user_' . $user->id . '_permissions';
            $dynamicRole = Role::updateOrCreate(
                ['name' => $dynamicRoleName],
                [
                    'display_name' => 'Custom Permissions for ' . $user->name,
                    'description' => 'Dynamic role with custom permissions for user ' . $user->username
                ]
            );

            // Clear existing permissions for this dynamic role
            $dynamicRole->permissions()->detach();

            // Assign new permissions to dynamic role
            foreach ($selectedPermissions as $permission) {
                $dynamicRole->assignPermission($permission);
            }

            // Assign dynamic role to user
            $user->assignRole($dynamicRole);
        }

        // Log the permission update
        $newPermissions = $selectedPermissions->pluck('name')->toArray();
        UserLog::log(
            'UPDATE_USER_PERMISSIONS',
            "Updated permissions for user: {$user->name} ({$user->username})",
            $user,
            $user,
            [
                'previous_permissions' => $currentPermissions,
                'new_permissions' => $newPermissions,
                'permissions_count' => count($newPermissions)
            ]
        );

        return redirect()->route('users.edit', $user)
            ->with('success', 'User permissions updated successfully.');
    }

    /**
     * Apply role template to user
     */
    public function applyTemplate(Request $request, User $user)
    {
        $request->validate([
            'role_template' => 'required|exists:roles,id'
        ]);

        $roleTemplate = Role::findOrFail($request->role_template);

        // Get current permissions for logging
        $currentPermissions = $user->roles()->with('permissions')->get()
            ->pluck('permissions')->flatten()->pluck('name')->unique()->toArray();

        // Clear current roles
        $user->roles()->detach();

        // Apply the template
        $user->assignRole($roleTemplate);

        // Log the template application
        $newPermissions = $roleTemplate->permissions->pluck('name')->toArray();
        UserLog::log(
            'APPLY_ROLE_TEMPLATE',
            "Applied role template '{$roleTemplate->display_name}' to user: {$user->name} ({$user->username})",
            $user,
            $user,
            [
                'template_name' => $roleTemplate->display_name,
                'template_id' => $roleTemplate->id,
                'previous_permissions' => $currentPermissions,
                'new_permissions' => $newPermissions
            ]
        );

        return redirect()->route('users.edit', $user)
            ->with('success', "Role template '{$roleTemplate->display_name}' applied successfully.");
    }

    /**
     * Get template permissions for AJAX
     */
    public function getTemplatePermissions(Role $role)
    {
        $permissionIds = $role->permissions()->pluck('permissions.id')->toArray();

        return response()->json([
            'success' => true,
            'permission_ids' => $permissionIds
        ]);
    }
}
