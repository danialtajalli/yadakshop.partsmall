<?php

namespace App\Support;

class PartIcon
{
    public static function type(mixed $part): string
    {
        $name = mb_strtolower(trim((string) data_get($part, 'name', '')));

        foreach (PartIconDefinitions::nameRules() as $rule) {
            foreach ($rule['patterns'] as $pattern) {
                if (str_contains($name, mb_strtolower($pattern))) {
                    return $rule['icon'];
                }
            }
        }

        $categoryName = data_get($part, 'partsCategory.name');

        if ($categoryName !== null) {
            $categoryIcons = PartIconDefinitions::categoryIcons();

            if (isset($categoryIcons[$categoryName])) {
                return $categoryIcons[$categoryName];
            }
        }

        return 'part';
    }
}
