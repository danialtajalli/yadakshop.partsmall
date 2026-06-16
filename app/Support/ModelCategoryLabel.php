<?php

namespace App\Support;

use App\Models\ModelCategory;

class ModelCategoryLabel
{
    public static function display(?ModelCategory $category): string
    {
        if ($category === null || trim($category->name) === '') {
            return 'سایر';
        }

        return $category->name;
    }

    public static function slug(?ModelCategory $category): string
    {
        return $category?->slug ?? 'uncategorized';
    }
}
