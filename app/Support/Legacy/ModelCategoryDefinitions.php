<?php

namespace App\Support\Legacy;

use Illuminate\Support\Str;

class ModelCategoryDefinitions
{
    /**
     * Unique legacy `cat` values from partsmall_db.sql `model` table.
     *
     * @return list<string>
     */
    public static function legacyCats(): array
    {
        return [
            '',
            'body',
            'cc',
            'company',
            'engine',
            'fuel',
            'g',
            'gearbox',
            'year-miladi',
            'year-shamsi',
            'بدنه',
            'سال',
            'شرکت',
            'لفظ',
            'موتور',
            'گیربکس',
        ];
    }

    public static function slugFor(string $legacyCat): string
    {
        return match ($legacyCat) {
            '' => 'uncategorized',
            'body' => 'body',
            'cc' => 'cc',
            'company' => 'company',
            'engine' => 'engine',
            'fuel' => 'fuel',
            'g' => 'g',
            'gearbox' => 'gearbox',
            'year-miladi' => 'year-miladi',
            'year-shamsi' => 'year-shamsi',
            'بدنه' => 'body-fa',
            'سال' => 'year-fa',
            'شرکت' => 'company-fa',
            'لفظ' => 'term',
            'موتور' => 'engine-fa',
            'گیربکس' => 'gearbox-fa',
            default => self::fallbackSlug($legacyCat),
        };
    }

    private static function fallbackSlug(string $legacyCat): string
    {
        $slug = Str::slug($legacyCat);

        return $slug !== '' ? $slug : 'cat-'.substr(md5($legacyCat), 0, 8);
    }
}
