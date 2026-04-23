<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
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

        $role->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        // Check if role is in use
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus');
    }

    public function getData(Request $request)
    {
        $roles = Role::select(['id', 'name', 'display_name', 'description', 'created_at'])
            ->withCount(['users', 'permissions']);

        return datatables()->of($roles)
            ->addColumn('users_count', function ($role) {
                return $role->users_count;
            })
            ->addColumn('permissions_count', function ($role) {
                return $role->permissions_count;
            })
            ->addColumn('action', function ($role) {
                $actions = '<div class="d-inline-flex align-items-center gap-1">';

                // View action
                if (auth()->user()->hasPermission('management.roles.view')) {
                    $actions .= '<a class="btn btn-sm btn-info" href="' . route('roles.show', $role->id) . '" title="Lihat" aria-label="Lihat">
                        <i class="bx bx-show"></i>
                    </a>';
                }

                // Edit action
                if (auth()->user()->hasPermission('management.roles.edit')) {
                    $actions .= '<a class="btn btn-sm btn-warning" href="' . route('roles.edit', $role->id) . '" title="Edit" aria-label="Edit">
                        <i class="bx bx-edit-alt"></i>
                    </a>';
                }

                // Delete action (only if role is not in use)
                if (auth()->user()->hasPermission('management.roles.delete') && $role->users_count == 0) {
                    $actions .= '<form action="' . route('roles.destroy', $role->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus" aria-label="Hapus" onclick="return confirm(\'Yakin ingin menghapus role ini?\')">
                            <i class="bx bx-trash"></i>
                        </button>
                    </form>';
                } elseif ($role->users_count > 0) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-secondary" title="Tidak dapat dihapus (sedang digunakan)" aria-label="Tidak dapat dihapus (sedang digunakan)" disabled>
                        <i class="bx bx-trash"></i>
                    </button>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
