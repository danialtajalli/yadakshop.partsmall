<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_shop_comment(): void
    {
        $shop = $this->createShop();

        $response = $this->post(route('shop.comments.store', $shop->slug), [
            'fullname' => 'علی رضایی',
            'mobile' => '09121234567',
            'body' => 'خرید خوبی بود و برخورد مناسبی داشتند.',
            'rating' => 4,
        ]);

        $response->assertRedirect(route('shop.profile', $shop->slug).'#comments');
        $response->assertSessionHas('comment_submitted', true);

        $this->assertDatabaseHas('comments', [
            'shop_id' => $shop->id,
            'fullname' => 'علی رضایی',
            'mobile' => '09121234567',
            'body' => 'خرید خوبی بود و برخورد مناسبی داشتند.',
            'rating' => 4,
            'confirmed' => false,
        ]);
    }

    public function test_submitted_comment_is_not_shown_until_confirmed(): void
    {
        $shop = $this->createShop();

        $this->post(route('shop.comments.store', $shop->slug), [
            'fullname' => 'علی رضایی',
            'body' => 'این نظر هنوز تایید نشده است.',
            'rating' => 5,
        ])->assertRedirect()->assertSessionHas('comment_submitted');

        $response = $this->get(route('shop.profile', $shop->slug));

        $response->assertOk();
        $response->assertDontSee('این نظر هنوز تایید نشده است.', false);
    }

    public function test_shop_comment_requires_valid_data(): void
    {
        $shop = $this->createShop();

        $response = $this->from(route('shop.profile', $shop->slug))
            ->post(route('shop.comments.store', $shop->slug), [
                'fullname' => '',
                'body' => 'کوتاه',
                'rating' => 9,
            ]);

        $response->assertRedirect(route('shop.profile', $shop->slug));
        $response->assertSessionHasErrors(['fullname', 'body', 'rating']);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_honeypot_blocks_spam_submissions(): void
    {
        $shop = $this->createShop();

        $response = $this->post(route('shop.comments.store', $shop->slug), [
            'fullname' => 'اسپمر',
            'body' => 'این یک نظر اسپم است برای تست سیستم.',
            'rating' => 1,
            'company_url' => 'https://spam.example',
        ]);

        $response->assertSessionHasErrors('company_url');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_shop_comment_store_returns_not_found_for_unknown_shop(): void
    {
        $this->post(route('shop.comments.store', 'missing-shop'), [
            'fullname' => 'کاربر',
            'body' => 'نظر تستی برای فروشگاه ناموجود.',
            'rating' => 3,
        ])->assertNotFound();
    }

    public function test_shop_comment_shows_validation_errors_as_list(): void
    {
        $shop = $this->createShop();

        $this->from(route('shop.profile', $shop->slug))
            ->post(route('shop.comments.store', $shop->slug), [
                'fullname' => '',
                'body' => 'کوتاه',
                'rating' => 9,
            ])
            ->assertRedirect(route('shop.profile', $shop->slug));

        $response = $this->get(route('shop.profile', $shop->slug));

        $response->assertOk();
        $response->assertSee('ps-form-errors', false);
        $response->assertSee('لطفاً موارد زیر را اصلاح کنید', false);
        $response->assertSee('متن نظر باید حداقل ۱۰ کاراکتر باشد.', false);
    }

    public function test_shop_comment_validates_mobile_format(): void
    {
        $shop = $this->createShop();

        $this->from(route('shop.profile', $shop->slug))
            ->post(route('shop.comments.store', $shop->slug), [
                'fullname' => 'علی رضایی',
                'mobile' => '12345',
                'body' => 'متن نظر معتبر برای تست اعتبارسنجی موبایل.',
                'rating' => 4,
            ])
            ->assertRedirect(route('shop.profile', $shop->slug));

        $response = $this->get(route('shop.profile', $shop->slug));

        $response->assertOk();
        $response->assertSee('شماره موبایل معتبر نیست', false);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_successful_comment_shows_success_modal(): void
    {
        $shop = $this->createShop();

        $response = $this->followingRedirects()->post(route('shop.comments.store', $shop->slug), [
            'fullname' => 'علی رضایی',
            'body' => 'خرید خوبی بود و برخورد مناسبی داشتند.',
            'rating' => 4,
        ]);

        $response->assertOk();
        $response->assertSee('data-shop-comment-success-modal', false);
        $response->assertSee('نظر شما ثبت شد', false);
        $response->assertSee('پس از تایید در این صفحه نمایش داده می‌شود', false);
    }

    public function test_shop_comment_store_returns_json_for_ajax_requests(): void
    {
        $shop = $this->createShop();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post(route('api.shop.comments.store', $shop->slug), [
            'fullname' => 'علی رضایی',
            'mobile' => '09121234567',
            'body' => 'خرید خوبی بود و برخورد مناسبی داشتند.',
            'rating' => 4,
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'نظر شما با موفقیت ثبت شد و پس از تایید در این صفحه نمایش داده می‌شود.',
        ]);

        $this->assertDatabaseHas('comments', [
            'shop_id' => $shop->id,
            'fullname' => 'علی رضایی',
            'confirmed' => false,
        ]);
    }

    public function test_shop_comment_ajax_validation_returns_json_errors(): void
    {
        $shop = $this->createShop();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post(route('api.shop.comments.store', $shop->slug), [
            'fullname' => '',
            'body' => 'کوتاه',
            'rating' => 9,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['fullname', 'body', 'rating']);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_shop_profile_displays_comment_form(): void
    {
        $shop = $this->createShop();

        $response = $this->get(route('shop.profile', $shop->slug));

        $response->assertOk();
        $response->assertSee('ثبت نظر شما', false);
        $response->assertSee('shopCommentForm', false);
        $response->assertSee('profile/'.$shop->slug.'/comments', false);
        $response->assertSee('data-rating-picker', false);
    }

    private function createShop(): Shop
    {
        $state = State::create(['name' => 'تهران', 'slug' => 'tehran', 'tel_prefix' => '021']);

        return Shop::create([
            'name' => 'یدک شاپ',
            'slug' => 'yadak-shop',
            'state_id' => $state->id,
            'order' => 1,
        ]);
    }
}
