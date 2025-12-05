<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\UserLog;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function show(User $user)
    {
        $user->load('roles');
        $availableRoles = Role::all();
        return view('users.roles', compact('user', 'availableRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $oldRoles = $user->roles->pluck('name')->toArray();
        
        // Sync roles
        $user->roles()->sync($request->roles ?? []);
        
        $newRoles = $user->fresh()->roles->pluck('name')->toArray();

        UserLog::log(
            'UPDATE_USER_ROLES',
            "Updated roles for user: {$user->name} ({$user->username})",
            $user,
            ['roles' => $oldRoles],
            ['roles' => $newRoles]
        );

        return redirect()->route('users.roles', $user)->with('success', 'Role user berhasil diperbarui');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::find($request->role_id);
        
        if (!$user->hasRole($role)) {
            $user->assignRole($role);
            
            UserLog::log(
                'ASSIGN_USER_ROLE',
                "Assigned role '{$role->display_name}' to user: {$user->name} ({$user->username})",
                $user,
                null,
                ['role' => $role->name]
            );

            return response()->json(['success' => true, 'message' => 'Role berhasil ditambahkan']);
        }

        return response()->json(['success' => false, 'message' => 'User sudah memiliki role ini']);
    }

    public function removeRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::find($request->role_id);
        
        if ($user->hasRole($role)) {
            $user->removeRole($role);
            
            UserLog::log(
                'REMOVE_USER_ROLE',
                "Removed role '{$role->display_name}' from user: {$user->name} ({$user->username})",
                $user,
                ['role' => $role->name],
                null
            );

            return response()->json(['success' => true, 'message' => 'Role berhasil dihapus']);
        }

        return response()->json(['success' => false, 'message' => 'User tidak memiliki role ini']);
    }
}