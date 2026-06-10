<?php

namespace Tests\Unit\Services;

use App\Enums\ImageType;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\RepairCategory;
use App\Models\Shop;
use App\Models\Wage;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProductService;
    }

    public function test_it_returns_expected_page_data_structure(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame(
            ['company', 'car', 'model', 'part', 'repairCards', 'shops', 'title', 'repairLocator'],
            array_keys($data),
        );
        $this->assertNull($data['repairLocator']);
        $this->assertSame($company->id, $data['company']->id);
        $this->assertSame($car->id, $data['car']->id);
        $this->assertSame($model->id, $data['model']->id);
        $this->assertSame($part->id, $data['part']->id);
    }

    public function test_it_sanitizes_car_and_part_descriptions(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph([
            'car_description' => 'برند ظظظ و خودرو طططrnمتن',
            'part_description' => 'ظظظ طططrn',
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame('برند هیوندای و خودرو سانتافهمتن', $data['car']->description);
        $this->assertSame('هیوندای سانتافه', $data['part']->description);
    }

    public function test_it_leaves_null_descriptions_unchanged(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph([
            'car_description' => null,
            'part_description' => null,
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertNull($data['car']->description);
        $this->assertNull($data['part']->description);
    }

    public function test_it_builds_title_with_text_model_name(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph([
            'model_name' => 'نیو',
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame('طبق هیوندای سانتافه نیو', $data['title']);
    }

    public function test_it_builds_title_with_numeric_model_year(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph([
            'model_name' => '1402',
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame('طبق هیوندای سانتافه سال 1402', $data['title']);
    }

    public function test_it_builds_repair_locator_context_when_part_has_repair_category(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $repairCategory = RepairCategory::create(['name' => 'جلوبندی']);
        $part->repairCategories()->attach($repairCategory);
        $part->load('repairCategories');

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertNotNull($data['repairLocator']);
        $this->assertSame('جلوبندی', $data['repairLocator']['category']->name);
        $this->assertSame('سانتافه', $data['repairLocator']['carName']);
        $this->assertSame(
            'مشاهده خدمات جلوبندی سانتافه در محدوده شما',
            $data['repairLocator']['buttonLabel'],
        );
    }

    public function test_it_builds_repair_cards_with_calculated_cost(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph([
            'wage_strike' => 2,
        ]);

        $repairCategory = RepairCategory::create(['name' => 'جلوبندی']);
        $wage = Wage::create([
            'name' => 'تعویض طبق',
            'variable' => 100,
            'coefficient' => 1.5,
        ]);

        $part->repairCategories()->attach($repairCategory);
        $part->wages()->attach($wage);
        $part->load(['repairCategories', 'wages']);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertNotEmpty($data['repairCards']);
        $this->assertSame('جلوبندی', $data['repairCards'][0]['type']);
        $this->assertSame('تعویض طبق', $data['repairCards'][0]['wage_name']);
        $this->assertSame(30000000, $data['repairCards'][0]['cost']);
    }

    public function test_it_limits_repair_cards_to_three(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        foreach (['A', 'B', 'C', 'D'] as $name) {
            $part->repairCategories()->attach(
                RepairCategory::create(['name' => "دسته {$name}"]),
            );
            $part->wages()->attach(
                Wage::create(['name' => "اجرت {$name}", 'variable' => 10, 'coefficient' => 1]),
            );
        }

        $part->load(['repairCategories', 'wages']);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertCount(3, $data['repairCards']);
    }

    public function test_it_loads_shops_linked_directly_to_part(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $linkedShop = Shop::create([
            'name' => 'فروشگاه مستقیم',
            'slug' => 'direct-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $linkedShop->parts()->attach($part);

        $unlinkedShop = Shop::create([
            'name' => 'فروشگاه دیگر',
            'slug' => 'other-shop',
            'order' => 2,
        ]);
        $unlinkedShop->partsCategories()->attach($part->parts_category_id);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertCount(1, $data['shops']);
        $this->assertSame('direct-shop', $data['shops']->first()->slug);
    }

    public function test_it_falls_back_to_company_shops_when_part_has_no_direct_shops(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $companyShop = Shop::create([
            'name' => 'فروشگاه شرکت',
            'slug' => 'company-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $company->shops()->attach($companyShop);
        $company->shops->first()->images()->create([
            'type' => ImageType::Logo,
            'path' => 'cover.jpg',
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertCount(1, $data['shops']);
        $this->assertSame('company-shop', $data['shops']->first()->slug);
    }

    public function test_if_shop_has_no_logo_it_wont_be_retrieved(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $companyShop = Shop::create([
            'name' => 'فروشگاه شرکت',
            'slug' => 'company-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $company->shops()->attach($companyShop);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertCount(0, $data['shops']);
    }

    public function test_it_sanitizes_shop_descriptions(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $shop = Shop::create([
            'name' => 'فروشگاه',
            'slug' => 'shop',
            'description' => 'ظظظ طططrn',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $company->shops()->attach($shop);
        $company->shops->first()->images()->create([
            'type' => ImageType::Logo,
            'path' => 'cover.jpg',
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame('هیوندای سانتافه', $data['shops']->first()->description);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: Company, 1: Car, 2: CarModel, 3: Part}
     */
    private function seedProductGraph(array $overrides = []): array
    {
        $company = Company::create([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'country' => 'کره',
            'wage_strike' => $overrides['wage_strike'] ?? 1,
        ]);

        $car = Car::create([
            'name' => 'سانتافه',
            'slug' => 'santafe',
            'company_id' => $company->id,
            'description' => $overrides['car_description'] ?? null,
        ]);

        $car->company()->associate($company);

        $model = CarModel::create([
            'name' => $overrides['model_name'] ?? 'نیو',
            'slug' => 'new',
        ]);
        $car->models()->attach($model);

        $category = PartsCategory::create(['name' => 'جلوبندی']);

        $part = Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $category->id,
            'description' => $overrides['part_description'] ?? null,
        ]);

        return [$company, $car, $model, $part];
    }
}
