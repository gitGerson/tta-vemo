<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index');
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('module');
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'guard_name' => 'web'
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        UserLog::log(
            'CREATE_ROLE',
            "Created role: {$role->display_name} ({$role->name})",
            null,
            null,
            $request->only(['name', 'display_name', 'description'])
        );

        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $oldValues = $role->only(['name', 'display_name', 'description']);
        $oldPermissions = $role->permissions->pluck('name')->toArray();

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);
        $newPermissions = $role->fresh()->permissions->pluck('name')->toArray();

        UserLog::log(
            'UPDATE_ROLE',
            "Updated role: {$role->display_name} ({$role->name})",
            null,
            array_merge($oldValues, ['permissions' => $oldPermissions]),
            array_merge($request->only(['name', 'display_name', 'description']), ['permissions' => $newPermissions])
        );

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        // Check if role is in use
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user');
        }

        $roleName = $role->display_name;
        $roleCode = $role->name;
        
        UserLog::log(
            'DELETE_ROLE',
            "Deleted role: {$roleName} ({$roleCode})",
            null,
            $role->only(['name', 'display_name', 'description'])
        );

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus');
    }

    public function getData(Request $request)
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->select(['id', 'name', 'display_name', 'description', 'created_at']);

        return datatables()->of($roles)
            ->addColumn('users_count', function ($role) {
                return $role->users_count;
            })
            ->addColumn('permissions_count', function ($role) {
                return $role->permissions_count;
            })
            ->addColumn('action', function ($role) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                // View action
                if (auth()->user()->hasPermission('roles.view')) {
                    $actions .= '<a class="dropdown-item" href="' . route('roles.show', $role->id) . '">
                        <i class="bx bx-show me-1"></i> Lihat
                    </a>';
                }

                // Edit action
                if (auth()->user()->hasPermission('roles.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('roles.edit', $role->id) . '">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>';
                }

                // Delete action (only if role is not in use)
                if (auth()->user()->hasPermission('roles.delete') && $role->users_count == 0) {
                    $actions .= '<form action="' . route('roles.destroy', $role->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus role ini?\')">
                            <i class="bx bx-trash me-1"></i> Hapus
                        </button>
                    </form>';
                } elseif ($role->users_count > 0) {
                    $actions .= '<span class="dropdown-item text-muted">
                        <i class="bx bx-trash me-1"></i> Tidak dapat dihapus (sedang digunakan)
                    </span>';
                }

                $actions .= '</div></div>';
                
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}