<?php

namespace App\Models\Concerns;

use App\Models\UserLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

trait Auditable
{
    protected array $auditableDirtySnapshot = [];

    protected array $auditableDeleteSnapshot = [];

    protected static array $auditableColumnCache = [];

    public static function bootAuditable(): void
    {
        static::creating(function (Model $model): void {
            if ($model->hasAuditableColumn('created_by') && auth()->check()) {
                $model->setAttribute('created_by', auth()->id());
            }
        });

        static::updating(function (Model $model): void {
            if ($model->hasAuditableColumn('updated_by') && auth()->check()) {
                $model->setAttribute('updated_by', auth()->id());
            }

            $model->auditableDirtySnapshot = $model->captureAuditableAttributes(array_keys($model->getDirty()));
        });

        static::deleting(function (Model $model): void {
            $model->auditableDeleteSnapshot = $model->captureAuditableAttributes();

            if (
                $model->hasAuditableColumn('deleted_by') &&
                auth()->check() &&
                (! method_exists($model, 'isForceDeleting') || ! $model->isForceDeleting())
            ) {
                $model->setAttribute('deleted_by', auth()->id());
            }
        });

        static::created(function (Model $model): void {
            UserLog::logModelEvent(
                auditable: $model,
                action: 'MODEL_CREATED',
                oldValues: null,
                newValues: $model->captureAuditableAttributes()
            );
        });

        static::updated(function (Model $model): void {
            $newValues = $model->captureAuditableAttributes(array_keys($model->getChanges()));

            if ($newValues === []) {
                return;
            }

            UserLog::logModelEvent(
                auditable: $model,
                action: 'MODEL_UPDATED',
                oldValues: $model->auditableDirtySnapshot,
                newValues: $newValues
            );
        });

        static::deleted(function (Model $model): void {
            $action = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                ? 'MODEL_FORCE_DELETED'
                : 'MODEL_DELETED';

            UserLog::logModelEvent(
                auditable: $model,
                action: $action,
                oldValues: $model->auditableDeleteSnapshot,
                newValues: null
            );
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function (Model $model): void {
                UserLog::logModelEvent(
                    auditable: $model,
                    action: 'MODEL_RESTORED',
                    oldValues: null,
                    newValues: $model->captureAuditableAttributes()
                );
            });
        }
    }

    /**
     * @param  array<int, string>|null  $keys
     * @return array<string, mixed>
     */
    protected function captureAuditableAttributes(?array $keys = null): array
    {
        $attributes = $keys === null
            ? $this->getAttributes()
            : collect($keys)
                ->filter(fn (string $key): bool => array_key_exists($key, $this->getAttributes()))
                ->mapWithKeys(fn (string $key): array => [$key => $this->getAttribute($key)])
                ->all();

        return collect($attributes)
            ->except($this->auditableExcludedAttributes())
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function auditableExcludedAttributes(): array
    {
        return [
            'password',
            'remember_token',
            'created_at',
            'updated_at',
            'deleted_at',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }

    protected function hasAuditableColumn(string $column): bool
    {
        $cacheKey = static::class.':'.$column;

        if (! array_key_exists($cacheKey, static::$auditableColumnCache)) {
            static::$auditableColumnCache[$cacheKey] = Schema::hasColumn($this->getTable(), $column);
        }

        return static::$auditableColumnCache[$cacheKey];
    }
}
