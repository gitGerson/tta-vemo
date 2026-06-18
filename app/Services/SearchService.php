<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SearchService
{
    /**
     * Search across the registered record models the user may view.
     *
     * Tenant scoping and soft-delete exclusion are applied automatically by the
     * models' global scopes, so a plain query() is used (no withoutGlobalScopes).
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, ?User $user): array
    {
        $query = trim($query);
        $config = config('search');

        if ($user === null || mb_strlen($query) < ($config['min_query_length'] ?? 2)) {
            return [];
        }

        $escaped = addcslashes($query, '%_\\'); // neutralize LIKE wildcards
        $perModelLimit = $config['per_model_limit'] ?? 5;
        $totalLimit = $config['total_limit'] ?? 30;

        $results = [];
        $total = 0;

        foreach ($config['models'] ?? [] as $groupKey => $def) {
            if ($total >= $totalLimit) {
                break;
            }

            $permission = $def['permission'] ?? null;
            if ($permission !== null && $user->hasPermission($permission) !== true) {
                continue;
            }

            $rows = ($def['model'])::query()
                ->where(function (Builder $q) use ($def, $escaped): void {
                    foreach ($def['columns'] as $column) {
                        $q->orWhere($column, 'like', '%'.$escaped.'%');
                    }
                })
                ->limit($perModelLimit)
                ->get();

            foreach ($rows as $model) {
                if ($total >= $totalLimit) {
                    break;
                }

                $results[] = [
                    'group' => $groupKey,
                    'label' => $def['label'],
                    'icon' => $def['icon'],
                    'title' => (string) ($def['title'])($model),
                    'description' => (string) ($def['description'])($model),
                    'url' => $this->resolveUrl($def['route'], $model),
                ];
                $total++;
            }
        }

        return $results;
    }

    /**
     * Build the show URL. route() calls the model's getRouteKey(), which returns
     * a hashid for models using HasDiffrntHashId and the raw id otherwise.
     */
    protected function resolveUrl(string $route, Model $model): string
    {
        return route($route, $model);
    }
}
