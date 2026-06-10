<?php

namespace Tests\Feature;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Enums\PhoneType;
use App\Models\Company;
use App\Models\Comment;
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
        $response->assertViewHas('title', 'یدک شاپ');
        $response->assertSee('یدک شاپ', false);
        $response->assertSee('هیوندای', false);
        $response->assertSee('02133979370', false);
        $response->assertSee('telegram', false);
        $response->assertSee('نظرات کاربران', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('BreadcrumbList', false);
        $response->assertSee(route('shops.index'), false);
    }

    public function test_shop_profile_returns_not_found_for_unknown_slug(): void
    {
        $this->get(route('shop.profile', 'unknown-shop'))->assertNotFound();
    }

    private function seedShopProfileGraph(): Shop
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);

        $shop = Shop::create([
            'name' => 'یدک شاپ',
            'secondary_name' => 'لوازم یدکی',
            'slug' => 'yadak-shop',
            'description' => 'توضیحات فروشگاه',
            'person_responsible_name' => 'بابک صفری',
            'person_responsible_email' => 'info@yadak.shop',
            'website_show' => 'www.yadak.shop',
            'state_id' => $state->id,
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
            'name' => 'https://t.me/yadakshop',
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
