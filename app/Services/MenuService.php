<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Collection;

class MenuService
{
    public function __construct(
        protected UrlGenerator $urlGenerator
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsForContext(string $context, ?User $user = null): array
    {
        return collect(config('menu.items', []))
            ->values()
            ->map(fn (array $item, int $index): array => $item + ['_index' => $index])
            ->filter(fn (array $item): bool => $this->isVisible($item, $context, $user))
            ->sortBy(fn (array $item): array => [
                $this->sectionOrder($item['section'] ?? null),
                $item['order'] ?? $item['_index'],
            ])
            ->map(fn (array $item): array => $this->resolveItem($item))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sectionsForContext(string $context, ?User $user = null): array
    {
        $items = $this->itemsForContext($context, $user);
        $sections = config('menu.sections', []);

        return collect($items)
            ->groupBy(fn (array $item): string => (string) ($item['section'] ?? 'general'))
            ->map(function ($sectionItems, string $sectionKey) use ($sections): array {
                $sectionConfig = $sections[$sectionKey] ?? [];

                return [
                    'key' => $sectionKey,
                    'label' => $sectionConfig['sidebar_label'] ?? $sectionConfig['label'] ?? null,
                    'order' => $sectionConfig['order'] ?? 999,
                    'items' => $sectionItems->values()->all(),
                    'nodes' => $this->buildSectionNodes($sectionItems),
                ];
            })
            ->sortBy('order')
            ->values()
            ->all();
    }

    /**
     * Build the sidebar render list for one section: items carrying a `group`
     * key (matching config('menu.groups')) collapse into a single submenu node
     * at the position of their first child; everything else stays a flat item.
     * Search uses itemsForContext() and is unaffected by this nesting.
     *
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function buildSectionNodes($items): array
    {
        $groups = config('menu.groups', []);
        $nodes = [];
        $seen = [];

        foreach ($items as $item) {
            $groupKey = $item['group'] ?? null;

            if ($groupKey !== null && isset($groups[$groupKey])) {
                if (isset($seen[$groupKey])) {
                    continue;
                }
                $seen[$groupKey] = true;

                $children = collect($items)
                    ->filter(fn (array $i): bool => ($i['group'] ?? null) === $groupKey)
                    ->values()
                    ->all();

                $nodes[] = [
                    'type' => 'group',
                    'key' => $groupKey,
                    'label' => $groups[$groupKey]['label'] ?? $groupKey,
                    'icon' => $groups[$groupKey]['icon'] ?? null,
                    'is_active' => collect($children)->contains(fn (array $i): bool => $i['is_active'] ?? false),
                    'items' => $children,
                ];

                continue;
            }

            $nodes[] = ['type' => 'item'] + $item;
        }

        return $nodes;
    }

    protected function isVisible(array $item, string $context, ?User $user): bool
    {
        if (($item['enabled'] ?? true) !== true) {
            return false;
        }

        $contexts = $item['contexts'] ?? [];
        if (! in_array($context, $contexts, true)) {
            return false;
        }

        $permission = $item['permission'] ?? null;

        if ($permission === null) {
            return true;
        }

        return $user?->hasPermission($permission) === true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveItem(array $item): array
    {
        return [
            'key' => $item['key'],
            'title' => $item['label'],
            'sidebar_label' => $item['sidebar_label'] ?? $item['label'],
            'description' => $item['search']['description'] ?? $item['label'],
            'url' => $this->urlGenerator->route($item['route'], $item['route_params'] ?? []),
            'icon' => $item['icon'],
            'keywords' => array_values($item['search']['keywords'] ?? []),
            'permission' => $item['permission'] ?? null,
            'section' => $item['section'] ?? null,
            'group' => $item['group'] ?? null,
            'active_patterns' => $item['active_patterns'] ?? [],
            'is_active' => $this->isActive($item['active_patterns'] ?? []),
        ];
    }

    protected function sectionOrder(?string $sectionKey): int
    {
        if ($sectionKey === null) {
            return 999;
        }

        return config("menu.sections.{$sectionKey}.order", 999);
    }

    /**
     * @param  array<int, string>  $activePatterns
     */
    protected function isActive(array $activePatterns): bool
    {
        foreach ($activePatterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
