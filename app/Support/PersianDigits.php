<?php

namespace App\Support;

class PersianDigits
{
    /** @var list<string> */
    private const PERSIAN = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    /** @var list<string> */
    private const ENGLISH = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public static function convert(string|int|float $value): string
    {
        return str_replace(self::ENGLISH, self::PERSIAN, (string) $value);
    }
}
