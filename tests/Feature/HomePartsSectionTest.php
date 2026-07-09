<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePartsSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_parts_section_uses_svg_icons_instead_of_font_awesome(): void
    {
        $category = PartsCategory::create(['name' => 'ترمز']);
        Part::create([
            'name' => 'لنت جلو',
            'slug' => 'front-pad',
            'parts_category_id' => $category->id,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-part-card', false)
            ->assertSee('<svg', false)
            ->assertSee('لنت جلو', false)
            ->assertDontSee('fa-gear', false);
    }

    public function test_home_parts_section_includes_load_more_control_when_many_parts_exist(): void
    {
        $category = PartsCategory::create(['name' => 'موتور']);

        for ($index = 1; $index <= 25; $index++) {
            Part::create([
                'name' => "قطعه {$index}",
                'slug' => "part-{$index}",
                'parts_category_id' => $category->id,
            ]);
        }

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('home-parts-load-more', false)
            ->assertSee('نمایش بیشتر', false)
            ->assertSee('قطعه 1', false)
            ->assertSee('قطعه 25', false);
    }
}
