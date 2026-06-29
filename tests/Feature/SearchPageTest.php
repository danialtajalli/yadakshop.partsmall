<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\EngineManager;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_uses_scout_indexed_part_fields(): void
    {
        config(['scout.driver' => 'collection']);
        app(EngineManager::class)->forgetDrivers();

        $suspension = PartsCategory::create(['name' => 'جلوبندی']);
        $engine = PartsCategory::create(['name' => 'موتوری']);

        Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $suspension->id,
        ]);
        Part::create([
            'name' => 'پیستون',
            'slug' => 'piston',
            'parts_category_id' => $engine->id,
        ]);

        $this->get(route('search.index', ['q' => 'جلوبندی']))
            ->assertOk()
            ->assertViewIs('search.index')
            ->assertSee('طبق', false)
            ->assertDontSee('پیستون', false);
    }

    public function test_search_page_prompts_for_a_query(): void
    {
        $this->get(route('search.index'))
            ->assertOk()
            ->assertSee('برای جستجو، نام قطعه یا دسته‌بندی را وارد کنید.', false);
    }
}
