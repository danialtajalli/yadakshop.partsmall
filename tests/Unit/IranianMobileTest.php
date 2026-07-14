<?php

namespace Tests\Unit;

use App\Support\IranianMobile;
use PHPUnit\Framework\TestCase;

class IranianMobileTest extends TestCase
{
    public function test_normalize_converts_persian_digits_and_country_prefix(): void
    {
        $this->assertSame('09121234567', IranianMobile::normalize('۰۹۱۲۱۲۳۴۵۶۷'));
        $this->assertSame('09121234567', IranianMobile::normalize('+989121234567'));
    }

    public function test_is_valid_accepts_normalized_mobile(): void
    {
        $this->assertTrue(IranianMobile::isValid('09121234567'));
        $this->assertFalse(IranianMobile::isValid('12345'));
    }
}
