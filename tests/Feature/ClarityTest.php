<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ClarityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.clarity.enabled' => true, 'services.clarity.project_id' => 'abc123']);
        $this->app['env'] = 'production';
    }

    private function renderTracker(): string
    {
        return Blade::render("@include('layouts.partials.clarity') @include('layouts.partials.clarity') @stack('overlays') @stack('privacy-settings')");
    }

    public function test_production_has_one_tracker_and_consent_controls(): void
    {
        $html = $this->renderTracker();

        $this->assertSame(1, substr_count($html, 'data-clarity-project-id="abc123"'));
        $this->assertSame(1, substr_count($html, 'id="ps-clarity-consent"'));
        $this->assertStringContainsString('id="ps-clarity-reject"', $html);
        $this->assertStringContainsString('id="ps-clarity-settings"', $html);
        $this->assertStringNotContainsString('https://www.clarity.ms/tag/', $html);
    }

    public function test_local_and_testing_environments_do_not_render_tracking(): void
    {
        foreach (['local', 'testing', 'staging'] as $environment) {
            $this->app['env'] = $environment;
            $this->assertStringNotContainsString('clarity', $this->renderTracker());
        }
    }

    public function test_disabled_tracking_does_not_render_assets_or_banner(): void
    {
        config(['services.clarity.enabled' => false]);

        $this->assertStringNotContainsString('clarity', $this->renderTracker());
    }

    public function test_missing_or_invalid_project_ids_do_not_render_tracking(): void
    {
        foreach (['', null, 'example/id', '"><script>alert(1)</script>'] as $projectId) {
            config(['services.clarity.project_id' => $projectId]);
            $this->assertStringNotContainsString('clarity', $this->renderTracker());
        }
    }

    public function test_admin_paths_do_not_render_tracking(): void
    {
        foreach (['/admin', '/admin/login', '/admin/shops/1/edit'] as $path) {
            $this->app->instance('request', Request::create($path));
            $this->assertStringNotContainsString('clarity', $this->renderTracker());
        }
    }

    public function test_public_layout_contains_consent_controls(): void
    {
        $html = view('layouts.app')->render();

        $this->assertStringContainsString('data-clarity-project-id="abc123"', $html);
        $this->assertStringContainsString('id="ps-clarity-consent"', $html);
    }
}
