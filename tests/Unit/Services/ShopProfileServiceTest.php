<?php

namespace Tests\Unit\Services;

use App\Enums\ImageType;
use App\Models\City;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Shop;
use App\Models\State;
use App\Services\ShopProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ShopProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShopProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ShopProfileService;
    }

    public function test_it_returns_shop_profile_page_data(): void
    {
        $shop = $this->createShopWithRelations();

        $data = $this->service->getProfilePageData($shop->slug);

        $this->assertSame(
            ['shop', 'title', 'averageRating', 'commentsCount', 'relatedShops'],
            array_keys($data),
        );
        $this->assertSame('پروفایل یدک شاپ  در پارتس‌مال', $data['title']);
        $this->assertSame($shop->id, $data['shop']->id);
        $this->assertStringContainsString('shop/logo/'.$shop->id.'/logo.webp', $data['shop']->logo);
        $this->assertCount(1, $data['shop']->companies);
        $this->assertTrue($data['relatedShops']->isEmpty());
        Log::info($data['shop']->companies->first()->logo_url);
        $this->assertStringContainsString('uploads/company', $data['shop']->companies->first()->logo_url);
    }

    public function test_it_loads_related_shops_that_share_the_same_company(): void
    {
        $shop = $this->createShopWithRelations();

        $relatedShop = Shop::create([
            'name' => 'فروشگاه مرتبط',
            'slug' => 'related-shop',
            'show_under_product' => true,
            'order' => 2,
        ]);
        $relatedShop->images()->create(['type' => ImageType::Logo, 'path' => 'related-logo.webp']);
        $relatedShop->companies()->attach($shop->companies->first());

        $unrelatedShop = Shop::create([
            'name' => 'فروشگاه دیگر',
            'slug' => 'unrelated-shop',
            'order' => 3,
        ]);
        $unrelatedShop->images()->create(['type' => ImageType::Logo, 'path' => 'other-logo.webp']);
        $otherCompany = Company::create([
            'name' => 'کیا',
            'slug' => 'kia',
            'wage_strike' => 2.5,
        ]);
        $unrelatedShop->companies()->attach($otherCompany);

        $data = $this->service->getProfilePageData($shop->slug);

        $this->assertCount(1, $data['relatedShops']);
        $this->assertSame('related-shop', $data['relatedShops']->first()->slug);
        $this->assertStringContainsString(
            'shop/logo/'.$relatedShop->id.'/related-logo.webp',
            $data['relatedShops']->first()->logo,
        );
        $this->assertFalse($data['relatedShops']->contains('id', $shop->id));
        $this->assertFalse($data['relatedShops']->contains('id', $unrelatedShop->id));
    }

    public function test_it_finds_shop_even_when_hidden_from_product_scope(): void
    {
        $shop = Shop::create([
            'name' => 'مخفی',
            'slug' => 'hidden-shop',
            'show_under_product' => false,
            'order' => 1,
        ]);
        $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);

        $data = $this->service->getProfilePageData('hidden-shop');

        $this->assertSame('مخفی', $data['shop']->name);
    }

    public function test_it_increments_visited_count_on_profile_view(): void
    {
        $shop = Shop::create([
            'name' => 'بازدید',
            'slug' => 'visited-shop',
            'visited_count' => 2050,
            'order' => 1,
        ]);
        $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);

        $data = $this->service->getProfilePageData('visited-shop');

        $this->assertSame(2051, $data['shop']->visited_count);
        $this->assertSame(2051, $shop->fresh()->visited_count);
    }

    public function test_new_shops_get_random_visited_count_between_two_thousand_and_twenty_one_hundred(): void
    {
        $shop = Shop::create([
            'name' => 'فروشگاه جدید',
            'slug' => 'new-visited-shop',
            'order' => 1,
        ]);

        $this->assertGreaterThanOrEqual(2000, $shop->visited_count);
        $this->assertLessThanOrEqual(2100, $shop->visited_count);
    }

    public function test_it_only_returns_confirmed_comments(): void
    {
        $shop = $this->createShopWithRelations();

        Comment::create([
            'shop_id' => $shop->id,
            'fullname' => 'تایید شده',
            'body' => 'نظر تایید شده',
            'rating' => 5,
            'confirmed' => true,
        ]);

        Comment::create([
            'shop_id' => $shop->id,
            'fullname' => 'تایید نشده',
            'body' => 'نظر تایید نشده',
            'rating' => 1,
            'confirmed' => false,
        ]);

        $data = $this->service->getProfilePageData($shop->slug);

        $this->assertSame(1, $data['commentsCount']);
        $this->assertSame(5.0, $data['averageRating']);
        $this->assertSame('نظر تایید شده', $data['shop']->comments->first()->body);
    }

    public function test_it_throws_not_found_for_unknown_slug(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->getProfilePageData('missing-shop');
    }

    private function createShopWithRelations(): Shop
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);

        $shop = Shop::create([
            'name' => 'یدک شاپ',
            'slug' => 'yadak-shop',
            'city_id' => $city->id,
            'show_under_product' => true,
            'order' => 1,
        ]);

        $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);

        $company = Company::create([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'wage_strike' => 2.5,
        ]);
        $company->images()->create(['type' => ImageType::Logo, 'path' => 'hyundai.png']);
        $shop->companies()->attach($company);

        return $shop;
    }
}
