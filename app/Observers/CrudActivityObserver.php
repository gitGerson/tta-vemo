<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrudActivityObserver
{
    public function created(Model $model): void
    {
        $this->log('CREATE', $model, null, $this->sanitize($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        if (empty($changes)) {
            return;
        }

        $original = $model->getOriginal();
        $oldValues = array_intersect_key($original, $changes);

        $this->log('UPDATE', $model, $this->sanitize($oldValues), $this->sanitize($changes));
    }

    public function deleted(Model $model): void
    {
        $event = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
            ? 'FORCE_DELETE'
            : 'DELETE';

        $this->log($event, $model, $this->sanitize($model->getAttributes()), null);
    }

    public function restored(Model $model): void
    {
        $this->log('RESTORE', $model, null, $this->sanitize($model->getAttributes()));
    }

    private function log(string $event, Model $model, ?array $oldValues, ?array $newValues): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        $action = $event . '_' . strtoupper(Str::snake(class_basename($model)));
        $label = $this->getModelLabel($model);
        $description = trim(sprintf(
            '%s %s%s',
            Str::title(str_replace('_', ' ', strtolower($event))),
            class_basename($model),
            $label ? ': ' . $label : ''
        ));

        $targetUser = $model instanceof User ? $model : null;

        UserLog::log($action, $description, $targetUser, $oldValues, $newValues);
    }

    private function shouldLog(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        if (!auth()->check()) {
            return false;
        }

        return true;
    }

    private function getModelLabel(Model $model): ?string
    {
        $candidates = ['name', 'title', 'code_mt', 'no_pol', 'plat_new', 'plat_old', 'email', 'username', 'id'];
        foreach ($candidates as $field) {
            if (!is_null($model->getAttribute($field))) {
                return (string) $model->getAttribute($field);
            }
        }

        return null;
    }

    private function sanitize(?array $values): ?array
    {
        if (!$values) {
            return $values;
        }

        unset($values['password'], $values['updated_at']);

        return $values;
    }
}
