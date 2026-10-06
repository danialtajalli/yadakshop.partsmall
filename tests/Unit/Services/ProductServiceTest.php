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
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['https://api.qrserver.com/*' => Http::response('', 503)]);

        $this->service = new ProductService;
    }

    public function test_it_returns_expected_page_data_structure(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame(
            ['company', 'car', 'model', 'part', 'repairCards', 'shops', 'shopFilterStates', 'shopFilterCitiesByState', 'title', 'metaDescription', 'breadcrumbs', 'repairLocators', 'relatedProducts', 'telegramTitle', 'telegramUrl', 'telegramName', 'signupUrl'],
            array_keys($data),
        );
        $this->assertSame('به گروه تلگرام هیوندای سانتافه سواران بپیوندید', $data['telegramTitle']);
        $this->assertSame('https://t.me/hyundai_saravan_partsmall', $data['telegramUrl']);
        $this->assertSame(route('page.show', 'register'), $data['signupUrl']);
        $this->assertNull($data['repairLocators']);
        $this->assertSame($company->id, $data['company']->id);
        $this->assertSame($car->id, $data['car']->id);
        $this->assertSame($model->id, $data['model']->id);
        $this->assertSame($part->id, $data['part']->id);
    }

    public function test_it_sanitizes_car_and_part_descriptions(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph([
            'car_description' => 'برند ظظظ و خودرو ططط مدل مممrnمتن',
            'part_description' => 'ظظظ ططط مممrn',
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame('برند هیوندای و خودرو سانتافه مدل نیومتن', $data['car']->description);
        $this->assertSame('هیوندای سانتافه نیو', $data['part']->description);
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

        $this->assertNotNull($data['repairLocators']);
        $this->assertSame('جلوبندی', $data['repairLocators'][0]['category']->name);
        $this->assertSame('سانتافه', $data['repairLocators'][0]['carName']);
        $this->assertSame(
            'مشاهده خدمات جلوبندی سانتافه در محدوده شما',
            $data['repairLocators'][0]['buttonLabel'],
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
        $linkedShop->companies()->attach($company);

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

    #[DataProvider('companyIds')]
    public function test_part_links_do_not_bypass_company_associations(int $companyId): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => $companyId]);
        $otherCompany = new Company(['name' => 'Other company', 'slug' => 'other-company']);
        $otherCompany->id = $companyId === 1 ? 2 : 1;
        $otherCompany->save();

        $wrongCompanyShop = Shop::create([
            'name' => 'Wrong company shop', 'slug' => 'wrong-company-shop', 'show_under_product' => true,
        ]);
        $wrongCompanyShop->parts()->attach($part);
        $wrongCompanyShop->companies()->attach($otherCompany);
        $matchingShop = Shop::create([
            'name' => 'Matching shop', 'slug' => 'matching-shop', 'show_under_product' => true,
        ]);
        $matchingShop->parts()->attach($part);
        $matchingShop->companies()->attach($company);
        $companylessShop = Shop::create([
            'name' => 'Companyless shop', 'slug' => 'companyless-shop', 'show_under_product' => true,
        ]);
        $companylessShop->parts()->attach($part);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([$matchingShop->id], $data['shops']->pluck('id')->all());
    }

    public static function companyIds(): array
    {
        return ['kia' => [1], 'hyundai' => [2], 'other_company' => [10]];
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

    public function test_ineligible_part_shops_do_not_prevent_company_fallback(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);
        $otherCompany = Company::create(['name' => 'Other company', 'slug' => 'other-company']);
        $wrongShop = Shop::create([
            'name' => 'Wrong shop', 'slug' => 'wrong-shop', 'show_under_product' => true,
        ]);
        $wrongShop->parts()->attach($part);
        $wrongShop->companies()->attach($otherCompany);
        $fallbackShop = Shop::create([
            'name' => 'Fallback shop', 'slug' => 'fallback-shop', 'show_under_product' => true,
        ]);
        $fallbackShop->companies()->attach($company);
        $fallbackShop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.jpg']);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([$fallbackShop->id], $data['shops']->pluck('id')->all());
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
            'description' => 'ظظظ ططط مممrn',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $company->shops()->attach($shop);
        $company->shops->first()->images()->create([
            'type' => ImageType::Logo,
            'path' => 'cover.jpg',
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame('هیوندای سانتافه نیو', $data['shops']->first()->description);
    }

    public function test_it_loads_related_products_for_same_car_and_model(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $sameCategoryPart = Part::create([
            'name' => 'سیبک',
            'slug' => 'ball-joint',
            'parts_category_id' => $part->parts_category_id,
        ]);

        $otherCategory = PartsCategory::create(['name' => 'موتور']);
        $otherCategoryPart = Part::create([
            'name' => 'فیلتر روغن',
            'slug' => 'oil-filter',
            'parts_category_id' => $otherCategory->id,
        ]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertCount(2, $data['relatedProducts']);
        $this->assertSame('ball-joint', $data['relatedProducts']->first()->slug);
        $this->assertSame('oil-filter', $data['relatedProducts']->last()->slug);
        $this->assertSame(
            'سیبک هیوندای سانتافه نیو',
            $data['relatedProducts']->first()->title,
        );
        $this->assertSame(
            route('product.show', [
                'company' => 'hyundai',
                'car' => 'santafe',
                'model' => 'new',
                'part' => 'ball-joint',
            ]),
            $data['relatedProducts']->first()->url,
        );
        $this->assertFalse($data['relatedProducts']->contains('id', $part->id));
        $this->assertTrue($data['relatedProducts']->contains('id', $sameCategoryPart->id));
        $this->assertTrue($data['relatedProducts']->contains('id', $otherCategoryPart->id));
    }

    public function test_it_pins_priority_shops_for_companies_linked_to_shop_409(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);

        $regularShop = Shop::create([
            'name' => 'فروشگاه عادی',
            'slug' => 'regular-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $regularShop->parts()->attach($part);
        $regularShop->companies()->attach($company);

        $pinnedShops = collect([409, 4, 6])->map(function (int $id): Shop {
            $shop = new Shop([
                'name' => "فروشگاه {$id}",
                'slug' => "pinned-shop-{$id}",
                'show_under_product' => true,
                'order' => 99,
            ]);
            $shop->id = $id;
            $shop->save();

            return $shop;
        });

        $company->shops()->attach([409, 4, 6]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([409, 4, 6, $regularShop->id], $data['shops']->pluck('id')->all());
        $this->assertTrue($pinnedShops->every(fn (Shop $shop): bool => $data['shops']->contains('id', $shop->id)));
    }

    public function test_priority_shops_must_each_belong_to_the_company(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);
        $otherCompany = Company::create(['name' => 'Other company', 'slug' => 'other-company']);

        foreach ([409, 4, 6] as $id) {
            $shop = new Shop([
                'name' => 'Priority shop '.$id, 'slug' => 'priority-shop-'.$id, 'show_under_product' => true,
            ]);
            $shop->id = $id;
            $shop->save();
            $shop->parts()->attach($part);
            $shop->companies()->attach($id === 6 ? $otherCompany : $company);
        }

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([409, 4], $data['shops']->pluck('id')->all());
    }

    public function test_it_does_not_pin_priority_shops_when_company_is_not_linked_to_shop_409(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);

        $anchor = new Shop([
            'name' => 'فروشگاه ۴۰۹',
            'slug' => 'shop-409',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $anchor->id = 409;
        $anchor->save();

        $regularShop = Shop::create([
            'name' => 'فروشگاه عادی',
            'slug' => 'regular-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $regularShop->parts()->attach($part);
        $regularShop->companies()->attach($company);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([$regularShop->id], $data['shops']->pluck('id')->all());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: Company, 1: Car, 2: CarModel, 3: Part}
     */
    private function seedProductGraph(array $overrides = []): array
    {
        $company = new Company([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'country' => 'کره',
            'wage_strike' => $overrides['wage_strike'] ?? 1,
        ]);
        if (isset($overrides['company_id'])) {
            $company->id = $overrides['company_id'];
        }
        $company->save();

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
