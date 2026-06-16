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
}
