<?php

namespace App\Providers;

use App\Services\MenuService;
use App\Services\DiffrntHashId;
use App\Services\BreadcrumbService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DiffrntHashId::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->hasDebugModeEnabled()) {
            Blade::anonymousComponentNamespace(
                'laravel-exceptions-renderer::components',
                'laravel-exceptions-renderer'
            );
        }

        View::composer('layouts.sneat', function ($view): void {
            $menuService = app(MenuService::class);
            $breadcrumbService = app(BreadcrumbService::class);
            $user = auth()->user();

            $view->with(
                'searchMenuItems',
                $menuService->itemsForContext('search', $user)
            );

            $view->with(
                'sidebarMenuSections',
                $menuService->sectionsForContext('desktop', $user)
            );

            $view->with(
                'breadcrumbs',
                $breadcrumbService->forCurrentRoute($user)
            );
        });
    }
}
