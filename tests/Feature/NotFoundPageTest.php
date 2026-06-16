<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_route_renders_custom_404_page(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('۴۰۴', false)
            ->assertSee('صفحه پیدا نشد', false)
            ->assertSee(route('home'), false)
            ->assertSee(route('companies.index'), false)
            ->assertSee(route('car.parts'), false);
    }

    public function test_missing_page_slug_renders_custom_404_page(): void
    {
        $this->get(route('page.show', 'missing-page'))
            ->assertNotFound()
            ->assertSee('۴۰۴', false)
            ->assertSee('صفحه پیدا نشد', false);
    }
}
