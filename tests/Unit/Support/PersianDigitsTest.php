<?php

namespace Tests\Unit\Support;

use App\Support\PersianDigits;
use PHPUnit\Framework\TestCase;

class PersianDigitsTest extends TestCase
{
    public function test_it_converts_english_digits_to_persian(): void
    {
        $this->assertSame('۴.۵/۵', PersianDigits::convert('4.5/5'));
    }

    public function test_it_converts_integers_to_persian(): void
    {
        $this->assertSame('۳', PersianDigits::convert(3));
    }

    public function test_it_leaves_persian_digits_unchanged(): void
    {
        $this->assertSame('۴.۵', PersianDigits::convert('۴.۵'));
    }
}
