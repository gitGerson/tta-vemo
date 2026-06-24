<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Auto-generates a unique, human-readable `code` on create when one is not supplied.
 * Codes follow `{PREFIX}-{NNNN}` with a zero-padded running number scoped to the model,
 * so users no longer enter codes by hand.
 *
 * Implementing models must define {@see codePrefix()}.
 */
trait GeneratesCode
{
    public static function bootGeneratesCode(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->code)) {
                $model->code = $model->generateCode();
            }
        });
    }

    /**
     * Build the next code for this model: the highest existing running number for the
     * prefix, plus one. Computed in PHP so it stays portable across MySQL and SQLite.
     */
    public function generateCode(): string
    {
        $prefix = $this->codePrefix();

        // Strip global scopes so the running number is computed across every row: the DB
        // unique index spans soft-deleted rows (a trashed code still occupies its number)
        // and tenant scopes would otherwise hide rows and reissue a colliding number.
        $highest = static::query()
            ->withoutGlobalScopes()
            ->where('code', 'like', $prefix.'-%')
            ->pluck('code')
            ->map(fn (string $code): int => (int) Str::afterLast($code, '-'))
            ->max() ?? 0;

        return sprintf('%s-%04d', $prefix, $highest + 1);
    }

    abstract protected function codePrefix(): string;
}
