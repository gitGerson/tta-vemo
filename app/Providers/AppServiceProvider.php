<?php

namespace App\Providers;

use App\Services\BreadcrumbService;
use App\Services\DiffrntHashId;
use App\Services\JPayrollService;
use App\Services\MenuService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DiffrntHashId::class);

        $this->app->singleton(JPayrollService::class, fn () => new JPayrollService(
            url: config('services.jpayroll.url', ''),
            token: config('services.jpayroll.token', ''),
        ));
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
