<?php

namespace App\Providers;

use App\Services\PageService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Paginator::defaultView('vendor.pagination.tailwind');

        View::composer(
            ['layouts.partials.header', 'layouts.partials.footer'],
            function ($view): void {
                $view->with('navigationPages', app(PageService::class)->getNavigationPages());
            },
        );
    }
}
