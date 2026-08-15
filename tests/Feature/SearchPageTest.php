<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\Representation;
use App\Models\RepairShop;
use App\Models\Shop;
use App\Enums\ImageType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\EngineManager;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_uses_scout_indexed_part_fields(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $suspension = PartsCategory::create(['name' => 'جلوبندی']);
        $engine = PartsCategory::create(['name' => 'موتوری']);

        Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $suspension->id,
        ]);
        Part::create([
            'name' => 'پیستون',
            'slug' => 'piston',
            'parts_category_id' => $engine->id,
        ]);

        $this->get(route('search.index', ['q' => 'جلوبندی']))
            ->assertOk()
            ->assertViewIs('search.index')
            ->assertSee('طبق', false)
            ->assertDontSee('پیستون', false);
    }

    public function test_search_page_finds_all_global_search_models(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $company = Company::create([
            'name' => 'برند خودرو آزمایش',
            'slug' => 'test-company',
        ]);

        $car = Car::create([
            'name' => 'خودرو آزمایش',
            'slug' => 'test-car',
            'company_id' => $company->id,
        ]);

        $model = CarModel::create([
            'name' => 'مدل آزمایش',
            'slug' => 'test-model',
        ]);
        $model->cars()->attach($car);

        $partsCategory = PartsCategory::create(['name' => 'دسته آزمایش']);

        Part::create([
            'name' => 'قطعه آزمایش',
            'slug' => 'test-part',
            'parts_category_id' => $partsCategory->id,
        ]);

        Shop::create([
            'name' => 'فروشگاه آزمایش',
            'slug' => 'test-shop',
        ]);

        RepairShop::create([
            'name' => 'تعمیرگاه آزمایش',
            'slug' => 'test-repair-shop',
        ]);

        Representation::create([
            'name' => 'نمایندگی آزمایش',
            'slug' => 'test-representation',
            'company_id' => $company->id,
        ]);

        $this->get(route('search.index', ['q' => 'آزمایش']))
            ->assertOk()
            ->assertSee('قطعه آزمایش', false)
            ->assertSee('فروشگاه آزمایش', false)
            ->assertSee('تعمیرگاه آزمایش', false)
            ->assertSee('نمایندگی آزمایش', false)
            ->assertSee('برند خودرو آزمایش', false)
            ->assertSee('خودرو آزمایش', false)
            ->assertDontSee('مدل آزمایش', false);
    }

    public function test_exact_vehicle_query_only_shows_vehicle(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $this->createSearchIntentFixture();

        $this->get(route('search.index', ['q' => 'هیوندای سانتافه']))
            ->assertOk()
            ->assertSee('خودرو', false)
            ->assertSee('سانتافه', false)
            ->assertDontSee('محصولات', false)
            ->assertDontSee('نیو', false)
            ->assertDontSee('لنت ترمز جلو', false);
    }

    public function test_exact_company_query_shows_company_only(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $this->createSearchIntentFixture();

        $this->get(route('search.index', ['q' => 'هیوندای']))
            ->assertOk()
            ->assertSee('هیوندای', false)
            ->assertDontSee('محصولات', false)
            ->assertDontSee('نیو', false)
            ->assertDontSee('لنت ترمز جلو', false);
    }

    public function test_vehicle_query_with_model_name_shows_company_car_and_model(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $this->createSearchIntentFixture(modelName: '2012', modelSlug: '2012');
        Car::create([
            'name' => 'ix45',
            'slug' => 'ix45',
            'company_id' => Company::create(['name' => 'برند دیگر', 'slug' => 'other-brand'])->id,
        ]);

        $this->get(route('search.index', ['q' => 'هیوندای سانتافه 2012']))
            ->assertOk()
            ->assertSee('هیوندای', false)
            ->assertSee('سانتافه', false)
            ->assertSee('2012', false)
            ->assertDontSee('محصولات', false)
            ->assertDontSee('لنت ترمز جلو', false)
            ->assertDontSee('front-brake-pad', false)
            ->assertDontSee('ix45', false);
    }

    public function test_exact_part_query_shows_related_parts_only(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $this->createSearchIntentFixture();

        $this->get(route('search.index', ['q' => 'لنت']))
            ->assertOk()
            ->assertSee('قطعات', false)
            ->assertSee('لنت ترمز جلو', false)
            ->assertSee('لنت ترمز عقب', false)
            ->assertDontSee('محصولات', false)
            ->assertDontSee('سانتافه', false);
    }

    public function test_mixed_part_vehicle_query_shows_existing_product_results_only(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $this->createSearchIntentFixture();

        $this->get(route('search.index', ['q' => 'لنت جلو سانتافه']))
            ->assertOk()
            ->assertSee('محصولات', false)
            ->assertSee('هیوندای سانتافه', false)
            ->assertSee('لنت ترمز جلو', false)
            ->assertDontSee('فروشگاه‌ها و نمایندگی‌ها', false);
    }

    public function test_model_only_queries_do_not_generate_products(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $this->createSearchIntentFixture();

        $this->get(route('search.index', ['q' => 'نیو']))
            ->assertOk()
            ->assertDontSee('محصولات', false)
            ->assertDontSee('لنت ترمز جلو', false);
    }

    public function test_search_page_prompts_for_a_query(): void
    {
        $this->get(route('search.index'))
            ->assertOk()
            ->assertSee('برای جستجو، نام قطعه، فروشگاه، تعمیرگاه، نمایندگی، برند خودرو، خودرو یا مدل را وارد کنید.', false);
    }

    public function test_search_page_shows_photo_cards_for_directory_results(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $company = Company::create([
            'name' => 'برند خودرو تصویر',
            'slug' => 'image-company',
        ]);

        $shop = Shop::create([
            'name' => 'فروشگاه تصویر',
            'slug' => 'image-shop',
        ]);
        $shop->images()->create([
            'type' => ImageType::Logo,
            'path' => 'shop-logo.webp',
        ]);

        $repairShop = RepairShop::create([
            'name' => 'تعمیرگاه تصویر',
            'slug' => 'image-repair-shop',
        ]);
        $repairShop->images()->create([
            'type' => ImageType::Logo,
            'path' => 'repair-logo.webp',
        ]);

        Representation::create([
            'name' => 'نمایندگی تصویر',
            'slug' => 'image-representation',
            'company_id' => $company->id,
            'logo' => 'rep-logo.webp',
        ]);

        $this->get(route('search.index', ['q' => 'تصویر']))
            ->assertOk()
            ->assertSee('shop/logo/'.$shop->id.'/shop-logo.webp', false)
            ->assertSee('repair/logo/'.$repairShop->id.'/repair-logo.webp', false)
            ->assertSee('representation/logo/', false)
            ->assertSee('rep-logo.webp', false);
    }

    /**
     * @return array{company: Company, car: Car, model: CarModel}
     */
    private function createSearchIntentFixture(string $modelName = 'نیو', string $modelSlug = 'new'): array
    {
        $company = Company::create([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
        ]);

        $car = Car::create([
            'name' => 'سانتافه',
            'slug' => 'santafe',
            'company_id' => $company->id,
        ]);

        $model = CarModel::create([
            'name' => $modelName,
            'slug' => $modelSlug,
        ]);
        $model->cars()->attach($car);

        $category = PartsCategory::create(['name' => 'ترمز']);

        Part::create([
            'name' => 'لنت ترمز جلو',
            'slug' => 'front-brake-pad',
            'parts_category_id' => $category->id,
        ]);

        Part::create([
            'name' => 'لنت ترمز عقب',
            'slug' => 'rear-brake-pad',
            'parts_category_id' => $category->id,
        ]);

        return [
            'company' => $company,
            'car' => $car,
            'model' => $model,
        ];
    }
}
