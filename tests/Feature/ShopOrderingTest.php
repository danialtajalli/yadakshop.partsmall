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
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShopOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['https://api.qrserver.com/*' => Http::response('', 503)]);
    }

    public function test_home_and_product_scopes_use_independent_orders_with_stable_ties(): void
    {
        $first = $this->createShop(['name' => 'Z', 'slug' => 'first', 'order' => 30, 'home_order' => 1]);
        $second = $this->createShop(['name' => 'A', 'slug' => 'second', 'order' => 10, 'home_order' => 2]);
        $third = $this->createShop(['name' => 'B', 'slug' => 'third', 'order' => 20, 'home_order' => 2]);
        $fourth = $this->createShop(['name' => 'B', 'slug' => 'fourth', 'order' => 20, 'home_order' => 2]);
        $this->createShop(['slug' => 'pending', 'confirmed' => false, 'order' => 0, 'home_order' => 0]);

        $this->assertSame([$first->id, $second->id, $third->id, $fourth->id], Shop::orderedForHome()->pluck('id')->all());
        $this->assertSame([$second->id, $third->id, $fourth->id, $first->id], Shop::ordered()->pluck('id')->all());
    }

    public function test_homepage_order_can_change_without_changing_product_order(): void
    {
        $first = $this->createShop(['slug' => 'first', 'order' => 20, 'home_order' => 1]);
        $second = $this->createShop(['slug' => 'second', 'order' => 10, 'home_order' => 2]);

        $this->get(route('home'))->assertOk()
            ->assertViewHas('shops', fn ($shops): bool => $shops->pluck('slug')->all() === ['first', 'second']);

        $first->update(['order' => 5]);
        $this->get(route('home'))->assertOk()
            ->assertViewHas('shops', fn ($shops): bool => $shops->pluck('slug')->all() === ['first', 'second']);

        $first->update(['home_order' => 3]);
        $this->get(route('home'))->assertOk()
            ->assertViewHas('shops', fn ($shops): bool => $shops->pluck('slug')->all() === ['second', 'first']);
        $this->assertSame([$first->id, $second->id], Shop::ordered()->pluck('id')->all());
    }

    public function test_homepage_applies_home_order_before_the_featured_limit(): void
    {
        $slugs = [];

        foreach (range(1, 19) as $order) {
            $slug = 'shop-'.$order;
            $this->createShop(['slug' => $slug, 'order' => $order, 'home_order' => 20 - $order]);
            $slugs[] = $slug;
        }

        $data = app(HomePageService::class)->getHomePageData();

        $this->assertSame(array_reverse(array_slice($slugs, 2)), $data['shops']->pluck('slug')->all());
    }

    public function test_best_shop_banner_keeps_its_configured_sequence(): void
    {
        $first = $this->createShop(['slug' => 'first', 'order' => 20, 'home_order' => 2]);
        $second = $this->createShop(['slug' => 'second', 'order' => 10, 'home_order' => 1]);
        config(['partsmall.home_best_shop_ids' => [$first->id, $second->id]]);

        $data = app(HomePageService::class)->getHomePageData();

        $this->assertSame(['second', 'first'], $data['shops']->pluck('slug')->all());
        $this->assertSame(['first', 'second'], $data['bestShops']->pluck('slug')->all());
    }

    public function test_product_and_related_shop_lists_ignore_home_order(): void
    {
        [$company, $car, $model, $part] = $this->createProductGraph();
        $profile = $this->createShop(['slug' => 'profile', 'show_under_product' => false]);
        $first = $this->createShop(['slug' => 'first', 'order' => 10, 'home_order' => 2]);
        $second = $this->createShop(['slug' => 'second', 'order' => 20, 'home_order' => 1]);
        $company->shops()->attach([$profile->id, $first->id, $second->id]);
        $part->shops()->attach([$first->id, $second->id]);

        $product = app(ProductService::class)->getProductPageData($company, $car, $model, $part);
        $related = app(ShopProfileService::class)->getProfilePageData($profile->slug);

        $this->assertSame([$first->id, $second->id], $product['shops']->pluck('id')->all());
        $this->assertSame([$first->id, $second->id], $related['relatedShops']->pluck('id')->all());

        $second->update(['order' => 5]);
        $product = app(ProductService::class)->getProductPageData($company, $car, $model, $part);
        $related = app(ShopProfileService::class)->getProfilePageData($profile->slug);

        $this->assertSame([$second->id, $first->id], $product['shops']->pluck('id')->all());
        $this->assertSame([$second->id, $first->id], $related['relatedShops']->pluck('id')->all());
    }

    public function test_product_priority_pins_do_not_change_home_order(): void
    {
        [$company, $car, $model, $part] = $this->createProductGraph();

        foreach ([409 => 3, 4 => 2, 6 => 1, 500 => 0] as $id => $homeOrder) {
            $shop = new Shop([
                'name' => 'Shop '.$id,
                'slug' => 'shop-'.$id,
                'confirmed' => true,
                'show_under_product' => true,
                'order' => $id === 500 ? 1 : 100,
                'home_order' => $homeOrder,
            ]);
            $shop->id = $id;
            $shop->save();
            $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);
            $shop->companies()->attach($company);
            $shop->parts()->attach($part);
        }

        $product = app(ProductService::class)->getProductPageData($company, $car, $model, $part);
        $home = app(HomePageService::class)->getHomePageData();

        $this->assertSame([409, 4, 6, 500], $product['shops']->pluck('id')->all());
        $this->assertSame(['shop-500', 'shop-6', 'shop-4', 'shop-409'], $home['shops']->pluck('slug')->all());
    }

    public function test_new_shops_receive_independent_orders_including_pending_shops(): void
    {
        $this->createShop(['slug' => 'pending', 'confirmed' => false, 'order' => 30, 'home_order' => 70]);
        $automatic = $this->createShop(['slug' => 'automatic']);
        $productZero = $this->createShop(['slug' => 'product-zero', 'order' => 0]);
        $homeZero = $this->createShop(['slug' => 'home-zero', 'home_order' => 0]);

        $this->assertSame(31, $automatic->order);
        $this->assertSame(71, $automatic->home_order);
        $this->assertSame(0, $productZero->order);
        $this->assertSame(72, $productZero->home_order);
        $this->assertSame(32, $homeZero->order);
        $this->assertSame(0, $homeZero->home_order);
    }

    public function test_migration_copies_existing_orders_including_pending_shops_and_null_values(): void
    {
        $first = $this->createShop(['slug' => 'first', 'order' => 5, 'home_order' => 100]);
        $pending = $this->createShop(['slug' => 'pending', 'confirmed' => false, 'order' => 20, 'home_order' => 0]);
        $null = $this->createShop(['slug' => 'null']);
        DB::table('shops')->where('id', $null->id)->update(['order' => null]);
        $migration = require database_path('migrations/2026_10_06_000003_add_home_order_to_shops_table.php');
        $migration->down();

        $this->assertFalse(Schema::hasColumn('shops', 'home_order'));
        $migration->up();

        $this->assertTrue(Schema::hasIndex('shops', 'shops_home_order_index'));
        $this->assertSame([5, 20, null], DB::table('shops')->orderBy('id')->pluck('home_order')->all());
        $this->assertSame([5, 20, null], DB::table('shops')->orderBy('id')->pluck('order')->all());
        $this->assertDatabaseHas('shops', ['id' => $first->id, 'confirmed' => true]);
        $this->assertDatabaseHas('shops', ['id' => $pending->id, 'confirmed' => false]);
    }

    public function test_migration_rollback_preserves_shops_and_product_order(): void
    {
        $shop = $this->createShop(['slug' => 'first', 'order' => 17, 'home_order' => 2]);
        $migration = require database_path('migrations/2026_10_06_000003_add_home_order_to_shops_table.php');

        $migration->down();

        $this->assertFalse(Schema::hasColumn('shops', 'home_order'));
        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'order' => 17]);
        $this->assertDatabaseCount('shops', 1);

        $migration->up();
    }

    private function createShop(array $attributes): Shop
    {
        $shop = Shop::create(array_merge([
            'name' => 'Shop',
            'confirmed' => true,
            'show_under_product' => true,
        ], $attributes));
        $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);

        return $shop;
    }

    private function createProductGraph(): array
    {
        $company = new Company(['name' => 'Company', 'slug' => 'company']);
        $company->id = 10;
        $company->save();
        $car = Car::create(['name' => 'Car', 'slug' => 'car', 'company_id' => $company->id]);
        $model = CarModel::create(['name' => 'Model', 'slug' => 'model']);
        $car->models()->attach($model);
        $category = PartsCategory::create(['name' => 'Category']);
        $part = Part::create(['name' => 'Part', 'slug' => 'part', 'parts_category_id' => $category->id]);

        return [$company, $car, $model, $part];
    }
}
