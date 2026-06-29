<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_show_returns_successful_response(): void
    {
        Page::create([
            'title' => 'درباره ما',
            'slug' => 'about',
            'content' => '<p>به پارتس مال خوش آمدید.</p>rn',
        ]);

        $response = $this->get(route('page.show', 'about'));

        $response->assertOk();
        $response->assertViewIs('page.show');
        $response->assertViewHas('title', 'درباره ما');
        $response->assertSee('درباره ما', false);
        $response->assertSee('به پارتس مال خوش آمدید.', false);
        $response->assertSee('BreadcrumbList', false);
    }

    public function test_page_show_returns_not_found_for_unknown_slug(): void
    {
        $this->get(route('page.show', 'missing-page'))->assertNotFound();
    }

    public function test_page_show_returns_not_found_when_slug_is_blank(): void
    {
        Page::create([
            'title' => 'بدون اسلاگ',
            'slug' => null,
            'content' => 'محتوا',
        ]);

        $this->get(route('page.show', 'no-slug'))->assertNotFound();
    }

    public function test_header_links_to_navigation_pages(): void
    {
        Page::create(['title' => 'درباره ما', 'slug' => 'about', 'content' => 'محتوا']);
        Page::create(['title' => 'تماس با ما', 'slug' => 'contact', 'content' => 'محتوا']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-mobile-menu-toggle', false)
            ->assertSee('id="mobile-menu"', false)
            ->assertSee('id="header-meilisearch-parts-search"', false)
            ->assertSee('id="mobile-meilisearch-parts-search"', false)
            ->assertSee(route('page.show', 'about'), false)
            ->assertSee(route('page.show', 'contact'), false);
    }
}
