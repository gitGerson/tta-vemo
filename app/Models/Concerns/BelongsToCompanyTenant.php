<?php

namespace App\Models\Concerns;

use App\Models\Scopes\CompanyTenantScope;
use Closure;
use Illuminate\Database\Eloquent\Model;

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

            $companyIds = static::resolveTenantCompanyIdsForValidation($companyField);

            if ($companyIds === []) {
                $fail('Data yang dipilih tidak valid untuk company tenant.');

                return;
            }

            $model = new static;

            $query = static::query()
                ->withoutGlobalScopes()
                ->whereKey($value);

            if (count($companyIds) === 1) {
                $query->where($model->getTenantScopeColumn(), $companyIds[0]);
            } else {
                $query->whereIn($model->getTenantScopeColumn(), $companyIds);
            }

            $exists = $query->exists();

            if (! $exists) {
                $fail('Data yang dipilih harus berasal dari company tenant yang sesuai.');
            }
        };
    }

    protected static function resolveTenantCompanyIdsForValidation(string $companyField): array
    {
        $user = auth()->user();

        if ($user !== null && ! static::isTenantBypassed($user)) {
            return method_exists($user, 'tenantCompanyIds')
                ? $user->tenantCompanyIds()->all()
                : array_values(array_filter([(int) $user->company_id]));
        }

        $companyId = request()->input($companyField);

        if ($companyId === null || $companyId === '') {
            return [];
        }

        return [(int) $companyId];
    }

    protected function enforceTenantCompanyAssignment(): void
    {
        if (! auth()->check() || static::isTenantBypassed()) {
            return;
        }

        $assignmentColumn = $this->getTenantAssignmentColumn();
        $user = auth()->user();
        $companyIds = method_exists($user, 'tenantCompanyIds')
            ? $user->tenantCompanyIds()
            : collect(array_filter([(int) $user?->company_id]));

        if ($assignmentColumn === null || $companyIds->isEmpty()) {
            return;
        }

        $currentCompanyId = $this->getAttribute($assignmentColumn);

        if ($currentCompanyId !== null && $currentCompanyId !== '' && $companyIds->contains((int) $currentCompanyId)) {
            return;
        }

        $fallbackCompanyId = $companyIds->contains((int) $user?->company_id)
            ? (int) $user->company_id
            : (int) $companyIds->first();

        $this->setAttribute($assignmentColumn, $fallbackCompanyId);
    }
}
