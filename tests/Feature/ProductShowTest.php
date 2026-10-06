<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\RepairCategory;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['https://api.qrserver.com/*' => Http::response('', 503)]);
    }

    public function test_product_show_excludes_part_shops_not_linked_to_its_company(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(10);
        $otherCompany = Company::create(['name' => 'Other company', 'slug' => 'other-company']);
        $wrongShop = Shop::create([
            'confirmed' => true,
            'name' => 'Wrong company shop', 'slug' => 'wrong-company-shop', 'show_under_product' => true,
        ]);
        $wrongShop->parts()->attach($part);
        $wrongShop->companies()->attach($otherCompany);

        $response = $this->get(route('product.show', [
            'company' => $company->slug, 'car' => $car->slug, 'model' => $model->slug, 'part' => $part->slug,
        ]));

        $response->assertOk();
        $response->assertViewHas('shops', fn ($shops): bool => $shops->pluck('slug')->all() === ['test-shop']);
        $response->assertDontSee('Wrong company shop');
    }

    public function test_product_show_includes_car_category_and_global_part_shops(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph(10);
        $otherCompany = Company::create(['name' => 'Other company', 'slug' => 'other-company']);
        $otherCar = Car::create(['name' => 'Other car', 'slug' => 'other-car', 'company_id' => $company->id]);
        $carShop = Shop::create(['confirmed' => true, 'name' => 'Car category shop', 'slug' => 'car-category-shop', 'show_under_product' => true]);
        $carShop->cars()->attach($car);
        $carShop->companies()->attach($otherCompany);
        $carShop->partsCategories()->attach($part->parts_category_id);
        $globalShop = Shop::create(['confirmed' => true, 'name' => 'Global part shop', 'slug' => 'global-part-shop', 'show_under_product' => true]);
        $globalShop->parts()->attach($part);
        $wrongCarShop = Shop::create(['confirmed' => true, 'name' => 'Wrong car shop', 'slug' => 'wrong-car-shop', 'show_under_product' => true]);
        $wrongCarShop->cars()->attach($otherCar);
        $wrongCarShop->parts()->attach($part);

        foreach ([411 => 'company', 412 => 'car'] as $id => $scope) {
            $vehicleOnlyShop = new Shop([
                'confirmed' => true,
                'name' => 'Vehicle-only '.$scope.' shop',
                'slug' => 'vehicle-only-'.$scope.'-shop',
                'show_under_product' => true,
            ]);
            $vehicleOnlyShop->id = $id;
            $vehicleOnlyShop->save();

            if ($scope === 'company') {
                $vehicleOnlyShop->companies()->attach($company);
            } else {
                $vehicleOnlyShop->cars()->attach($car);
            }
        }

        $response = $this->get(route('product.show', [
            'company' => $company->slug, 'car' => $car->slug, 'model' => $model->slug, 'part' => $part->slug,
        ]));

        $response->assertOk();
        $response->assertViewHas('shops', fn ($shops): bool => $shops->pluck('slug')->all() === [
            'test-shop', 'car-category-shop', 'global-part-shop',
            'vehicle-only-company-shop', 'vehicle-only-car-shop',
        ]);
        $response->assertDontSee('Wrong car shop');
        $response->assertSee('Vehicle-only company shop');
        $response->assertSee('Vehicle-only car shop');
    }

    public function test_product_show_returns_successful_response_for_valid_slugs(): void
    {
        $this->travelTo(Carbon::parse('2026-08-15 10:42:00', config('app.timezone')));
        $this->seedProductGraph();

        $response = $this->get(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]));

        $response->assertOk();
        $response->assertViewIs('product.show');
        $response->assertViewHas('title', 'طبق هیوندای سانتافه نیو');
        $response->assertSee('طبق', false);
        $response->assertSee('هیوندای', false);
        $response->assertSee('سانتافه', false);
        $response->assertSee('لیست فروشگاه ها', false);
        $response->assertSee('آخرین بروزرسانی قیمت', false);
        $response->assertSee('پنج‌شنبه، ۲۲ مرداد ۱۴۰۵', false);
        $response->assertDontSee('ساعت ۱۰:۴۲', false);
        $this->assertSame(2, substr_count($response->getContent(), 'آخرین بروزرسانی قیمت'));
        $response->assertSee('پیش از خرید، موجودی و قیمت نهایی این قطعه را از فروشنده بپرسید', false);
        $response->assertDontSee('موجودی لحظه‌ای', false);
        $response->assertSee('اطلاعات تماس', false);
        $response->assertSee('فروشگاه شما میتواند اینجا باشد', false);
        $response->assertSee('ثبت نام', false);
        $response->assertSee('قطعات خود را در پارتس‌مال بفروشید', false);
        $response->assertSee('به گروه تلگرام هیوندای سانتافه سواران بپیوندید', false);
        $response->assertSee('https://t.me/hyundai_saravan_partsmall', false);
        $response->assertSee(route('page.show', ['slug' => 'register']), false);
    }

    public function test_product_show_displays_repair_locator_when_part_has_repair_category(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        $repairCategory = RepairCategory::create(['name' => 'جلوبندی']);
        $part->repairCategories()->attach($repairCategory);

        $response = $this->get(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]));

        $response->assertOk();
        $response->assertSee('مشاهده تعمیرگاه‌ها و اجرت‌ها', false);
        $response->assertSee('انتخاب محدوده', false);
        $response->assertSee('name="specialization_id"', false);
        $response->assertSee('value="'.$repairCategory->id.'"', false);
        $response->assertSee(route('repair-shops.index'), false);
    }

    public function test_product_show_uses_yesterday_update_time_when_yesterday_is_not_off(): void
    {
        $this->travelTo(Carbon::parse('2026-08-16 10:42:00', config('app.timezone')));
        $this->seedProductGraph();

        $response = $this->get(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]));

        $response->assertOk();
        $response->assertSee('دیروز، ۲۴ مرداد ۱۴۰۵', false);
    }

    public function test_product_show_skips_configured_price_update_off_dates(): void
    {
        config(['partsmall.price_update_off_dates.jalali' => ['1405-05-24']]);
        $this->travelTo(Carbon::parse('2026-08-16 10:42:00', config('app.timezone')));
        $this->seedProductGraph();

        $response = $this->get(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]));

        $response->assertOk();
        $response->assertSee('پنج‌شنبه، ۲۲ مرداد ۱۴۰۵', false);
    }

    public function test_product_show_returns_not_found_for_unknown_company(): void
    {
        $this->seedProductGraph();

        $this->get(route('product.show', [
            'company' => 'unknown',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]))->assertNotFound();
    }

    public function test_product_show_returns_not_found_when_car_does_not_belong_to_company(): void
    {
        $this->seedProductGraph();

        Company::create([
            'name' => 'کیا',
            'slug' => 'kia',
            'country' => 'کره',
            'wage_strike' => 2.5,
        ]);

        $this->get(route('product.show', [
            'company' => 'kia',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]))->assertNotFound();
    }

    public function test_product_show_returns_not_found_when_model_is_not_linked_to_car(): void
    {
        $this->seedProductGraph();

        CarModel::create([
            'name' => 'قدیم',
            'slug' => 'old',
        ]);

        $this->get(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'old',
            'part' => 'arm',
        ]))->assertNotFound();
    }

    public function test_product_show_returns_not_found_for_unknown_part(): void
    {
        $this->seedProductGraph();

        $this->get(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'unknown',
        ]))->assertNotFound();
    }

    public function test_product_show_displays_related_products_for_same_car_and_model(): void
    {
        [$company, $car, $model, $part] = $this->seedProductGraph();

        Part::create([
            'name' => 'سیبک',
            'slug' => 'ball-joint',
            'parts_category_id' => $part->parts_category_id,
        ]);

        $response = $this->get(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]));

        $response->assertOk();
        $response->assertSee('محصولات مرتبط', false);
        $response->assertSee('قطعات دیگر برای هیوندای سانتافه نیو', false);
        $response->assertSee('سیبک هیوندای سانتافه نیو', false);
        $response->assertSee(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'ball-joint',
        ]), false);
        $response->assertViewHas('relatedProducts', function ($relatedProducts) use ($part): bool {
            return $relatedProducts->count() === 1
                && $relatedProducts->first()->slug === 'ball-joint'
                && ! $relatedProducts->contains('id', $part->id);
        });
    }

    /**
     * @return array{0: Company, 1: Car, 2: CarModel, 3: Part}
     */
    private function seedProductGraph(int $companyId = 1): array
    {
        $company = new Company([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'country' => 'کره',
            'wage_strike' => 2.5,
        ]);
        $company->id = $companyId;
        $company->save();

        $car = Car::create([
            'name' => 'سانتافه',
            'slug' => 'santafe',
            'company_id' => $company->id,
        ]);

        $model = CarModel::create([
            'name' => 'نیو',
            'slug' => 'new',
        ]);
        $car->models()->attach($model);

        $category = PartsCategory::create(['name' => 'جلوبندی']);

        $part = Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $category->id,
        ]);

        $shop = Shop::create([
            'confirmed' => true,
            'name' => 'فروشگاه تست',
            'slug' => 'test-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $shop->parts()->attach($part);
        $shop->companies()->attach($company);

        return [$company, $car, $model, $part];
    }
}
