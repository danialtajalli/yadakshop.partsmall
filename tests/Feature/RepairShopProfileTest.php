<?php

namespace Tests\Feature;

use App\Enums\ImageType;
use App\Enums\LinkType;
use App\Enums\PhoneType;
use App\Models\RepairCategory;
use App\Models\RepairShop;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairShopProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_shop_profile_returns_successful_response(): void
    {
        $repairShop = $this->seedRepairShopProfileGraph();

        $response = $this->get($repairShop->profileUrl());

        $response->assertOk();
        $response->assertViewIs('repair-shop.show');
        $response->assertViewHas('title', 'تعمیرگاه آریا');
        $response->assertSee('تعمیرگاه آریا', false);
        $response->assertSee('تعمیر موتور و گیربکس', false);
        $response->assertSee('جلوبندی', false);
        $response->assertSee('02144556677', false);
        $response->assertSee('telegram', false);
        $response->assertSee('علی رضایی', false);
        $response->assertSee('موقعیت روی نقشه', false);
        $response->assertSee('data-location-map', false);
        $response->assertSee('leaflet@1.9.4', false);
    }

    public function test_repair_shop_profile_returns_not_found_for_unknown_id(): void
    {
        $this->get(route('repair-shop.profile', ['id' => 99999, 'slug' => 'unknown-repair-shop']))->assertNotFound();
    }

    public function test_repair_shop_profile_returns_not_found_when_slug_does_not_match(): void
    {
        $repairShop = $this->seedRepairShopProfileGraph();

        $this->get(route('repair-shop.profile', ['id' => $repairShop->id, 'slug' => 'wrong-slug']))->assertNotFound();
    }

    private function seedRepairShopProfileGraph(): RepairShop
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);

        $repairShop = RepairShop::create([
            'name' => 'تعمیرگاه آریا',
            'slug' => 'aria-repair',
            'responsible_person_name' => 'علی رضایی',
            'work_description' => 'تعمیر موتور و گیربکس',
            'description' => 'توضیحات تعمیرگاه',
            'state_id' => $state->id,
            'address' => 'تهران، خیابان نمونه',
            'latitude' => 35.68843735,
            'longitude' => 51.43004894,
        ]);

        $repairShop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);

        $repairShop->phones()->create([
            'phone_number' => '۰۲۱۴۴۵۵۶۶۷۷',
            'type' => PhoneType::Land,
        ]);

        $repairShop->links()->create([
            'name' => 'https://t.me/aria',
            'link_type' => LinkType::Telegram,
        ]);

        $category = RepairCategory::create(['name' => 'جلوبندی']);
        $repairShop->repairCategories()->attach($category);

        return $repairShop;
    }
}
