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

    public function test_icon_type_is_derived_from_category_and_name(): void
    {
        $brakeCategory = PartsCategory::create(['name' => 'ترمز']);
        $brakePart = Part::create([
            'name' => 'دیسک چرخ جلو',
            'slug' => 'front-disc',
            'parts_category_id' => $brakeCategory->id,
        ]);

        $enginePart = Part::create([
            'name' => 'شمع',
            'slug' => 'spark-plug',
            'parts_category_id' => PartsCategory::create(['name' => 'موتور'])->id,
        ]);

        $this->assertSame('brake', PartIcon::type($brakePart));
        $this->assertSame('engine', PartIcon::type($enginePart));
    }
}
