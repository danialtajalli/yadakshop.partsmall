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
            'name' => 'کمپانی آزمایش',
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
            ->assertSee('کمپانی آزمایش', false)
            ->assertSee('خودرو آزمایش', false)
            ->assertSee('مدل آزمایش', false);
    }

    public function test_search_page_prompts_for_a_query(): void
    {
        $this->get(route('search.index'))
            ->assertOk()
            ->assertSee('برای جستجو، نام قطعه، فروشگاه، تعمیرگاه، نمایندگی، کمپانی، خودرو یا مدل را وارد کنید.', false);
    }
}
