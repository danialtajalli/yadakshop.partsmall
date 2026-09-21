<?php

namespace App\Providers;

use App\Services\PageService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->configureRateLimiting();

        View::composer(
            ['layouts.partials.header', 'layouts.partials.footer'],
            function ($view): void {
                $view->with('navigationPages', app(PageService::class)->getNavigationPages());
            },
        );
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('heavy', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
