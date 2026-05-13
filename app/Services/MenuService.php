<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Routing\UrlGenerator;

class MenuService
{
    public function __construct(
        protected UrlGenerator $urlGenerator
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsForContext(string $context, ?User $user = null): array
    {
        return collect(config('menu.items', []))
            ->values()
            ->map(fn (array $item, int $index): array => $item + ['_index' => $index])
            ->filter(fn (array $item): bool => $this->isVisible($item, $context, $user))
            ->sortBy([
                fn (array $item): int => $this->sectionOrder($item['section'] ?? null),
                fn (array $item): int => $item['order'] ?? $item['_index'],
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
                ];
            })
            ->sortBy('order')
            ->values()
            ->all();
    }

    protected function isVisible(array $item, string $context, ?User $user): bool
    {
        if (($item['enabled'] ?? true) !== true) {
            return false;
        }

        $contexts = $item['contexts'] ?? [];
        if (!in_array($context, $contexts, true)) {
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
            'description' => $item['search']['description'] ?? $item['label'],
            'url' => $this->urlGenerator->route($item['route'], $item['route_params'] ?? []),
            'icon' => $item['icon'],
            'keywords' => array_values($item['search']['keywords'] ?? []),
            'permission' => $item['permission'] ?? null,
            'section' => $item['section'] ?? null,
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
