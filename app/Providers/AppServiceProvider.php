<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\City;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Image;
use App\Models\ModelCategory;
use App\Models\Page;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\RepairCategory;
use App\Models\RepairShop;
use App\Models\Representation;
use App\Models\Shop;
use App\Models\State;
use App\Observers\ContentCacheObserver;
use App\Services\PageService;
use App\Support\ContentCacheInvalidator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var list<class-string<Model>>
     */
    private const CONTENT_CACHE_MODELS = [
        Company::class,
        Car::class,
        CarModel::class,
        ModelCategory::class,
        Shop::class,
        RepairShop::class,
        Representation::class,
        Part::class,
        PartsCategory::class,
        State::class,
        City::class,
        RepairCategory::class,
        Page::class,
        Comment::class,
        Image::class,
    ];

    /**
     * Models that sync BelongsToMany relations from the admin panel.
     *
     * @var list<class-string<Model>>
     */
    private const CONTENT_CACHE_PIVOT_MODELS = [
        Company::class,
        Car::class,
        CarModel::class,
        Shop::class,
        RepairShop::class,
        Part::class,
        PartsCategory::class,
        RepairCategory::class,
    ];

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
        $this->configureContentCacheInvalidation();

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

    private function configureContentCacheInvalidation(): void
    {
        $observer = $this->app->make(ContentCacheObserver::class);

        foreach (self::CONTENT_CACHE_MODELS as $modelClass) {
            $modelClass::observe($observer);
        }

        foreach (self::CONTENT_CACHE_PIVOT_MODELS as $modelClass) {
            foreach (['pivotAttached', 'pivotDetached', 'pivotUpdated'] as $event) {
                $modelClass::registerModelEvent(
                    $event,
                    fn (Model $model): mixed => ContentCacheInvalidator::forModel($model),
                );
            }
        }
    }
}
