<?php

namespace Tests\Feature;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Enums\PhoneType;
use App\Models\City;
use App\Models\Comment;
use App\Models\Company;
use App\Models\PartsCategory;
use App\Models\Shop;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_profile_returns_successful_response(): void
    {
        $shop = $this->seedShopProfileGraph();

        $response = $this->get(route('shop.profile', $shop->slug));

        $response->assertOk();
        $response->assertViewIs('shop.show');
        $response->assertViewHas('title', 'پروفایل یدک شاپ  در پارتس‌مال');
        $response->assertSee('یدک شاپ', false);
        $response->assertSee('هیوندای', false);
        $response->assertSee('02133979370', false);
        $response->assertSee('fa-solid fa-phone', false);
        $response->assertSee('https://t.me/yadakshop', false);
        $response->assertSee('telegram', false);
        $response->assertSee('نظرات کاربران', false);
        $response->assertSee('فروشگاه عالی بود', false);
        $response->assertSee('کاربر تست', false);
        $response->assertSee('میانگین امتیاز', false);
        $response->assertSee('۵/۵', false);
        $response->assertDontSee('نظر تایید نشده', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('BreadcrumbList', false);
        $response->assertSee(route('shops.index'), false);
        $response->assertSee('موقعیت روی نقشه', false);
        $response->assertSee('data-location-map', false);
        $response->assertSee('مسیریابی در گوگل مپ', false);
        $response->assertSee('leaflet@1.9.4', false);
        $response->assertSee('data-lat="35.68843735"', false);
        $response->assertSee('data-lng="51.43004894"', false);
        $response->assertDontSee('data-floating-call', false);
        $response->assertSee('data-shop-phones-jump', false);
        $response->assertSee('ps-shop-phones-jump', false);
        $response->assertSee('id="shop-phones"', false);
    }

    public function test_shop_profile_hides_unconfirmed_comments(): void
    {
        $shop = $this->seedShopProfileGraph();

        Comment::create([
            'shop_id' => $shop->id,
            'fullname' => 'کاربر تایید نشده',
            'body' => 'نظر تایید نشده',
            'rating' => 1,
            'confirmed' => false,
        ]);

        $response = $this->get(route('shop.profile', $shop->slug));

        $response->assertOk();
        $response->assertSee('فروشگاه عالی بود', false);
        $response->assertDontSee('نظر تایید نشده', false);
        $response->assertDontSee('کاربر تایید نشده', false);
    }

    public function test_shop_profile_shows_empty_comments_state(): void
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);

        $shop = Shop::create([
            'name' => 'بدون نظر',
            'slug' => 'no-comments-shop',
            'city_id' => $city->id,
            'order' => 1,
        ]);

        $response = $this->get(route('shop.profile', $shop->slug));

        $response->assertOk();
        $response->assertSee('نظرات کاربران', false);
        $response->assertSee('هنوز نظری برای این فروشگاه ثبت نشده است.', false);
    }

    public function test_shop_profile_formats_telegram_t_me_links_with_at_prefix(): void
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);

        $shop = Shop::create([
            'name' => 'فروشگاه تلگرام',
            'slug' => 'telegram-shop',
            'city_id' => $city->id,
            'order' => 1,
        ]);

        $shop->links()->create([
            'name' => 't.me/yadakshop',
            'link_type' => LinkType::Telegram,
        ]);

        $shop->links()->create([
            'name' => 't.me/secondshop',
            'link_type' => LinkType::Telegram,
        ]);

        $this->get(route('shop.profile', $shop->slug))
            ->assertOk()
            ->assertSee('@t.me/yadakshop', false)
            ->assertSee('@t.me/secondshop', false);
    }

    public function test_shop_profile_returns_not_found_for_unknown_slug(): void
    {
        $this->get(route('shop.profile', 'unknown-shop'))->assertNotFound();
    }

    private function seedShopProfileGraph(): Shop
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);

        $shop = Shop::create([
            'name' => 'یدک شاپ',
            'secondary_name' => 'لوازم یدکی',
            'slug' => 'yadak-shop',
            'description' => 'توضیحات فروشگاه',
            'person_responsible_name' => 'بابک صفری',
            'person_responsible_email' => 'info@yadak.shop',
            'website_show' => 'www.yadak.shop',
            'city_id' => $city->id,
            'address' => 'تهران، خیابان نمونه',
            'latitude' => 35.68843735,
            'longitude' => 51.43004894,
            'show_under_product' => false,
            'order' => 1,
        ]);

        $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);
        $shop->images()->create(['type' => ImageType::Cover, 'path' => 'cover.webp']);

        $shop->phones()->create([
            'phone_number' => '۰۲۱۳۳۹۷۹۳۷۰',
            'type' => PhoneType::Land,
        ]);

        $shop->links()->create([
            'name' => '@yadakshop',
            'link_type' => LinkType::Telegram,
        ]);

        $company = Company::create([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'country' => 'کره',
            'wage_strike' => 2.5,
        ]);
        $company->images()->create(['type' => ImageType::Logo, 'path' => 'hyundai.png']);
        $shop->companies()->attach($company);

        $category = PartsCategory::create(['name' => 'جلوبندی']);
        $shop->partsCategories()->attach($category);

        Comment::create([
            'shop_id' => $shop->id,
            'fullname' => 'کاربر تست',
            'body' => 'فروشگاه عالی بود',
            'rating' => 5,
            'confirmed' => true,
        ]);

        return $shop;
    }
}
