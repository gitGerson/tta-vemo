<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use LdapRecord\Models\OpenLDAP\User as LdapUser;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::where('is_active', true)
            ->orderBy('display_name')
            ->get();
        $companies = Company::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('users.index', compact('companies', 'roles'));
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
        $roles = Role::where('is_active', true)
            ->orderBy('display_name')
            ->get();
        $companies = Company::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('users.create', compact('companies', 'roles'));
    }

    public function verifyLdap(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        if (User::where('username', $request->username)->exists()) {
            return response()->json([
                'message' => 'User dengan username tersebut sudah ada di database.',
            ], 422);
        }

        try {
            $ldapUser = LdapUser::where('uid', $request->username)->first();

            if (! $ldapUser) {
                return response()->json([
                    'message' => 'Username tidak ditemukan di LDAP. Pastikan username sudah terdaftar di sistem LDAP.',
                ], 404);
            }

            return response()->json([
                'message' => 'User LDAP berhasil diverifikasi. Silakan pilih role.',
                'user' => [
                    'name' => $ldapUser->cn[0] ?? $ldapUser->displayname[0] ?? $ldapUser->uid[0] ?? $request->username,
                    'username' => $request->username,
                    'email' => $ldapUser->mail[0] ?? $request->username.'@tongtji.com',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('LDAP User Verification Error: '.$e->getMessage(), [
                'username' => $request->username,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghubungi LDAP server. Silakan coba lagi atau hubungi administrator.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where('is_active', true),
            ],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', Rule::exists(Company::class, 'id')],
        ]);

        try {
            // Query LDAP untuk mencari user berdasarkan username (sesuai format: uid=username)
            $ldapUser = LdapUser::where('uid', $request->username)->first();

            if (! $ldapUser) {
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
                'email' => $ldapUser->mail[0] ?? $request->username.'@tongtji.com',
                'password' => Hash::make(str()->random(32)), // Random password since we use LDAP auth
                'domain' => $ldapUser->getDn(),
                'guid' => $ldapUser->entryuuid[0] ?? null,
            ]);

            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);
            $user->companies()->sync($request->input('company_ids', []));

            UserLog::log(
                'CREATE_USER',
                "Created user from LDAP sync: {$user->name} ({$user->username})",
                $user,
                null,
                [
                    'username' => $request->username,
                    'ldap_dn' => $ldapUser->getDn(),
                    'ldap_guid' => $ldapUser->entryuuid[0] ?? null,
                    'role' => $role->name,
                ],
                $user
            );

            return redirect()->route('users.index')
                ->with('success', "User berhasil ditemukan di LDAP, disinkronisasi, dan diberi role {$role->display_name}. User: {$user->name} ({$user->username})");
        } catch (\Exception $e) {
            Log::error('LDAP User Sync Error: '.$e->getMessage(), [
                'username' => $request->username,
                'exception' => $e,
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
        $user->load(['companies', 'creator', 'updater', 'deleter', 'roles']);

        UserLog::log(
            'VIEW_USER',
            "Viewed user: {$user->name} ({$user->username})",
            $user,
            null,
            null,
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
        $companies = Company::query()->orderBy('name')->get(['id', 'name', 'code']);
        $user->load('companies');

        return view('users.edit', compact('companies', 'user', 'permissions', 'roleTemplates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', Rule::exists(Company::class, 'id')],
        ]);

        $user->companies()->sync($validated['company_ids'] ?? []);

        return redirect()->route('users.edit', $user)->with('success', 'User berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }

    /**
     * Restore soft deleted user.
     */
    public function restore(User $user)
    {
        $user->deleted_by = null;
        $user->restore();

        return redirect()->route('users.trash')->with('success', 'User berhasil dipulihkan');
    }

    /**
     * Permanently delete user.
     */
    public function forceDelete(User $user)
    {
        $user->forceDelete();

        return redirect()->route('users.trash')->with('success', 'User berhasil dihapus permanen');
    }

    /**
     * Get data
     */
    public function getData(Request $request)
    {
        $users = User::with(['companies', 'roles'])->select(['id', 'name', 'email', 'username']);

        return datatables()->of($users)
            ->addIndexColumn()
            ->addColumn('roles', function ($user) {
                return $user->roles->pluck('display_name')->join(', ') ?: '-';
            })
            ->addColumn('companies', function ($user) {
                return $user->companies->pluck('name')->join(', ') ?: '-';
            })
            ->addColumn('row_url', function ($user) {
                return route('users.show', $user);
            })
            ->addColumn('action', function ($user) {
                return view('datatable.actions.users', compact('user'))->render();
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
                return view('datatable.actions.users-trash', compact('user'))->render();
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
                    'MODEL_CREATED' => 'success',
                    'MODEL_UPDATED' => 'warning',
                    'MODEL_DELETED' => 'danger',
                    'MODEL_RESTORED' => 'info',
                    'MODEL_FORCE_DELETED' => 'dark',
                ];
                $badgeClass = $badges[$log->action] ?? 'primary';

                return '<span class="badge bg-'.$badgeClass.'">'.str_replace('_', ' ', $log->action).'</span>';
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
                    'MODEL_CREATED' => 'success',
                    'MODEL_UPDATED' => 'warning',
                    'MODEL_DELETED' => 'danger',
                    'MODEL_RESTORED' => 'info',
                    'MODEL_FORCE_DELETED' => 'dark',
                ];
                $badgeClass = $badges[$log->action] ?? 'primary';

                return '<span class="badge bg-'.$badgeClass.'">'.str_replace('_', ' ', $log->action).'</span>';
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
            'permissions.*' => 'exists:permissions,id',
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
            $dynamicRoleName = 'user_'.$user->id.'_permissions';
            $dynamicRole = Role::updateOrCreate(
                ['name' => $dynamicRoleName],
                [
                    'display_name' => 'Custom Permissions for '.$user->name,
                    'description' => 'Dynamic role with custom permissions for user '.$user->username,
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
            [
                'previous_permissions' => $currentPermissions,
            ],
            [
                'new_permissions' => $newPermissions,
                'permissions_count' => count($newPermissions),
            ],
            $user
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
            'role_template' => 'required|exists:roles,id',
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
            [
                'template_name' => $roleTemplate->display_name,
                'template_id' => $roleTemplate->id,
                'previous_permissions' => $currentPermissions,
                'new_permissions' => $newPermissions,
            ],
            [
                'template_name' => $roleTemplate->display_name,
                'template_id' => $roleTemplate->id,
                'new_permissions' => $newPermissions,
            ],
            $user
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
            'permission_ids' => $permissionIds,
        ]);
    }
}
