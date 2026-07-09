<?php

namespace Tests\Unit\Services;

use App\Enums\ImageType;
use App\Models\City;
use App\Models\RepairCategory;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Models\State;
use App\Services\DirectoryListingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DirectoryListingServiceTest extends TestCase
{
    use RefreshDatabase;

    private DirectoryListingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DirectoryListingService;
    }

    public function test_shop_listing_returns_expected_page_data_structure(): void
    {
        $this->createShopWithLogo(['name' => 'یدک شاپ', 'slug' => 'yadak-shop']);

        $data = $this->service->getShopListing(Request::create('/shops', 'GET'));

        $this->assertSame(
            [
                'listings',
                'type',
                'title',
                'states',
                'cities',
                'citiesByState',
                'specializations',
                'filters',
                'showSpecializationFilter',
            ],
            array_keys($data),
        );
        $this->assertSame('shop', $data['type']);
        $this->assertSame('فروشگاه‌های لوازم یدکی', $data['title']);
        $this->assertFalse($data['showSpecializationFilter']);
        $this->assertTrue($data['specializations']->isEmpty());
    }

    public function test_shop_listing_filters_by_search_query(): void
    {
        $this->createShopWithLogo(['name' => 'یدک شاپ', 'slug' => 'yadak-shop']);
        $this->createShopWithLogo(['name' => 'قطعه مرکزی', 'slug' => 'parts-center']);

        $data = $this->service->getShopListing(Request::create('/shops?q=یدک', 'GET'));

        $this->assertCount(1, $data['listings']);
        $this->assertSame('یدک شاپ', $data['listings']->first()->name);
        $this->assertSame('یدک', $data['filters']['q']);
    }

    public function test_shop_listing_filters_by_state_and_city(): void
    {
        $tehran = $this->createState('تهران', 'tehran', '021');
        $isfahan = $this->createState('اصفهان', 'isfahan', '031');
        $tehranCity = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $tehran->id]);

        $this->createShopWithLogo([
            'name' => 'فروشگاه تهران',
            'slug' => 'tehran-shop',
            'state_id' => $tehran->id,
            'address' => 'تهران، خیابان ولیعصر',
        ]);
        $this->createShopWithLogo([
            'name' => 'فروشگاه اصفهان',
            'slug' => 'isfahan-shop',
            'state_id' => $isfahan->id,
            'address' => 'اصفهان، چهارباغ',
        ]);

        $data = $this->service->getShopListing(Request::create('/shops', 'GET', [
            'state_id' => $tehran->id,
            'city_id' => $tehranCity->id,
        ]));

        $this->assertCount(1, $data['listings']);
        $this->assertSame('فروشگاه تهران', $data['listings']->first()->name);
        $this->assertCount(1, $data['cities']);
        $this->assertSame('تهران', $data['cities']->first()->name);
    }

    public function test_shop_listing_only_includes_shops_with_logo_image(): void
    {
        $withLogo = $this->createShopWithLogo(['name' => 'دارای لوگو', 'slug' => 'with-logo']);

        $withoutLogo = Shop::create([
            'name' => 'بدون لوگو',
            'slug' => 'without-logo',
            'show_under_product' => false,
            'order' => 2,
        ]);
        $withoutLogo->images()->create([
            'type' => ImageType::Cover,
            'path' => 'cover.webp',
        ]);

        $data = $this->service->getShopListing(Request::create('/shops', 'GET'));

        $this->assertCount(1, $data['listings']);
        $this->assertSame($withLogo->id, $data['listings']->first()->id);
    }

    public function test_shop_listing_attaches_logo_url(): void
    {
        $shop = $this->createShopWithLogo([
            'name' => 'یدک شاپ',
            'slug' => 'yadak-shop',
        ], 'yadak.webp');

        $data = $this->service->getShopListing(Request::create('/shops', 'GET'));
        $listing = $data['listings']->first();

        $this->assertStringContainsString('shop/logo/'.$shop->id.'/yadak.webp', $listing->logo);
    }

    public function test_shop_listing_includes_shops_regardless_of_show_under_product_scope(): void
    {
        $this->createShopWithLogo([
            'name' => 'مخفی',
            'slug' => 'hidden-shop',
            'show_under_product' => false,
        ]);

        $data = $this->service->getShopListing(Request::create('/shops', 'GET'));

        $this->assertCount(1, $data['listings']);
        $this->assertSame('مخفی', $data['listings']->first()->name);
    }

    public function test_shop_listing_paginates_results(): void
    {
        foreach (range(1, 15) as $index) {
            $this->createShopWithLogo([
                'name' => "فروشگاه {$index}",
                'slug' => "shop-{$index}",
                'order' => $index,
            ]);
        }

        $data = $this->service->getShopListing(Request::create('/shops', 'GET'));

        $this->assertCount(12, $data['listings']);
        $this->assertSame(15, $data['listings']->total());
    }

    public function test_shop_listing_title_includes_page_number_on_later_pages(): void
    {
        foreach (range(1, 25) as $index) {
            $this->createShopWithLogo([
                'name' => "فروشگاه {$index}",
                'slug' => "shop-{$index}",
                'order' => $index,
            ]);
        }

        $data = $this->service->getShopListing(Request::create('/shops', 'GET', ['page' => 2]));

        $this->assertSame('فروشگاه‌های لوازم یدکی - صفحه 2', $data['title']);
    }

    public function test_repair_shop_listing_returns_expected_page_data_structure(): void
    {
        $specialization = RepairCategory::create(['name' => 'جلوبندی']);
        $this->createRepairShopWithLogo(['name' => 'تعمیرگاه آریا', 'slug' => 'aria']);

        $data = $this->service->getRepairShopListing(Request::create('/repair_shops', 'GET'));

        $this->assertSame('repair_shop', $data['type']);
        $this->assertSame('تعمیرگاه‌ها', $data['title']);
        $this->assertTrue($data['showSpecializationFilter']);
        $this->assertTrue($data['specializations']->contains('id', $specialization->id));
    }

    public function test_repair_shop_listing_filters_by_specialization(): void
    {
        $specialization = RepairCategory::create(['name' => 'جلوبندی']);
        $otherSpecialization = RepairCategory::create(['name' => 'موتور']);

        $matching = $this->createRepairShopWithLogo([
            'name' => 'تعمیرگاه جلوبندی',
            'slug' => 'front-shop',
        ]);
        $matching->repairCategories()->attach($specialization);

        $other = $this->createRepairShopWithLogo([
            'name' => 'تعمیرگاه موتور',
            'slug' => 'engine-shop',
        ]);
        $other->repairCategories()->attach($otherSpecialization);

        $data = $this->service->getRepairShopListing(Request::create('/repair_shops', 'GET', [
            'specialization_id' => $specialization->id,
        ]));

        $this->assertCount(1, $data['listings']);
        $this->assertSame('تعمیرگاه جلوبندی', $data['listings']->first()->name);
        $this->assertSame($specialization->id, $data['filters']['specialization_id']);
    }

    public function test_repair_shop_listing_filters_by_search_query(): void
    {
        $this->createRepairShopWithLogo([
            'name' => 'تعمیرگاه آریا',
            'slug' => 'aria',
            'work_description' => 'تخصص جلوبندی',
        ]);
        $this->createRepairShopWithLogo([
            'name' => 'تعمیرگاه پارس',
            'slug' => 'pars',
            'work_description' => 'برق خودرو',
        ]);

        $data = $this->service->getRepairShopListing(Request::create('/repair_shops?q=جلوبندی', 'GET'));

        $this->assertCount(1, $data['listings']);
        $this->assertSame('تعمیرگاه آریا', $data['listings']->first()->name);
    }

    public function test_repair_shop_listing_attaches_logo_url(): void
    {
        $shop = $this->createRepairShopWithLogo([
            'name' => 'تعمیرگاه آریا',
            'slug' => 'aria',
        ], 'aria.webp');

        $data = $this->service->getRepairShopListing(Request::create('/repair_shops', 'GET'));
        $listing = $data['listings']->first();

        $this->assertStringContainsString('repair/logo/'.$shop->id.'/aria.webp', $listing->logo);
    }

    public function test_listing_builds_cities_grouped_by_state(): void
    {
        $tehran = $this->createState('تهران', 'tehran', '021');
        $isfahan = $this->createState('اصفهان', 'isfahan', '031');

        City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $tehran->id]);
        City::create(['name' => 'شیراز', 'slug' => 'shiraz-city', 'state_id' => $isfahan->id]);

        $this->createShopWithLogo(['name' => 'فروشگاه', 'slug' => 'shop']);

        $data = $this->service->getShopListing(Request::create('/shops', 'GET'));

        $this->assertArrayHasKey($tehran->id, $data['citiesByState']);
        $this->assertArrayHasKey($isfahan->id, $data['citiesByState']);
        $this->assertSame('تهران', $data['citiesByState'][$tehran->id][0]['name']);
    }

    private function createState(string $name, string $slug, string $telPrefix): State
    {
        return State::create([
            'name' => $name,
            'slug' => $slug,
            'tel_prefix' => $telPrefix,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createShopWithLogo(array $overrides = [], string $logoPath = 'logo.webp'): Shop
    {
        $shop = Shop::create(array_merge([
            'name' => 'فروشگاه',
            'slug' => 'shop-'.uniqid(),
            'show_under_product' => true,
            'order' => 1,
        ], $overrides));

        $shop->images()->create([
            'type' => ImageType::Logo,
            'path' => $logoPath,
        ]);

        return $shop;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createRepairShopWithLogo(array $overrides = [], string $logoPath = 'logo.webp'): RepairShop
    {
        $shop = RepairShop::create(array_merge([
            'name' => 'تعمیرگاه',
            'slug' => 'repair-'.uniqid(),
        ], $overrides));

        $shop->images()->create([
            'type' => ImageType::Logo,
            'path' => $logoPath,
        ]);

        return $shop;
    }
}
