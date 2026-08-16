<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_500_error_page_renders(): void
    {
        $this->view('errors.500')
            ->assertSee('۵۰۰', false)
            ->assertSee('خطای سرور', false)
            ->assertSee(route('home'), false);
    }
}
