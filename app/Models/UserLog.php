<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserLog extends Model
{
    protected $fillable = [
        'user_id',
        'target_user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log($action, $description, $targetUser = null, $oldValues = null, $newValues = null, $auditable = null)
    {
        return self::create([
            'user_id' => auth()->id(),
            'target_user_id' => $targetUser ? $targetUser->id : null,
            'auditable_type' => $auditable ? $auditable->getMorphClass() : null,
            'auditable_id' => $auditable?->getKey(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logModelEvent(Model $auditable, string $action, ?array $oldValues = null, ?array $newValues = null): self
    {
        $targetUser = $auditable instanceof User ? $auditable : null;

        return self::create([
            'user_id' => auth()->id(),
            'target_user_id' => $targetUser?->getKey(),
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'action' => $action,
            'description' => self::buildModelDescription($auditable, $action),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d M Y H:i:s');
    }

    public function getChangesTextAttribute()
    {
        if (!$this->old_values && !$this->new_values) {
            return null;
        }

        $changes = [];
        
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue && $key !== 'password' && $key !== 'updated_at') {
                    $changes[] = "{$key}: '{$oldValue}' → '{$newValue}'";
                }
            }
        }

        return implode(', ', $changes);
    }

    protected static function buildModelDescription(Model $auditable, string $action): string
    {
        $modelName = class_basename($auditable);
        $label = $auditable->getAttribute('name')
            ?? $auditable->getAttribute('title')
            ?? $auditable->getAttribute('display_name')
            ?? $auditable->getKey();

        return "{$action} {$modelName}: {$label}";
    }
}
