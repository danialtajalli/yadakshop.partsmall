<?php

namespace Tests\Feature;

use App\Events\ContactLeadSubmitted;
use App\Models\ContactLead;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'contact.pipeline' => 'didar_with_database',
            'contact.didar.api_key' => 'test-api-key',
            'contact.didar.owner_username' => 'owner@example.com',
            'contact.didar.deal_field_key' => 'Field_8783_0_1',
            'contact.didar.base_url' => 'https://app.didar.me/api/',
        ]);

        Page::create([
            'title' => 'Contact',
            'slug' => 'contact',
            'content' => '<p>Contact page content</p>',
        ]);
    }

    public function test_database_only_pipeline_persists_lead_without_didar_calls(): void
    {
        config(['contact.pipeline' => 'database_only']);

        Event::fake([ContactLeadSubmitted::class]);

        $response = $this->post(route('forms.contact.store'), [
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'phone' => '09121234567',
            'message' => 'سلام، نیاز به راهنمایی دارم.',
        ]);

        $response->assertRedirect(route('page.contact'));
        $response->assertSessionHas('contact_status_type', 'success');

        $this->assertDatabaseHas('contact_leads', [
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'phone' => '09121234567',
            'status' => ContactLead::STATUS_COMPLETED,
            'pipeline' => 'database_only',
        ]);

        Http::assertNothingSent();
        Event::assertDispatched(ContactLeadSubmitted::class);
    }

    public function test_successful_contact_submission_shows_status_modal(): void
    {
        config(['contact.pipeline' => 'database_only']);

        $response = $this->post(route('forms.contact.store'), [
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'phone' => '09121234567',
            'message' => 'Please contact me about parts.',
        ]);

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('data-contact-form-status-modal', false)
            ->assertSee('data-contact-form-status-close', false)
            ->assertSee('contact-form-status-modal', false);
    }

    public function test_didar_pipeline_submits_lead_and_persists_result(): void
    {
        Http::fake([
            'https://app.didar.me/api/product/search*' => Http::response([
                'Response' => [['Id' => 'product-1']],
            ]),
            'https://app.didar.me/api/contact/search*' => Http::response([
                'Response' => [],
            ]),
            'https://app.didar.me/api/contact/save*' => Http::response([
                'Response' => ['PersonId' => 'person-1'],
            ]),
            'https://app.didar.me/api/User/List*' => Http::response([
                'Response' => [
                    ['UserName' => 'owner@example.com', 'UserId' => 'owner-1'],
                ],
            ]),
            'https://app.didar.me/api/pipeline/list/0*' => Http::response([
                'Response' => [
                    [
                        'Id' => 'pipeline-1',
                        'Stages' => [['Id' => 'stage-1']],
                    ],
                ],
            ]),
            'https://app.didar.me/api/deal/save*' => Http::response([
                'Response' => ['DealId' => 'deal-1'],
            ]),
        ]);

        Event::fake([ContactLeadSubmitted::class]);

        $response = $this->post(route('forms.contact.store'), [
            'first_name' => 'Sara',
            'last_name' => 'Karimi',
            'phone' => '09123456789',
            'message' => 'درخواست تماس از وب‌سایت',
        ]);

        $response->assertRedirect(route('page.contact'));
        $response->assertSessionHas('contact_status_type', 'success');

        $this->assertDatabaseHas('contact_leads', [
            'first_name' => 'Sara',
            'last_name' => 'Karimi',
            'phone' => '09123456789',
            'status' => ContactLead::STATUS_COMPLETED,
            'didar_product_id' => 'product-1',
            'didar_person_id' => 'person-1',
            'didar_deal_id' => 'deal-1',
        ]);

        Http::assertSentCount(6);
        Event::assertDispatched(ContactLeadSubmitted::class);
    }

    public function test_didar_pipeline_reuses_existing_contact_when_found(): void
    {
        Http::fake([
            'https://app.didar.me/api/product/search*' => Http::response([
                'Response' => [['Id' => 'product-1']],
            ]),
            'https://app.didar.me/api/contact/search*' => Http::response([
                'Response' => [['Id' => 'existing-person']],
            ]),
            'https://app.didar.me/api/User/List*' => Http::response([
                'Response' => [
                    ['UserName' => 'owner@example.com', 'UserId' => 'owner-1'],
                ],
            ]),
            'https://app.didar.me/api/pipeline/list/0*' => Http::response([
                'Response' => [
                    [
                        'Id' => 'pipeline-1',
                        'Stages' => [['Id' => 'stage-1']],
                    ],
                ],
            ]),
            'https://app.didar.me/api/deal/save*' => Http::response([
                'Response' => ['DealId' => 'deal-1'],
            ]),
        ]);

        $this->post(route('forms.contact.store'), [
            'first_name' => 'Sara',
            'last_name' => 'Karimi',
            'phone' => '09123456789',
            'message' => 'درخواست تماس از وب‌سایت',
        ])->assertSessionHas('contact_status_type', 'success');

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'contact/save');
        });

        $this->assertDatabaseHas('contact_leads', [
            'didar_person_id' => 'existing-person',
            'status' => ContactLead::STATUS_COMPLETED,
        ]);
    }
}
