<?php

namespace Tests\Unit\Services;

use App\Enums\ImageType;
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
            ['shop', 'title', 'averageRating', 'commentsCount'],
            array_keys($data),
        );
        $this->assertSame('یدک شاپ', $data['title']);
        $this->assertSame($shop->id, $data['shop']->id);
        $this->assertStringContainsString('shop/logo/'.$shop->id.'/logo.webp', $data['shop']->logo);
        $this->assertCount(1, $data['shop']->companies);
        Log::info($data['shop']->companies->first()->logo_url);
        $this->assertStringContainsString('uploads/company', $data['shop']->companies->first()->logo_url);
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

    public function test_it_throws_not_found_for_unknown_slug(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->getProfilePageData('missing-shop');
    }

    private function createShopWithRelations(): Shop
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);

        $shop = Shop::create([
            'name' => 'یدک شاپ',
            'slug' => 'yadak-shop',
            'state_id' => $state->id,
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
