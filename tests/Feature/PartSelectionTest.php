<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_car_parts_page_returns_successful_response_for_valid_slugs(): void
    {
        $this->seedSelectionGraph();

        $response = $this->get(route('car.parts.vehicle', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
        ]));

        $response->assertOk();
        $response->assertViewIs('catalog.parts');
        $response->assertViewHas('title', 'لوازم یدکی هیوندای سانتافه نیو');
        $response->assertSee('جستجوی نام قطعه', false);
        $response->assertSee('طبق', false);
        $response->assertSee(route('product.show', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'new',
            'part' => 'arm',
        ]), false);
    }

    public function test_car_parts_page_returns_not_found_for_unknown_company(): void
    {
        $this->seedSelectionGraph();

        $this->get(route('car.parts.vehicle', [
            'company' => 'unknown',
            'car' => 'santafe',
            'model' => 'new',
        ]))->assertNotFound();
    }

    public function test_car_parts_page_returns_not_found_when_model_is_not_linked_to_car(): void
    {
        $this->seedSelectionGraph();

        CarModel::create([
            'name' => 'قدیم',
            'slug' => 'old',
        ]);

        $this->get(route('car.parts.vehicle', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => 'old',
        ]))->assertNotFound();
    }

    /**
     * @return array{0: Company, 1: Car, 2: CarModel, 3: Part}
     */
    private function seedSelectionGraph(): array
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

        return [$company, $car, $model, $part];
    }
}
