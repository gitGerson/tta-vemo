<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Tenant scope for models that relate to companies through a many-to-many pivot
 * (e.g. Driver via company_driver) rather than a local company_id column.
 */
class CompanyTenantPivotScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->hasUser()) {
            return;
        }

        $user = auth()->user();

        if ($user === null || (method_exists($model, 'isTenantBypassed') && $model::isTenantBypassed($user))) {
            return;
        }

        $companyIds = method_exists($model, 'resolveTenantCompanyIds')
            ? $model::resolveTenantCompanyIds($user)
            : [];

        if ($companyIds === []) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $relation = method_exists($model, 'getTenantPivotRelation')
            ? $model->getTenantPivotRelation()
            : 'companies';

        $builder->whereHas($relation, function (Builder $query) use ($companyIds): void {
            $query->whereIn($query->getModel()->qualifyColumn($query->getModel()->getKeyName()), $companyIds);
        });
    }
}
