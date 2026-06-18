<?php

namespace App\Models\Concerns;

use App\Models\Scopes\CompanyTenantPivotScope;
use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Tenant scoping for models linked to companies through a many-to-many pivot.
 *
 * Unlike BelongsToCompanyTenant there is no local company_id to assign on create;
 * company membership is managed via the pivot relation (synced by the controller).
 */
trait BelongsToCompanyTenantThroughPivot
{
    public static function bootBelongsToCompanyTenantThroughPivot(): void
    {
        static::addGlobalScope(new CompanyTenantPivotScope);
    }

    public function getTenantPivotRelation(): string
    {
        return 'companies';
    }

    public static function isTenantBypassed($user = null): bool
    {
        $user ??= auth()->user();

        return $user?->hasPermission('management.tenant.bypass') === true;
    }

    /**
     * Validation rule ensuring a submitted company id is within the user's tenant.
     */
    public static function belongsToTenantCompanyRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            if (static::isTenantBypassed()) {
                return;
            }

            $allowedCompanyIds = static::resolveTenantCompanyIds();

            if (! in_array((int) $value, $allowedCompanyIds, true)) {
                $fail('The selected company is not available for the active user.');
            }
        };
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
