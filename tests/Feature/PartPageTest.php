<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PartPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_part_page_returns_successful_response(): void
    {
        [$company, $car, $model, $part] = $this->seedGraph();

        $response = $this->get(route('part.show', $part->slug));

        $response->assertOk();
        $response->assertViewIs('part.show');
        $response->assertViewHas('title', 'طبق');
        $response->assertSee('خودروها و مدل‌های مرتبط', false);
        $response->assertSee('هیوندای سانتافه نیو', false);
        $response->assertSee(route('product.show', [
            'company' => $company->slug,
            'car' => $car->slug,
            'model' => $model->slug,
            'part' => $part->slug,
        ]), false);
        $response->assertSee('BreadcrumbList', false);
        $response->assertSee('from-gray-100', false);
    }

    public function test_part_page_supports_search_and_pagination_query_string(): void
    {
        [, , , $part] = $this->seedGraph();

        $this->get(route('part.show', ['part' => $part->slug, 'q' => 'سانتافه']))
            ->assertOk()
            ->assertSee('هیوندای سانتافه نیو', false);

        $this->get(route('part.show', ['part' => $part->slug, 'q' => 'ناموجود']))
            ->assertOk()
            ->assertSee('خودرویی با این نام یافت نشد.', false);
    }

    public function test_part_page_sanitizes_description_placeholders(): void
    {
        $category = PartsCategory::create(['name' => 'جلوبندی']);
        $part = Part::create([
            'name' => 'طبق',
            'slug' => 'arm-desc',
            'description' => 'یکی از قطعات xxx می باشد.',
            'parts_category_id' => $category->id,
        ]);

        $this->get(route('part.show', $part->slug))
            ->assertOk()
            ->assertSee('یکی از قطعات جلوبندی می باشد.', false);
    }

    public function test_home_page_links_to_part_page(): void
    {
        [, , , $part] = $this->seedGraph();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('part.show', $part->slug), false);
    }

    public function test_car_parts_page_links_to_part_page(): void
    {
        [, , $model, $part] = $this->seedGraph();

        $this->get(route('car.parts.vehicle', [
            'company' => 'hyundai',
            'car' => 'santafe',
            'model' => $model->slug,
        ]))
            ->assertOk()
            ->assertSee(route('product.show', [
                'company' => 'hyundai',
                'car' => 'santafe',
                'model' => $model->slug,
                'part' => $part->slug,
            ]), false);
    }

    /**
     * @return array{0: Company, 1: Car, 2: CarModel, 3: Part}
     */
    private function seedGraph(): array
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
            'description' => '<p>توضیحات طبق</p>',
            'parts_category_id' => $category->id,
        ]);

        return [$company, $car, $model, $part];
    }
}
