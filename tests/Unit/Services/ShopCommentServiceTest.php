<?php

namespace Tests\Unit\Services;

use App\Models\City;
use App\Models\Shop;
use App\Models\State;
use App\Services\ShopCommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCommentServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShopCommentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ShopCommentService;
    }

    public function test_it_stores_unconfirmed_shop_comment(): void
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);
        $city = City::create(['name' => 'تهران', 'slug' => 'tehran-city', 'state_id' => $state->id]);

        $shop = Shop::create([
            'name' => 'یدک شاپ',
            'slug' => 'yadak-shop',
            'city_id' => $city->id,
            'order' => 1,
        ]);

        $comment = $this->service->store($shop, [
            'fullname' => 'کاربر',
            'mobile' => '09120000000',
            'body' => 'نظر تستی برای فروشگاه.',
            'rating' => 5,
        ]);

        $this->assertFalse($comment->confirmed);
        $this->assertSame($shop->id, $comment->shop_id);
        $this->assertSame('کاربر', $comment->fullname);
    }
}
