<?php

namespace Tests\Unit\Services;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\Company;
use App\Models\Part;
use App\Models\PartsCategory;
use App\Services\PartPageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PartPageServiceTest extends TestCase
{
    use RefreshDatabase;

    private PartPageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PartPageService;
    }

    public function test_it_sanitizes_part_description_placeholders(): void
    {
        $category = PartsCategory::create(['name' => 'جلوبندی']);
        $part = Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'description' => 'یکی از قطعات xxx می باشد.',
            'parts_category_id' => $category->id,
        ]);

        $data = $this->service->getPartPageData('arm', Request::create('/part/arm'));

        $this->assertSame('یکی از قطعات جلوبندی می باشد.', $data['part']->description);
    }

    public function test_it_filters_and_paginates_vehicle_applications(): void
    {
        $part = $this->seedVehicles();

        $filtered = $this->service->getPartPageData('arm', Request::create('/part/arm', 'GET', ['q' => 'سانتافه']));
        $this->assertSame(1, $filtered['vehicleApplications']->total());
        $this->assertSame('طبق هیوندای سانتافه نیو', $filtered['vehicleApplications']->first()['label']);

        foreach (range(1, 61) as $index) {
            $car = Car::create([
                'name' => "خودرو {$index}",
                'slug' => "car-{$index}",
                'company_id' => Company::first()->id,
            ]);
            $model = CarModel::create([
                'name' => "مدل {$index}",
                'slug' => "model-{$index}",
            ]);
            $car->models()->attach($model);
        }

        $paginated = $this->service->getPartPageData('arm', Request::create('/part/arm', 'GET', ['page' => 2]));
        $this->assertSame(63, $paginated['vehicleApplications']->total());
        $this->assertCount(3, $paginated['vehicleApplications']->items());
    }

    private function seedVehicles(): Part
    {
        $company = Company::create([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'wage_strike' => 2.5,
        ]);

        $car = Car::create([
            'name' => 'سانتافه',
            'slug' => 'santafe',
            'company_id' => $company->id,
        ]);

        $otherCar = Car::create([
            'name' => 'النترا',
            'slug' => 'elantra',
            'company_id' => $company->id,
        ]);

        $model = CarModel::create(['name' => 'نیو', 'slug' => 'new']);
        $otherModel = CarModel::create(['name' => 'قدیم', 'slug' => 'old']);

        $car->models()->attach($model);
        $otherCar->models()->attach($otherModel);

        $category = PartsCategory::create(['name' => 'جلوبندی']);

        return Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $category->id,
        ]);
    }
}
