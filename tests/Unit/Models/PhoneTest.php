<?php

namespace Tests\Unit\Models;

use App\Enums\PhoneType;
use App\Models\Phone;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    #[Test]
    public function display_label_falls_back_to_phone_number(): void
    {
        $phone = new Phone([
            'phone_number' => '02133979370',
            'type' => PhoneType::Land,
        ]);

        $this->assertSame('02133979370', $phone->displayLabel());
    }

    #[Test]
    public function display_label_uses_label_when_present(): void
    {
        $phone = new Phone([
            'phone_number' => '02133979370',
            'label' => '021 33 97 93 70',
            'type' => PhoneType::Land,
        ]);

        $this->assertSame('021 33 97 93 70', $phone->displayLabel());
    }

    #[Test]
    public function display_label_ignores_blank_label(): void
    {
        $phone = new Phone([
            'phone_number' => '02133979370',
            'label' => '   ',
            'type' => PhoneType::Land,
        ]);

        $this->assertSame('02133979370', $phone->displayLabel());
    }
}
