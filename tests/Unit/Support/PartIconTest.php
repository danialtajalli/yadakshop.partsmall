<?php

namespace Tests\Unit\Support;

use App\Models\Part;
use App\Models\PartsCategory;
use App\Support\PartIcon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartIconTest extends TestCase
{
    use RefreshDatabase;

    public function test_icon_type_is_derived_from_part_name_first(): void
    {
        $category = PartsCategory::create(['name' => 'جلوبندی']);

        $armPart = Part::create([
            'name' => 'طبق',
            'slug' => 'arm',
            'parts_category_id' => $category->id,
        ]);

        $sparkPlug = Part::create([
            'name' => 'شمع',
            'slug' => 'spark-plug',
            'parts_category_id' => PartsCategory::create(['name' => 'برقی'])->id,
        ]);

        $brakePad = Part::create([
            'name' => 'لنت ترمز جلو',
            'slug' => 'front-brake-pad',
            'parts_category_id' => PartsCategory::create(['name' => 'لنت ترمزی'])->id,
        ]);

        $this->assertSame('control-arm', PartIcon::type($armPart));
        $this->assertSame('spark-plug', PartIcon::type($sparkPlug));
        $this->assertSame('brake-pad', PartIcon::type($brakePad));
    }

    public function test_icon_type_falls_back_to_category_when_name_is_ambiguous(): void
    {
        $part = Part::create([
            'name' => 'قطعه تست',
            'slug' => 'test-part',
            'parts_category_id' => PartsCategory::create(['name' => 'اگزوزی'])->id,
        ]);

        $this->assertSame('exhaust', PartIcon::type($part));
    }

    public function test_unknown_part_and_category_use_default_icon(): void
    {
        $part = Part::create([
            'name' => 'نامشخص',
            'slug' => 'unknown',
            'parts_category_id' => PartsCategory::create(['name' => 'دسته ناشناخته'])->id,
        ]);

        $this->assertSame('part', PartIcon::type($part));
    }
}
