<?php

namespace Tests\Unit\Support;

use App\Enums\PhoneType;
use App\Models\Phone;
use App\Support\ContentCacheInvalidator;
use App\Support\ContentCacheTag;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContentCacheInvalidatorPhoneTest extends TestCase
{
    #[Test]
    public function phone_with_shop_maps_to_home_directory_product_tags(): void
    {
        $phone = new Phone(['shop_id' => 1, 'phone_number' => '0211', 'type' => PhoneType::Land]);

        $this->assertSame(
            [ContentCacheTag::HOME, ContentCacheTag::DIRECTORY, ContentCacheTag::PRODUCT],
            ContentCacheInvalidator::tagsFor($phone),
        );
    }

    #[Test]
    public function phone_with_repair_shop_maps_to_home_directory_tags(): void
    {
        $phone = new Phone(['repair_shop_id' => 2, 'phone_number' => '0211', 'type' => PhoneType::Land]);

        $this->assertSame(
            [ContentCacheTag::HOME, ContentCacheTag::DIRECTORY],
            ContentCacheInvalidator::tagsFor($phone),
        );
    }

    #[Test]
    public function phone_without_owner_maps_to_home_product_tags(): void
    {
        $phone = new Phone(['phone_number' => '0211', 'type' => PhoneType::Mobile]);

        $this->assertSame(
            [ContentCacheTag::HOME, ContentCacheTag::PRODUCT],
            ContentCacheInvalidator::tagsFor($phone),
        );
    }
}
