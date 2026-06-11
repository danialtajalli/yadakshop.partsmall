<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Company;
use App\Models\Representation;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepresentationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_representations_index_returns_successful_response(): void
    {
        $this->seedRepresentationGraph();

        $response = $this->get(route('representations.index'));

        $response->assertOk();
        $response->assertViewIs('listings.index');
        $response->assertViewHas('type', 'representation');
        $response->assertViewHas('title', 'نمایندگی‌ها');
        $response->assertSee('شركت آسان موتور', false);
        $response->assertSee('نمایندگی‌ها', false);
    }

    public function test_representations_index_filters_by_state_and_search(): void
    {
        [$state, $company] = $this->seedRepresentationGraph();

        Representation::create([
            'name' => 'نمایندگی دیگر',
            'slug' => 'other-rep',
            'company_id' => $company->id,
            'state_id' => $state->id,
            'service_type' => 'خدمات پس از فروش',
        ]);

        $this->get(route('representations.index', ['q' => 'آسان']))
            ->assertOk()
            ->assertSee('شركت آسان موتور', false)
            ->assertDontSee('نمایندگی دیگر', false);

        $this->get(route('representations.index', ['state_id' => $state->id]))
            ->assertOk()
            ->assertSee('شركت آسان موتور', false);
    }

    public function test_representation_profile_returns_successful_response(): void
    {
        $this->seedRepresentationGraph();

        $response = $this->get(route('representation.profile', 'asanmotor'));

        $response->assertOk();
        $response->assertViewIs('representation.show');
        $response->assertViewHas('title', 'شركت آسان موتور');
        $response->assertSee('شركت آسان موتور', false);
        $response->assertSee('فروش خودرو', false);
        $response->assertSee('تلفن ثابت', false);
        $response->assertSee('fa-solid fa-phone', false);
        $response->assertSee('هیوندای', false);
    }

    public function test_representation_profile_returns_not_found_for_unknown_slug(): void
    {
        $this->get(route('representation.profile', 'unknown-representation'))->assertNotFound();
    }

    /**
     * @return array{0: State, 1: Company}
     */
    private function seedRepresentationGraph(): array
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);
        $company = Company::create([
            'name' => 'هیوندای',
            'slug' => 'hyundai',
            'wage_strike' => 2.5,
        ]);

        Representation::create([
            'name' => 'شركت آسان موتور',
            'slug' => 'asanmotor',
            'responsible_person_name' => 'شركت آسان موتور',
            'telephone' => '۰۲۱۳۷۶۰۶۰۰۰',
            'company_id' => $company->id,
            'service_type' => 'فروش خودرو,خدمات پس از فروش',
            'state_id' => $state->id,
            'city_id' => $city->id,
            'address' => 'تهران، خیابان نمونه',
            'latitude' => 35.70626415,
            'longitude' => 51.24677896,
            'description' => 'ساعت کاری: شنبه تا چهارشنبه از 08:00 الی 17:00',
            'show_under_product' => true,
        ]);

        return [$state, $company];
    }
}
