<?php

namespace Tests\Feature;

use App\Enums\ImageType;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\RepairShop;
use App\Models\Representation;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Laravel\Scout\EngineManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImageAltTextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $company = Company::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $company->images()->create(['type' => ImageType::Logo, 'path' => 'brand.png', 'alt' => 'Custom brand logo']);
        $car = $company->cars()->create(['name' => 'Test Car', 'slug' => 'test-car']);
        $model = CarModel::create(['name' => 'Test Model', 'slug' => 'test-model']);
        $car->models()->attach($model);
        $category = PartsCategory::create(['name' => 'Test Category']);
        $part = Part::create(['name' => 'Test Part', 'slug' => 'test-part', 'parts_category_id' => $category->id]);

        $shop = Shop::create(['name' => 'Test Shop', 'slug' => 'test-shop', 'confirmed' => true, 'show_under_product' => true]);
        $shop->companies()->attach($company);
        $shop->parts()->attach($part);
        $shop->images()->create(['type' => ImageType::Logo, 'path' => 'shop.png', 'alt' => 'Shop "logo" <sign>']);
        $shop->images()->create(['type' => ImageType::Cover, 'path' => 'cover.png', 'alt' => 'Custom shop cover']);
        $relatedShop = Shop::create(['name' => 'Related Shop', 'slug' => 'related-shop', 'confirmed' => true]);
        $relatedShop->companies()->attach($company);
        $relatedShop->images()->create(['type' => ImageType::Logo, 'path' => 'related.png', 'alt' => 'Custom related logo']);
        config(['partsmall.home_best_shop_ids' => [$shop->id]]);

        $repair = RepairShop::create(['name' => 'Test Repair', 'slug' => 'test-repair']);
        $repair->images()->create(['type' => ImageType::Logo, 'path' => 'repair.png', 'alt' => 'Custom repair logo']);
        $repair->images()->create(['type' => ImageType::Cover, 'path' => 'repair-cover.png', 'alt' => 'Custom repair cover']);
        Representation::create([
            'name' => 'Test Representation', 'slug' => 'test-representation', 'company_id' => $company->id,
            'logo' => 'representation.png', 'logo_alt' => 'Custom representation logo',
        ]);
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_use_saved_alt_text(string $path, array $alts): void
    {
        $response = $this->get($path)->assertOk();

        foreach ($alts as $alt) {
            $response->assertSee('alt="'.e($alt).'"', false);
        }
        $response->assertDontSee('alt="Shop "logo" <sign>"', false);
    }

    public static function publicPages(): array
    {
        return [
            'home' => ['/', ['Shop "logo" <sign>', 'Custom brand logo', 'Custom repair logo', 'Custom representation logo']],
            'shop profile' => ['/profile/test-shop', ['Shop "logo" <sign>', 'Custom shop cover', 'Custom brand logo', 'Custom related logo']],
            'shops' => ['/shops', ['Shop "logo" <sign>']],
            'company shops' => ['/shops/test-brand', ['Shop "logo" <sign>', 'Custom brand logo']],
            'repair shops' => ['/repair_shops', ['Custom repair logo']],
            'repair profile' => ['/carservice/1/test-repair', ['Custom repair logo', 'Custom repair cover']],
            'representations' => ['/representations', ['Custom representation logo']],
            'representation profile' => ['/representation/test-representation', ['Custom representation logo', 'Custom brand logo']],
            'companies' => ['/company', ['Custom brand logo']],
            'shop search' => ['/search?q=Test+Shop', ['Shop "logo" <sign>']],
            'brand search' => ['/search?q=Test+Brand', ['Custom brand logo']],
            'repair search' => ['/search?q=Test+Repair', ['Custom repair logo']],
            'representation search' => ['/search?q=Test+Representation', ['Custom representation logo']],
            'product' => ['/product/test-brand/test-car/test-model/test-part', ['Shop "logo" <sign>']],
        ];
    }

    public function test_blank_alt_text_keeps_name_based_fallbacks(): void
    {
        Shop::firstOrFail()->images()->update(['alt' => null]);

        $this->get('/profile/test-shop')->assertOk()
            ->assertSee('alt="لوگوی فروشگاه Test Shop"', false)
            ->assertSee('alt="تصویر فروشگاه Test Shop"', false);

        $html = Blade::render('<x-ui.company-logo name="Test Brand" logo-url="/logo.png" alt="   " />');
        $this->assertStringContainsString('alt="لوگوی Test Brand"', $html);
    }

    public function test_company_filters_receive_saved_alt_text(): void
    {
        $this->get('/')->assertOk()->assertSee('data-logo-alt="Custom brand logo"', false);
        $this->get('/shops')->assertOk()->assertSee('data-logo-alt="Custom brand logo"', false);
    }
}
