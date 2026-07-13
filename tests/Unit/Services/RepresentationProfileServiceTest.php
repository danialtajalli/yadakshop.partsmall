<?php

namespace Tests\Unit\Services;

use App\Models\City;
use App\Models\Company;
use App\Models\Representation;
use App\Models\State;
use App\Services\RepresentationProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepresentationProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private RepresentationProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RepresentationProfileService;
    }

    public function test_it_returns_representation_profile_page_data(): void
    {
        $representation = $this->createRepresentation();

        $data = $this->service->getProfilePageData($representation->slug);

        $this->assertSame(
            ['representation', 'title', 'serviceTypes', 'contacts', 'socialLinks'],
            array_keys($data),
        );
        $this->assertSame('نمایندگی تست', $data['title']);
        $this->assertSame(['فروش خودرو', 'خدمات پس از فروش'], $data['serviceTypes']);
        $this->assertSame('02112345678', $data['contacts'][0]['value']);
    }

    public function test_it_throws_not_found_for_unknown_slug(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->getProfilePageData('missing-representation');
    }

    private function createRepresentation(): Representation
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);
        $company = Company::create(['name' => 'هیوندای', 'slug' => 'hyundai', 'wage_strike' => 2.5]);

        return Representation::create([
            'name' => 'نمایندگی تست',
            'slug' => 'test-representation',
            'telephone' => '۰۲۱۱۲۳۴۵۶۷۸',
            'company_id' => $company->id,
            'city_id' => $city->id,
            'service_type' => 'فروش خودرو,خدمات پس از فروش',
        ]);
    }
}
