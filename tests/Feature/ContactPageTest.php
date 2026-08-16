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
            'title' => 'Contact',
            'slug' => 'contact',
            'content' => '<p>Contact page content</p>',
        ]);
    }

    public function test_contact_page_renders_direct_contact_form_and_image(): void
    {
        $this->get(route('page.show', 'contact'))
            ->assertOk()
            ->assertViewIs('page.contact')
            ->assertSee('didar-contact-form', false)
            ->assertSee('novalidate', false)
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="message"', false)
            ->assertSee(route('forms.contact.store'), false)
            ->assertDontSee('contact-form-iframe', false)
            ->assertDontSee('<iframe', false)
            ->assertDontSee('didar-contact-form-resize', false)
            ->assertSee(config('partsmall.contact.image_url'), false)
            ->assertSee(config('partsmall.contact.phone'), false)
            ->assertSee(config('partsmall.contact.email'), false);
    }

    public function test_contact_form_get_route_is_not_public(): void
    {
        $this->get(route('forms.contact.create', ['embed' => 1]))
            ->assertNotFound();
    }

    public function test_contact_form_rejects_invalid_mobile(): void
    {
        $response = $this->from(route('page.contact'))
            ->post(route('forms.contact.store'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'phone' => '12345',
                'message' => 'hello world',
            ]);

        $response->assertRedirect(route('page.contact'))
            ->assertSessionHasErrors('phone');

        $this->followRedirects($response)
            ->assertSee('data-field-error="phone"', false)
            ->assertSee('didar-contact-form__input--error', false);
    }
}
