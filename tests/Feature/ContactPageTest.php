<?php

namespace Tests\Feature;

use App\Models\ContactLead;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
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
            ->assertSee(route('forms.contact.create', ['embed' => 1]), false)
            ->assertSee('contact-form-iframe', false)
            ->assertSee('didar-contact-form-resize', false)
            ->assertSee(config('partsmall.contact.image_url'), false)
            ->assertSee(config('partsmall.contact.phone'), false)
            ->assertSee(config('partsmall.contact.email'), false);
    }

    public function test_contact_form_renders_embed_mode(): void
    {
        $this->get(route('forms.contact.create', ['embed' => 1]))
            ->assertOk()
            ->assertSee('didar-contact-form', false)
            ->assertSee('novalidate', false)
            ->assertSee('data-field-error="phone"', false)
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="message"', false)
            ->assertSee('ارسال پیام', false)
            ->assertSee('name="_token"', false);
    }

    public function test_contact_form_rejects_invalid_mobile(): void
    {
        $response = $this->from(route('forms.contact.create', ['embed' => 1]))
            ->post(route('forms.contact.store', ['embed' => 1]), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '12345',
                'message' => 'hello world',
            ]);

        $response->assertRedirect(route('forms.contact.create', ['embed' => 1]))
            ->assertSessionHasErrors('phone');

        $this->followRedirects($response)
            ->assertSee('شماره موبایل معتبر نیست', false)
            ->assertSee('data-field-error="phone"', false)
            ->assertSee('didar-contact-form__input--error', false);
    }
}
