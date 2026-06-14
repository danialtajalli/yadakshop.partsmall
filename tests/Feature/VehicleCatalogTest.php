<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\ModelCategory;
use App\Models\Part;
use App\Models\PartsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_companies_index_lists_all_companies(): void
    {
        $this->seedGraph();

        $this->get(route('companies.index'))
            ->assertOk()
            ->assertViewIs('catalog.companies')
            ->assertSee('هیوندای', false)
            ->assertSee(route('cars.index', ['company' => 'hyundai']), false);
    }

    public function test_cars_index_filters_by_company(): void
    {
        $this->seedGraph();

        $this->get(route('cars.index', ['company' => 'hyundai']))
            ->assertOk()
            ->assertViewIs('catalog.cars')
            ->assertSee('سانتافه', false)
            ->assertSee('car/hyundai', false);
    }

    public function test_models_index_filters_by_car(): void
    {
        $this->seedGraph();

        $this->get(route('models.index', ['company' => 'hyundai', 'car' => 'santafe']))
            ->assertOk()
            ->assertViewIs('catalog.models')
            ->assertSee('لفظ', false)
            ->assertSee('نیو', false)
            ->assertSee(route('car.parts.vehicle', [
                'company' => 'hyundai',
                'car' => 'santafe',
                'model' => 'new',
            ]), false);
    }

    public function test_parts_index_links_to_product_when_vehicle_context_is_set(): void
    {
        $this->seedGraph();

        $response = $this->get(route('car.parts.vehicle', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
        ]));

        $response->assertOk();
        $response->assertViewIs('catalog.parts');
        $response->assertSee(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]), false);
    }

    public function test_parts_index_links_to_part_show_without_vehicle_context(): void
    {
        $this->seedGraph();

        $this->get(route('car.parts'))
            ->assertOk()
            ->assertSee(route('part.show', 'arm'), false);
    }

    public function test_car_parts_page_uses_catalog_breadcrumbs(): void
    {
        $this->seedGraph();

        $this->get(route('car.parts.vehicle', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
        ]))
            ->assertOk()
            ->assertSee(route('companies.index'), false)
            ->assertSee(route('cars.index', ['company' => 'hyundai']), false);
    }

    private function seedGraph(): void
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

        $category = ModelCategory::query()->where('slug', 'term')->firstOrFail();

        $model = CarModel::create([
            'name' => 'نیو',
            'slug' => 'new',
            'category_id' => $category->id,
        ]);
        $car->models()->attach($model);

        $category = PartsCategory::create(['name' => 'جلوبندی']);

        Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $category->id,
        ]);
    }
}
