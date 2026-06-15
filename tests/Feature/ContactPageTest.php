<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    private const PORSLINE_EMBED = '<div id="OrZ9EFLS" style="min-height: 480px;"><style>.porsline_embed{}.porsline_embed .ratio {display:block;width:100%;height:auto;}.porsline_embed iframe {position:absolute;top:0;left:0;width:100%; height:100%;}</style><div class="porsline_embed"> <span style="display: block;padding-top: 44.3%"></span><iframe src="https://survey.porsline.ir/s/OrZ9EFLS" border="none" height="100%" width="100%" style="min-height: 420px;min-width: 360px;max-height: 100%;max-width: 100%;" allow="microphone" frameborder="0"></iframe></div></div>';

    protected function setUp(): void
    {
        parent::setUp();

        Page::create([
            'title' => 'تماس با ما',
            'slug' => 'contact',
            'content' => self::PORSLINE_EMBED,
        ]);
    }

    public function test_contact_page_renders_survey_embed_and_image(): void
    {
        $this->get(route('page.show', 'contact'))
            ->assertOk()
            ->assertViewIs('page.contact')
            ->assertSee('تماس با ما', false)
            ->assertSee('فرم تماس', false)
            ->assertSee('survey.porsline.ir/s/OrZ9EFLS', false)
            ->assertSee('contact-survey-embed', false)
            ->assertSee(config('partsmall.contact.image_url'), false)
            ->assertSee(config('partsmall.contact.phone'), false)
            ->assertSee(config('partsmall.contact.email'), false)
            ->assertDontSee('ارسال پیام', false);
    }
}
