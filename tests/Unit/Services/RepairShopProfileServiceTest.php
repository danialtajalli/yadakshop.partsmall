<?php

namespace Tests\Unit\Services;

use App\Enums\ImageType;
use App\Models\RepairCategory;
use App\Models\RepairShop;
use App\Models\State;
use App\Services\RepairShopProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairShopProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private RepairShopProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RepairShopProfileService;
    }

    public function test_it_returns_repair_shop_profile_page_data(): void
    {
        $repairShop = $this->createRepairShopWithRelations();

        $data = $this->service->getProfilePageData($repairShop->slug);

        $this->assertSame(
            ['repairShop', 'title'],
            array_keys($data),
        );
        $this->assertSame('تعمیرگاه آریا', $data['title']);
        $this->assertSame($repairShop->id, $data['repairShop']->id);
        $this->assertStringContainsString('repair/logo/'.$repairShop->id.'/logo.webp', $data['repairShop']->logo);
        $this->assertCount(1, $data['repairShop']->repairCategories);
    }

    public function test_it_uses_fallback_logo_when_no_image_exists(): void
    {
        $repairShop = RepairShop::create([
            'name' => 'بدون لوگو',
            'slug' => 'no-logo',
        ]);

        $data = $this->service->getProfilePageData('no-logo');

        $this->assertSame('https://partsmall.ir/img/no_image_repair.jpg', $data['repairShop']->logo);
    }

    public function test_it_throws_not_found_for_unknown_slug(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->getProfilePageData('missing-repair-shop');
    }

    private function createRepairShopWithRelations(): RepairShop
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);

        $repairShop = RepairShop::create([
            'name' => 'تعمیرگاه آریا',
            'slug' => 'aria-repair',
            'state_id' => $state->id,
        ]);

        $repairShop->images()->create(['type' => ImageType::Logo, 'path' => 'logo.webp']);

        $category = RepairCategory::create(['name' => 'جلوبندی']);
        $repairShop->repairCategories()->attach($category);

        return $repairShop;
    }
}
