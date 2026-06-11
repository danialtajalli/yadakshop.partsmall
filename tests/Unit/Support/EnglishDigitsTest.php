<?php

namespace Tests\Unit\Support;

use App\Support\EnglishDigits;
use PHPUnit\Framework\TestCase;

class EnglishDigitsTest extends TestCase
{
    public function test_it_converts_persian_digits_to_english(): void
    {
        $this->assertSame('02133979370', EnglishDigits::convert('۰۲۱۳۳۹۷۹۳۷۰'));
    }

    public function test_it_converts_arabic_digits_to_english(): void
    {
        $this->assertSame('09121234567', EnglishDigits::convert('٠٩١٢١٢٣٤٥٦٧'));
    }

    public function test_it_leaves_english_digits_unchanged(): void
    {
        $this->assertSame('02133979370', EnglishDigits::convert('02133979370'));
    }
}
