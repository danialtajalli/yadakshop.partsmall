<?php

namespace Tests\Feature;

use App\Enums\ImageType;
use App\Models\City;
use App\Models\RepairCategory;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_shops_index_returns_successful_response(): void
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);

        $this->createListedShop([
            'name' => 'یدک شاپ',
            'slug' => 'yadak-shop',
            'city_id' => $city->id,
            'address' => 'تهران، خیابان نمونه',
            'order' => 1,
        ]);

        $response = $this->get(route('shops.index', [
            'q' => 'یدک',
            'state_id' => $state->id,
            'city_id' => $city->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('listings.index');
        $response->assertViewHas('type', 'shop');
        $response->assertSee('یدک شاپ', false);
        $response->assertDontSee('id="listing-specialization"', false);
    }

    public function test_repair_shops_index_returns_successful_response_with_specialization_filter(): void
    {
        $state = State::create(['name' => 'اصفهان', 'slug' => 'isfahan', 'tel_prefix' => '031']);
        $city = City::create(['name' => 'اصفهان', 'slug' => 'isfahan-city', 'state_id' => $state->id]);
        $specialization = RepairCategory::create(['name' => 'جلوبندی']);

        $shop = RepairShop::create([
            'name' => 'تعمیرگاه آریا',
            'slug' => 'aria-repair',
            'city_id' => $city->id,
            'address' => 'اصفهان، خیابان چهارباغ',
        ]);
        $shop->repairCategories()->attach($specialization);

        RepairShop::create([
            'name' => 'تعمیرگاه دیگر',
            'slug' => 'other-repair',
            'city_id' => $city->id,
            'address' => 'اصفهان',
        ]);

        $response = $this->get(route('repair-shops.index', [
            'specialization_id' => $specialization->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('listings.index');
        $response->assertViewHas('type', 'repair_shop');
        $response->assertSee('تعمیرگاه آریا', false);
        $response->assertDontSee('تعمیرگاه دیگر', false);
        $response->assertSee('تخصص‌ها', false);
    }

    public function test_shops_index_paginates_results(): void
    {
        $state = State::create(['name' => 'فارس', 'slug' => 'fars', 'tel_prefix' => '071']);
        $city = City::create(['name' => 'شیراز', 'slug' => 'shiraz-city', 'state_id' => $state->id]);

        foreach (range(1, 15) as $index) {
            $this->createListedShop([
                'name' => "فروشگاه {$index}",
                'slug' => "shop-{$index}",
                'city_id' => $city->id,
                'order' => $index,
            ]);
        }

        $response = $this->get(route('shops.index'));

        $response->assertOk();
        $response->assertViewHas('listings', fn ($listings) => $listings->count() === 12 && $listings->total() === 15);
    }

    public function test_shops_index_title_includes_page_number_on_later_pages(): void
    {
        $state = State::create(['name' => 'فارس', 'slug' => 'fars', 'tel_prefix' => '071']);
        $city = City::create(['name' => 'شیراز', 'slug' => 'shiraz-city', 'state_id' => $state->id]);

        foreach (range(1, 25) as $index) {
            $this->createListedShop([
                'name' => "فروشگاه {$index}",
                'slug' => "shop-{$index}",
                'city_id' => $city->id,
                'order' => $index,
            ]);
        }

        $this->get(route('shops.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('فروشگاه‌های لوازم یدکی - صفحه 2', false)
            ->assertSee('صفحه 2', false)
            ->assertDontSee('page=1', false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createListedShop(array $attributes): Shop
    {
        $shop = Shop::create(array_merge([
            'show_under_product' => true,
            'order' => 1,
        ], $attributes));

        $shop->images()->create([
            'type' => ImageType::Logo,
            'path' => 'logo.webp',
        ]);

        return $shop;
    }
}
