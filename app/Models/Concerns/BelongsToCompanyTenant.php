<?php

namespace App\Models\Concerns;

use App\Models\Scopes\CompanyTenantScope;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait BelongsToCompanyTenant
{
    public static function bootBelongsToCompanyTenant(): void
    {
        static::addGlobalScope(new CompanyTenantScope);

        static::creating(function (Model $model): void {
            $model->enforceTenantCompanyAssignment();
        });

        static::updating(function (Model $model): void {
            $model->enforceTenantCompanyAssignment();
        });
    }

    public function getTenantScopeColumn(): string
    {
        return 'company_id';
    }

    public function getTenantAssignmentColumn(): ?string
    {
        return 'company_id';
    }

    public static function isTenantBypassed($user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasPermission('management.tenant.bypass') === true;
    }

    public static function belongsToTenantCompanyRule(string $companyField = 'company_id'): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($companyField): void {
            if ($value === null || $value === '') {
                return;
            }

            if (static::isTenantBypassed()) {
                return;
            }

            $companyId = static::resolveTenantCompanyIdForValidation($companyField);

            if ($companyId === null) {
                $fail('The selected data is invalid for the active company.');

                return;
            }

            $model = new static;

            $exists = static::query()
                ->withoutGlobalScopes()
                ->whereKey($value)
                ->where($model->getTenantScopeColumn(), $companyId)
                ->exists();

            if (! $exists) {
                $fail('The selected data must belong to the matching company.');
            }
        };
    }

    protected static function resolveTenantCompanyIdForValidation(string $companyField): ?int
    {
        $user = auth()->user();

        if ($user !== null && ! static::isTenantBypassed($user)) {
            $allowedCompanyIds = static::resolveTenantCompanyIds($user);
            $selectedCompanyId = request()->input($companyField);

            if ($selectedCompanyId !== null && $selectedCompanyId !== '') {
                $selectedCompanyId = (int) $selectedCompanyId;

                return in_array($selectedCompanyId, $allowedCompanyIds, true)
                    ? $selectedCompanyId
                    : null;
            }

            return $allowedCompanyIds[0] ?? null;
        }

        $companyId = request()->input($companyField);

        if ($companyId === null || $companyId === '') {
            return null;
        }

        return (int) $companyId;
    }

    protected function enforceTenantCompanyAssignment(): void
    {
        if (! auth()->check() || static::isTenantBypassed()) {
            return;
        }

        $assignmentColumn = $this->getTenantAssignmentColumn();
        $user = auth()->user();

        if ($assignmentColumn === null || $user === null) {
            return;
        }

        $allowedCompanyIds = static::resolveTenantCompanyIds($user);

        if ($allowedCompanyIds === []) {
            return;
        }

        $currentCompanyId = $this->getAttribute($assignmentColumn);

        if ($currentCompanyId !== null && $currentCompanyId !== '') {
            $currentCompanyId = (int) $currentCompanyId;

            if (in_array($currentCompanyId, $allowedCompanyIds, true)) {
                return;
            }
        }

        $this->setAttribute($assignmentColumn, $allowedCompanyIds[0]);
    }

    /**
     * @return list<int>
     */
    public static function resolveTenantCompanyIds($user = null): array
    {
        $user ??= auth()->user();

        if ($user === null) {
            return [];
        }

        return DB::table('company_user')
            ->where('user_id', $user->getKey())
            ->pluck('company_id')
            ->map(static fn (mixed $companyId): int => (int) $companyId)
            ->filter(static fn (int $companyId): bool => $companyId > 0)
            ->unique()
            ->values()
            ->all();
    }
}
