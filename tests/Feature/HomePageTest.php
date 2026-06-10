<?php

namespace Tests\Feature;

use App\Enums\ImageType;
use App\Models\City;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('home.index');
        $response->assertSee('فروشگاه‌های لوازم یدکی', false);
        $response->assertSee('تعمیرگاه‌ها', false);
        $response->assertSee('نمایندگی‌ها', false);
        $response->assertSee('قطعات خودرو', false);
    }

    public function test_home_page_shows_featured_entities_and_parts(): void
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);
        $company = Company::create(['name' => 'ایران خودرو', 'slug' => 'ikco']);

        $shop = Shop::create([
            'name' => 'یدک شاپ',
            'slug' => 'yadak-shop',
            'state_id' => $state->id,
            'order' => 1,
        ]);
        $shop->images()->create([
            'type' => ImageType::Logo,
            'path' => 'logo.webp',
        ]);

        RepairShop::create([
            'name' => 'تعمیرگاه آریا',
            'slug' => 'aria-repair',
            'state_id' => $state->id,
        ]);

        Representation::create([
            'name' => 'نمایندگی آسان',
            'slug' => 'asan-rep',
            'company_id' => $company->id,
            'state_id' => $state->id,
            'city_id' => $city->id,
            'logo' => 'rep-logo.webp',
        ]);

        $category = PartsCategory::create(['name' => 'موتور']);
        Part::create([
            'name' => 'شمع',
            'slug' => 'spark-plug',
            'parts_category_id' => $category->id,
        ]);

        $response = $this->get(route('home'));

        $response->assertSee('یدک شاپ', false);
        $response->assertSee('تعمیرگاه آریا', false);
        $response->assertSee('نمایندگی آسان', false);
        $response->assertSee('شمع', false);
        $response->assertSee(route('shops.index'), false);
        $response->assertSee(route('repair-shops.index'), false);
        $response->assertSee(route('representations.index'), false);
        $response->assertSee(route('shop.profile', 'yadak-shop'), false);
        $response->assertDontSee('route(\'product.show\'', false);
    }
}
