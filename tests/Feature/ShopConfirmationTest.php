<?php

namespace Tests\Feature;

use App\Enums\ImageType;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\Shop;
use App\Services\HomePageService;
use App\Services\ProductService;
use App\Services\ShopProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Scout\EngineManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ShopConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['https://api.qrserver.com/*' => Http::response('', 503)]);
    }

    public function test_only_confirmed_shops_are_visible_by_default(): void
    {
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);

        $this->assertSame([$confirmed->id], Shop::query()->pluck('id')->all());
        $this->assertNull(Shop::find($pending->id));
        $this->assertSame(2, Shop::withoutGlobalScope('confirmed')->count());
        $this->assertSame(1, Shop::withoutGlobalScope('confirmed')->confirmed()->count());
    }

    public function test_new_shops_still_require_confirmation(): void
    {
        $shop = Shop::create(['name' => 'New shop', 'slug' => 'new-shop']);

        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'confirmed' => false]);
        $this->assertNull(Shop::find($shop->id));
        $this->assertFalse($shop->shouldBeSearchable());
    }

    public function test_relationships_and_existence_queries_only_include_confirmed_shops(): void
    {
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);
        $company = Company::create(['name' => 'Company', 'slug' => 'company']);
        $pendingCompany = Company::create(['name' => 'Pending company', 'slug' => 'pending-company']);
        $company->shops()->attach([$confirmed->id, $pending->id]);
        $pendingCompany->shops()->attach($pending);

        $this->assertSame([$confirmed->id], $company->shops()->pluck('shops.id')->all());
        $this->assertSame([$confirmed->id], $company->load('shops')->shops->pluck('id')->all());
        $this->assertSame([$company->id], Company::whereHas('shops')->pluck('id')->all());
    }

    public function test_scope_qualifies_confirmation_column_when_joining_comments(): void
    {
        $shop = $this->createShop('confirmed', true);
        $shop->comments()->create(['fullname' => 'User', 'body' => 'Pending comment', 'rating' => 5, 'confirmed' => false]);

        $ids = Shop::query()->join('comments', 'comments.shop_id', '=', 'shops.id')
            ->where('comments.confirmed', false)->pluck('shops.id')->all();

        $this->assertSame([$shop->id], $ids);
    }

    public function test_pending_shops_do_not_reset_automatic_ordering(): void
    {
        $this->createShop('pending', false)->update(['order' => 100]);
        $shop = $this->createShop('confirmed', true);

        $this->assertSame(101, $shop->order);
    }

    public function test_pending_shop_profiles_and_comment_endpoints_return_not_found(): void
    {
        $pending = $this->createShop('pending', false);

        $this->get(route('shop.profile', $pending->slug))->assertNotFound();
        $payload = ['fullname' => 'Test user', 'mobile' => '09121234567', 'body' => 'A valid comment', 'rating' => 5];
        $this->post(route('shop.comments.store', $pending->slug), $payload)->assertNotFound();
        $this->postJson(route('api.shop.comments.store', $pending->slug), $payload)->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_directory_excludes_pending_shops(): void
    {
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);

        $this->get(route('shops.index'))->assertOk()
            ->assertViewHas('listings', fn ($listings): bool => $listings->pluck('id')->all() === [$confirmed->id])
            ->assertDontSee(route('shop.profile', $pending->slug), false);
    }

    public function test_home_featured_and_best_shop_lists_exclude_pending_shops(): void
    {
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);
        config(['partsmall.home_best_shop_ids' => [$pending->id, $confirmed->id]]);

        $data = app(HomePageService::class)->getHomePageData();

        $this->assertSame([$confirmed->slug], $data['shops']->pluck('slug')->all());
        $this->assertSame([$confirmed->slug], $data['bestShops']->pluck('slug')->all());
    }

    public function test_related_shop_list_excludes_pending_shops(): void
    {
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);
        $company = Company::create(['name' => 'Company', 'slug' => 'company']);
        $company->shops()->attach([$confirmed->id, $pending->id]);

        $data = app(ShopProfileService::class)->getProfilePageData($confirmed->slug);

        $this->assertTrue($data['relatedShops']->isEmpty());
    }

    public function test_search_results_exclude_pending_shops(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);

        $this->get(route('search.index', ['q' => 'Shop']))->assertOk()
            ->assertSee(route('shop.profile', $confirmed->slug), false)
            ->assertDontSee(route('shop.profile', $pending->slug), false);
        $this->assertTrue($confirmed->shouldBeSearchable());
        $this->assertFalse($pending->shouldBeSearchable());
        $this->assertSame([$confirmed->id], (new Shop)->getScoutModelsByIds(Shop::search('Shop'), [$confirmed->id, $pending->id])->pluck('id')->all());
    }

    #[DataProvider('productCompanies')]
    public function test_product_lists_exclude_pending_shops_including_curated_companies(int $companyId): void
    {
        [$company, $car, $model, $part] = $this->createProductGraph($companyId);
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);
        $company->shops()->attach([$confirmed->id, $pending->id]);
        $part->shops()->attach([$confirmed->id, $pending->id]);

        $data = app(ProductService::class)->getProductPageData($company, $car, $model, $part);

        $this->assertSame([$confirmed->id], $data['shops']->pluck('id')->all());
    }

    public static function productCompanies(): array
    {
        return ['kia' => [1], 'hyundai' => [2], 'general' => [10]];
    }

    public function test_pending_priority_anchor_does_not_appear_but_preserves_company_pinning(): void
    {
        [$company, $car, $model, $part] = $this->createProductGraph(10);

        foreach ([409, 4, 6, 500] as $id) {
            $shop = new Shop(['name' => 'Shop '.$id, 'slug' => 'shop-'.$id, 'confirmed' => $id !== 409, 'show_under_product' => true, 'order' => $id === 500 ? 1 : 100]);
            $shop->id = $id;
            $shop->save();
            $shop->companies()->attach($company);
            $shop->parts()->attach($part);
        }

        $data = app(ProductService::class)->getProductPageData($company, $car, $model, $part);

        $this->assertSame([4, 6, 500], $data['shops']->pluck('id')->all());
    }

    public function test_migration_confirms_existing_shops_but_not_future_shops(): void
    {
        $confirmed = $this->createShop('confirmed', true);
        $pending = $this->createShop('pending', false);
        $migration = require database_path('migrations/2026_10_06_000002_confirm_existing_shops.php');

        $migration->up();
        $migration->up();

        $this->assertSame([$confirmed->id, $pending->id], Shop::orderBy('id')->pluck('id')->all());
        $this->assertSame(0, DB::table('shops')->where('confirmed', false)->count());
        $this->createShop('future', false);
        $this->assertSame(2, Shop::count());
        $this->assertSame(3, DB::table('shops')->count());

        $migration->down();

        $this->assertSame(2, Shop::count());
    }

    private function createShop(string $slug, bool $confirmed): Shop
    {
        $shop = Shop::create(['name' => 'Shop '.$slug, 'slug' => $slug, 'confirmed' => $confirmed, 'show_under_product' => true]);
        $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);

        return $shop;
    }

    private function createProductGraph(int $companyId): array
    {
        $company = new Company(['name' => 'Company', 'slug' => 'company']);
        $company->id = $companyId;
        $company->save();
        $car = Car::create(['name' => 'Car', 'slug' => 'car', 'company_id' => $company->id]);
        $model = CarModel::create(['name' => 'Model', 'slug' => 'model']);
        $car->models()->attach($model);
        $category = PartsCategory::create(['name' => 'Category']);
        $part = Part::create(['name' => 'Part', 'slug' => 'part', 'parts_category_id' => $category->id]);

        return [$company, $car, $model, $part];
    }
}
