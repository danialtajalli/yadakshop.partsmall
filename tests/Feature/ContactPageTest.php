<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create([
            'title' => 'تماس با ما',
            'slug' => 'contact',
            'content' => '<p>توضیحات تکمیلی تماس با ما</p>',
        ]);
    }

    public function test_contact_page_renders_contact_form_iframe_and_image(): void
    {
        $this->get(route('page.show', 'contact'))
            ->assertOk()
            ->assertViewIs('page.contact')
            ->assertSee('تماس با ما', false)
            ->assertSee('فرم تماس', false)
            ->assertSee('forms/contact.php?embed=1', false)
            ->assertSee(config('partsmall.contact.image_url'), false)
            ->assertSee(config('partsmall.contact.phone'), false)
            ->assertSee(config('partsmall.contact.email'), false)
            ->assertSee('توضیحات تکمیلی تماس با ما', false);
    }

    public function test_standalone_contact_form_renders_embed_mode(): void
    {
        $_GET = ['embed' => '1'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];

        ob_start();
        require public_path('forms/contact.php');
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('didar-contact-form', $html);
        $this->assertStringContainsString('name="first_name"', $html);
        $this->assertStringContainsString('name="last_name"', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('name="message"', $html);
        $this->assertStringContainsString('ارسال پیام', $html);
        $this->assertStringContainsString('name="csrf_token"', $html);
    }

    public function test_standalone_contact_form_rejects_invalid_mobile(): void
    {
        $_SESSION['didar_contact_csrf'] = 'test-token';
        $_GET = ['embed' => '1'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => 'test-token',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '12345',
            'message' => '',
        ];

        ob_start();
        require public_path('forms/contact.php');
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('شماره موبایل معتبر نیست', $html);
    }
}
