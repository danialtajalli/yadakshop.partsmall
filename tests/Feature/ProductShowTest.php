<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Models\RepairCategory;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_show_returns_successful_response_for_valid_slugs(): void
    {
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
        $response->assertSee('مشاهده پروفایل فروشگاه', false);
        $response->assertSee('اطلاعات تماس', false);
        $response->assertSee('فروشگاه شما میتواند اینجا باشد', false);
        $response->assertSee('ثبت نام', false);
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
        $response->assertSee('مشاهده خدمات جلوبندی سانتافه در محدوده شما', false);
        $response->assertSee('name="specialization_id"', false);
        $response->assertSee('value="'.$repairCategory->id.'"', false);
        $response->assertSee(route('repair-shops.index'), false);
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

    /**
     * @return array{0: Company, 1: Car, 2: CarModel, 3: Part}
     */
    private function seedProductGraph(): array
    {
        $company = Company::create([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'country' => 'کره',
            'wage_strike' => 2.5,
        ]);

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
            'name' => 'فروشگاه تست',
            'slug' => 'test-shop',
            'show_under_product' => true,
            'order' => 1,
        ]);
        $shop->parts()->attach($part);

        return [$company, $car, $model, $part];
    }
}
