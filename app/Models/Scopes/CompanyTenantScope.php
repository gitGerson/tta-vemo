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

        $companyIds = method_exists($user, 'tenantCompanyIds')
            ? $user->tenantCompanyIds()
            : collect(array_filter([(int) $user->company_id]));
        $tenantColumn = method_exists($model, 'getTenantScopeColumn')
            ? $model->getTenantScopeColumn()
            : 'company_id';

        if ($companyIds->isEmpty()) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $qualifiedColumn = $model->qualifyColumn($tenantColumn);

        if ($companyIds->count() === 1) {
            $builder->where($qualifiedColumn, $companyIds->first());

            return;
        }

        $builder->whereIn($qualifiedColumn, $companyIds->all());
    }
}
