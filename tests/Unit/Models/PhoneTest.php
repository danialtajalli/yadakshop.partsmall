<?php

namespace Tests\Unit\Models;

use App\Enums\PhoneType;
use App\Models\Phone;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    #[Test]
    public function landline_display_label_formats_city_code_and_local_pairs(): void
    {
        $phone = new Phone([
            'phone_number' => '02191556162',
            'type' => PhoneType::Land,
        ]);

        $this->assertSame('021 - 91 55 6162', $phone->displayLabel());
    }

    #[Test]
    public function landline_display_label_formats_eight_digit_numbers_without_city_code(): void
    {
        $phone = new Phone([
            'phone_number' => '91556162',
            'type' => PhoneType::Land,
        ]);

        $this->assertSame('91 55 6162', $phone->displayLabel());
    }

    #[Test]
    public function landline_display_label_keeps_short_numbers_unchanged(): void
    {
        $phone = new Phone([
            'phone_number' => '9155616',
            'type' => PhoneType::Land,
        ]);

        $this->assertSame('9155616', $phone->displayLabel());
    }

    #[Test]
    public function landline_display_label_normalizes_persian_digits(): void
    {
        $phone = new Phone([
            'phone_number' => '۰۲۱۹۱۵۵۶۱۶۲',
            'type' => PhoneType::Land,
        ]);

        $this->assertSame('021 - 91 55 6162', $phone->displayLabel());
    }

    #[Test]
    public function mobile_display_label_formats_prefix_triple_and_pairs(): void
    {
        $phone = new Phone([
            'phone_number' => '09111111111',
            'type' => PhoneType::Mobile,
        ]);

        $this->assertSame('0911 111 11 11', $phone->displayLabel());
    }

    #[Test]
    public function mobile_display_label_formats_typical_iranian_number(): void
    {
        $phone = new Phone([
            'phone_number' => '09120818355',
            'type' => PhoneType::Mobile,
        ]);

        $this->assertSame('0912 081 83 55', $phone->displayLabel());
    }

    #[Test]
    public function mobile_display_label_keeps_short_numbers_unchanged(): void
    {
        $phone = new Phone([
            'phone_number' => '091111',
            'type' => PhoneType::Mobile,
        ]);

        $this->assertSame('091111', $phone->displayLabel());
    }

    #[Test]
    public function messenger_display_label_keeps_stored_number(): void
    {
        $phone = new Phone([
            'phone_number' => '09120818355',
            'type' => PhoneType::Whatsapp,
        ]);

        $this->assertSame('09120818355', $phone->displayLabel());
    }
}
