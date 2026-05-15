<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BreadcrumbService
{
    /**
     * @var array<string, string|null>
     */
    protected array $actionLabels = [
        'index' => null,
        'show' => 'Detail',
        'edit' => 'Edit',
        'create' => 'Create',
        'trash' => 'Trash',
        'logs' => 'Activity Logs',
        'user-logs' => 'User Activity Logs',
    ];

    public function __construct(
        protected UrlGenerator $urlGenerator
    ) {
    }

    /**
     * @return array<int, array{label: string, url: string|null, active: bool}>
     */
    public function forCurrentRoute(?User $user = null): array
    {
        $routeName = request()->route()?->getName();

        if ($routeName === null || $routeName === 'home') {
            return [];
        }

        $baseRoute = Str::before($routeName, '.');
        $action = Str::afterLast($routeName, '.');
        $parent = $this->parentItem($routeName, $baseRoute, $user);
        $breadcrumbs = [];

        if ($parent !== null) {
            $breadcrumbs[] = [
                'label' => $parent['label'],
                'url' => $action === 'index' ? null : $this->routeUrl($parent['route'], $parent['route_params'] ?? []),
                'active' => $action === 'index',
            ];
        } else {
            $breadcrumbs[] = [
                'label' => Str::headline($baseRoute),
                'url' => null,
                'active' => $action === 'index',
            ];
        }

        if ($action !== 'index') {
            $breadcrumbs[] = [
                'label' => $this->currentLabel($routeName, $action, $user),
                'url' => null,
                'active' => true,
            ];
        }

        return $breadcrumbs;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function parentItem(string $routeName, string $baseRoute, ?User $user): ?array
    {
        $items = $this->visibleMenuItems($user);
        $indexRoute = $baseRoute.'.index';

        $exactIndex = $items->first(fn (array $item): bool => ($item['route'] ?? null) === $indexRoute);

        if ($exactIndex !== null) {
            return $exactIndex;
        }

        return $items->first(function (array $item) use ($routeName): bool {
            foreach ($item['active_patterns'] ?? [] as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return true;
                }
            }

            return false;
        });
    }

    protected function currentLabel(string $routeName, string $action, ?User $user): string
    {
        if (array_key_exists($action, $this->actionLabels) && $this->actionLabels[$action] !== null) {
            return $this->actionLabels[$action];
        }

        $exactItem = $this->visibleMenuItems($user)
            ->first(fn (array $item): bool => ($item['route'] ?? null) === $routeName);

        if ($exactItem !== null && ! str_ends_with($routeName, '.index')) {
            return $exactItem['label'];
        }

        return Str::headline($action);
    }

    protected function routeUrl(string $route, array $params = []): ?string
    {
        if (! Route::has($route)) {
            return null;
        }

        return $this->urlGenerator->route($route, $params);
    }

    protected function visibleMenuItems(?User $user)
    {
        return collect(config('menu.items', []))
            ->filter(fn (array $item): bool => $this->isVisible($item, $user))
            ->values();
    }

    protected function isVisible(array $item, ?User $user): bool
    {
        if (($item['enabled'] ?? true) !== true) {
            return false;
        }

        $permission = $item['permission'] ?? null;

        if ($permission === null) {
            return true;
        }

        return $user?->hasPermission($permission) === true;
    }
}
