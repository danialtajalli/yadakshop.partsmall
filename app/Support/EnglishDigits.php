<?php

namespace App\Support;

class EnglishDigits
{
    /** @var list<string> */
    private const PERSIAN = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    /** @var list<string> */
    private const ARABIC = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    /** @var list<string> */
    private const ENGLISH = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public static function convert(string $value): string
    {
        return str_replace(
            self::PERSIAN,
            self::ENGLISH,
            str_replace(self::ARABIC, self::ENGLISH, $value),
        );
    }
}
