<?php

namespace Tests\Unit\Support;

use App\Support\PartIconDefinitions;
use Tests\TestCase;

class PartIconDefinitionsTest extends TestCase
{
    public function test_all_seeded_parts_resolve_to_a_specific_icon(): void
    {
        if (! file_exists(base_path('scripts/list_parts.php'))) {
            $this->markTestSkipped('Requires seeded database.');
        }

        $parts = \App\Models\Part::query()->with('partsCategory')->get();

        if ($parts->isEmpty()) {
            $this->markTestSkipped('No parts in database.');
        }

        $genericCount = 0;

        foreach ($parts as $part) {
            if (\App\Support\PartIcon::type($part) === 'part') {
                $genericCount++;
            }
        }

        $this->assertSame(0, $genericCount, 'Every seeded part should map to a specific icon.');
    }

    public function test_name_rules_are_ordered_with_specific_patterns(): void
    {
        $rules = PartIconDefinitions::nameRules();

        $wiperIndex = collect($rules)->search(fn (array $rule) => $rule['icon'] === 'wiper');
        $motorIndex = collect($rules)->search(fn (array $rule) => $rule['icon'] === 'motor');

        $this->assertNotFalse($wiperIndex);
        $this->assertNotFalse($motorIndex);
        $this->assertLessThan($motorIndex, $wiperIndex);
    }
}
