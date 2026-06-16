<?php

namespace App\Support;

use App\Models\Part;

class PartIcon
{
    public static function type(Part $part): string
    {
        $name = mb_strtolower(trim($part->name));

        foreach (PartIconDefinitions::nameRules() as $rule) {
            foreach ($rule['patterns'] as $pattern) {
                if (str_contains($name, mb_strtolower($pattern))) {
                    return $rule['icon'];
                }
            }
        }

        $categoryName = $part->partsCategory?->name;

        if ($categoryName !== null) {
            $categoryIcons = PartIconDefinitions::categoryIcons();

            if (isset($categoryIcons[$categoryName])) {
                return $categoryIcons[$categoryName];
            }
        }

        return 'part';
    }
}
