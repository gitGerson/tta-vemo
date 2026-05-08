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

            $companyId = static::resolveTenantCompanyIdForValidation($companyField);

            if ($companyId === null) {
                $fail('Data yang dipilih tidak valid untuk company aktif.');

                return;
            }

            $model = new static;

            $exists = static::query()
                ->withoutGlobalScopes()
                ->whereKey($value)
                ->where($model->getTenantScopeColumn(), $companyId)
                ->exists();

            if (! $exists) {
                $fail('Data yang dipilih harus berasal dari company yang sesuai.');
            }
        };
    }

    protected static function resolveTenantCompanyIdForValidation(string $companyField): ?int
    {
        $user = auth()->user();

        if ($user !== null && ! static::isTenantBypassed($user)) {
            return $user->company_id !== null ? (int) $user->company_id : null;
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
        $companyId = auth()->user()?->company_id;

        if ($assignmentColumn === null || $companyId === null) {
            return;
        }

        $this->setAttribute($assignmentColumn, $companyId);
    }
}
