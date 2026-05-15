<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use Auditable, HasDiffrntHashId;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'guard_name',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
                    ->withPivot('created_by')
                    ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')
                    ->withPivot(['assigned_by', 'assigned_at'])
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('permissions.name', $permission)->exists();
        }

        if (is_object($permission)) {
            return $this->permissions()->where('permissions.id', $permission->id)->exists();
        }

        return false;
    }

    public function assignPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            // Check if permission is already assigned
            $exists = $this->permissions()->where('permissions.id', $permission->id)->exists();
            
            if (!$exists) {
                $this->permissions()->attach($permission->id, [
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $this;
    }

    public function removePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }
}
