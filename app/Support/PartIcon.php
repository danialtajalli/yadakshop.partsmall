<?php

namespace App\Support;

use App\Models\Part;

class PartIcon
{
    public static function type(Part $part): string
    {
        $haystack = mb_strtolower(trim(
            ($part->partsCategory?->name ?? '').' '.$part->name.' '.($part->category_description ?? ''),
        ));

        return match (true) {
            self::contains($haystack, ['موتور', 'شمع', 'پیستون', 'میل لنگ', 'سرسیلندر']) => 'engine',
            self::contains($haystack, ['ترمز', 'لنت', 'دیسک']) => 'brake',
            self::contains($haystack, ['جلوبندی', 'تعلیق', 'فنر', 'طبق', 'کمک', 'میل فرمان', 'سیبک']) => 'suspension',
            self::contains($haystack, ['برق', 'الکتری', 'باتری', 'دینام', 'سیم', 'سنسور']) => 'electric',
            self::contains($haystack, ['فیلتر', 'روغن']) => 'filter',
            self::contains($haystack, ['گیربکس', 'کلاچ', 'دیسک و صفحه']) => 'gearbox',
            self::contains($haystack, ['بدنه', 'شیشه', 'آینه', 'درب', 'سپر', 'گلگیر']) => 'body',
            self::contains($haystack, ['لاستیک', 'تایر', 'رینگ']) => 'tire',
            self::contains($haystack, ['رادیاتور', 'کولر', 'بخاری', 'تهویه']) => 'cooling',
            default => 'part',
        };
    }

    /**
     * @param  list<string>  $needles
     */
    private static function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
