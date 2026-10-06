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
    public function test_part_links_respect_vehicle_scope_and_allow_unscoped_shops(int $companyId): void
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

        $this->assertSame([$matchingShop->id, $companylessShop->id], $data['shops']->pluck('id')->all());
    }

    public static function companyIds(): array
    {
        return ['kia' => [1], 'hyundai' => [2], 'other_company' => [10]];
    }

    public function test_it_includes_company_shops_without_part_or_category_selections(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);

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

        $this->assertSame([$companyShop->id], $data['shops']->pluck('id')->all());
    }

    public function test_ineligible_part_shops_do_not_hide_vehicle_wide_shops(): void
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

    public function test_vehicle_wide_shops_do_not_require_a_logo(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);

        $companyShop = Shop::create([
            'name' => 'فروشگاه شرکت',
            'slug' => 'company-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $company->shops()->attach($companyShop);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([$companyShop->id], $data['shops']->pluck('id')->all());
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
        $shop->parts()->attach($part);
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

        $pinnedShops = collect([409, 4, 6])->map(function (int $id) use ($part): Shop {
            $shop = new Shop([
                'name' => "فروشگاه {$id}",
                'slug' => "pinned-shop-{$id}",
                'show_under_product' => true,
                'order' => 99,
            ]);
            $shop->id = $id;
            $shop->save();
            $shop->parts()->attach($part);

            return $shop;
        });

        $company->shops()->attach([409, 4, 6]);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([409, 4, 6, $regularShop->id], $data['shops']->pluck('id')->all());
        $this->assertTrue($pinnedShops->every(fn (Shop $shop): bool => $data['shops']->contains('id', $shop->id)));
    }

    #[DataProvider('priorityAnchorScopes')]
    public function test_company_priority_applies_with_or_without_eligible_shop_409(string $reason): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);
        $anchor = new Shop([
            'name' => 'Priority anchor',
            'slug' => 'priority-anchor',
            'show_under_product' => $reason !== 'hidden',
            'order' => 10,
        ]);
        $anchor->id = 409;
        $anchor->save();
        $anchor->companies()->attach($company);

        if ($reason === 'wrong_product') {
            $otherCategory = PartsCategory::create(['name' => 'Other category']);
            $anchor->partsCategories()->attach($otherCategory);
        } elseif ($reason === 'hidden') {
            $anchor->parts()->attach($part);
        }

        foreach ([500 => 1, 501 => 2, 6 => 3, 4 => 4] as $id => $order) {
            $shop = new Shop([
                'name' => 'Shop '.$id,
                'slug' => 'ordered-shop-'.$id,
                'show_under_product' => true,
                'order' => $order,
            ]);
            $shop->id = $id;
            $shop->save();
            $shop->parts()->attach($part);
            $shop->companies()->attach($company);
        }

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $expectedIds = $reason === 'no_product' ? [409, 4, 6, 500, 501] : [4, 6, 500, 501];
        $this->assertSame($expectedIds, $data['shops']->pluck('id')->all());
    }

    public static function priorityAnchorScopes(): array
    {
        return [
            'hidden anchor' => ['hidden'],
            'anchor has wrong product category' => ['wrong_product'],
            'anchor has no product selections' => ['no_product'],
        ];
    }

    #[DataProvider('unlinkedPriorityAnchorScopes')]
    public function test_an_eligible_shop_409_does_not_pin_for_an_unlinked_company(string $scope): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);
        $otherCompany = Company::create(['name' => 'Other company', 'slug' => 'other-company']);

        foreach ([500 => 1, 6 => 2, 4 => 3, 409 => 4] as $id => $order) {
            $shop = new Shop([
                'name' => 'Shop '.$id,
                'slug' => 'unlinked-shop-'.$id,
                'show_under_product' => true,
                'order' => $order,
            ]);
            $shop->id = $id;
            $shop->save();
            $shop->parts()->attach($part);

            if ($id !== 409) {
                $shop->companies()->attach($company);
            } elseif ($scope === 'other_company') {
                $shop->companies()->attach($otherCompany);
                $shop->cars()->attach($car);
            }
        }

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([500, 6, 4, 409], $data['shops']->pluck('id')->all());
    }

    public static function unlinkedPriorityAnchorScopes(): array
    {
        return [
            'anchor has no companies' => ['none'],
            'anchor matches car but belongs to another company' => ['other_company'],
        ];
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

    #[DataProvider('shopProductScopes')]
    public function test_shop_vehicle_and_product_selections(string $vehicleScope, string $productScope, bool $expected, bool $hasLogo): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);
        $otherCompany = Company::create(['name' => 'Other company', 'slug' => 'other-company']);
        $otherCar = Car::create(['name' => 'Other car', 'slug' => 'other-car', 'company_id' => $company->id]);
        $otherCategory = PartsCategory::create(['name' => 'Other category']);
        $otherPart = Part::create(['name' => 'Other part', 'slug' => 'other-part', 'parts_category_id' => $otherCategory->id]);
        $shop = new Shop(['name' => 'Selected shop', 'slug' => 'selected-shop', 'show_under_product' => true]);
        $shop->id = 500;
        $shop->save();
        if ($hasLogo) {
            $shop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.jpg']);
        }

        if (in_array($vehicleScope, ['company', 'company_car_wrong'], true)) {
            $shop->companies()->attach($company);
        } elseif (in_array($vehicleScope, ['company_wrong', 'company_wrong_car'], true)) {
            $shop->companies()->attach($otherCompany);
        }

        if (in_array($vehicleScope, ['car', 'company_wrong_car'], true)) {
            $shop->cars()->attach($car);
        } elseif (in_array($vehicleScope, ['car_wrong', 'company_car_wrong'], true)) {
            $shop->cars()->attach($otherCar);
        }

        if (in_array($productScope, ['part', 'part_category_wrong'], true)) {
            $shop->parts()->attach($part);
        } elseif (in_array($productScope, ['part_wrong', 'category_part_wrong'], true)) {
            $shop->parts()->attach($otherPart);
        }

        if (in_array($productScope, ['category', 'category_part_wrong'], true)) {
            $shop->partsCategories()->attach($part->parts_category_id);
        } elseif (in_array($productScope, ['category_wrong', 'part_category_wrong'], true)) {
            $shop->partsCategories()->attach($otherCategory);
        }

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame($expected ? [$shop->id] : [], $data['shops']->pluck('id')->all());
    }

    public static function shopProductScopes(): array
    {
        $cases = [];

        foreach (['company', 'car', 'none', 'company_wrong', 'car_wrong', 'company_wrong_car', 'company_car_wrong'] as $vehicleScope) {
            foreach (['none', 'part', 'category', 'part_wrong', 'category_wrong', 'part_category_wrong', 'category_part_wrong'] as $productScope) {
                $vehicleMatches = ! in_array($vehicleScope, ['company_wrong', 'car_wrong'], true);
                $productMatches = in_array($productScope, ['part', 'category', 'part_category_wrong', 'category_part_wrong'], true)
                    || ($productScope === 'none' && $vehicleScope !== 'none');

                foreach ([true, false] as $hasLogo) {
                    $label = $hasLogo ? 'with_logo' : 'without_logo';
                    $cases[$vehicleScope.'_'.$productScope.'_'.$label] = [$vehicleScope, $productScope, $vehicleMatches && $productMatches, $hasLogo];
                }
            }
        }

        return $cases;
    }

    public function test_vehicle_wide_and_part_specific_shops_are_both_included(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);
        $specific = Shop::create(['name' => 'Specific', 'slug' => 'specific', 'show_under_product' => true]);
        $specific->parts()->attach($part);
        $broad = Shop::create(['name' => 'Broad', 'slug' => 'broad', 'show_under_product' => true]);
        $broad->cars()->attach($car);
        $broad->images()->create(['type' => ImageType::Logo, 'path' => 'logo.jpg']);
        $hidden = Shop::create(['name' => 'Hidden', 'slug' => 'hidden', 'show_under_product' => false]);
        $hidden->partsCategories()->attach($part->parts_category_id);

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([$specific->id, $broad->id], $data['shops']->pluck('id')->all());
    }

    public function test_pin_does_not_override_part_category_or_car_selections(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => 10]);
        $otherCar = Car::create(['name' => 'Other car', 'slug' => 'other-car', 'company_id' => $company->id]);
        $otherCategory = PartsCategory::create(['name' => 'Other category']);

        foreach ([409, 4, 6] as $id) {
            $shop = new Shop(['name' => 'Pinned '.$id, 'slug' => 'pinned-'.$id, 'show_under_product' => true]);
            $shop->id = $id;
            $shop->save();

            if ($id === 4) {
                $shop->companies()->attach($company);
                $shop->partsCategories()->attach($otherCategory);
            } else {
                $shop->cars()->attach($id === 6 ? $otherCar : $car);
                $shop->parts()->attach($part);
            }
        }

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame([409], $data['shops']->pluck('id')->all());
    }

    #[DataProvider('curatedShopLists')]
    public function test_curated_shop_lists_override_all_matching_and_pinning_rules(int $companyId, string $scope, array $expectedIds): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(['company_id' => $companyId]);

        foreach ([1, 2, 3, 4, 6, 409, 411, 412, 413, 500] as $id) {
            $shop = new Shop(['name' => 'Shop '.$id, 'slug' => 'shop-'.$id, 'show_under_product' => true]);
            $shop->id = $id;
            $shop->save();

            if ($scope === 'category') {
                $shop->partsCategories()->attach($part->parts_category_id);
            } else {
                $shop->parts()->attach($part);
            }

            if ($scope === 'company') {
                $shop->companies()->attach($company);
            } elseif ($scope === 'car') {
                $shop->cars()->attach($car);
            }
        }

        $data = $this->service->getProductPageData($company, $car, $model, $part);

        $this->assertSame($expectedIds, $data['shops']->pluck('id')->all());
    }

    public static function curatedShopLists(): array
    {
        $cases = [];

        foreach ([
            1 => [1, 2, 3, 411, 412],
            2 => [1, 2, 3, 411, 413],
            10 => [409, 4, 6, 1, 2, 3, 411, 412, 413, 500],
        ] as $companyId => $expectedIds) {
            foreach (['company', 'car', 'part', 'category'] as $scope) {
                $orderedIds = $companyId === 10 && $scope !== 'company'
                    ? [1, 2, 3, 4, 6, 409, 411, 412, 413, 500]
                    : $expectedIds;
                $cases[$companyId.'_'.$scope] = [$companyId, $scope, $orderedIds];
            }
        }

        return $cases;
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
