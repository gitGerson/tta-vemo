<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyTenantScope implements Scope
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
        $tenantColumn = method_exists($model, 'getTenantScopeColumn')
            ? $model->getTenantScopeColumn()
            : 'company_id';

        if ($companyIds === []) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->whereIn($model->qualifyColumn($tenantColumn), $companyIds);
    }
}
