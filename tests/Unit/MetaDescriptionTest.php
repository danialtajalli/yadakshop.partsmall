<?php

namespace Tests\Unit;

use App\Support\MetaDescription;
use PHPUnit\Framework\TestCase;

class MetaDescriptionTest extends TestCase
{
    public function test_home_mentions_spare_parts(): void
    {
        $this->assertStringContainsString('لوازم یدکی', MetaDescription::home());
    }

    public function test_listing_pages_have_type_specific_detail(): void
    {
        $shop = MetaDescription::listing('فروشگاه‌های لوازم یدکی');
        $repairShop = MetaDescription::listing('تعمیرگاه‌ها');
        $representation = MetaDescription::listing('نمایندگی‌ها');

        $this->assertStringContainsString('برندهای تحت پوشش', $shop);
        $this->assertStringContainsString('تخصص‌ها', $repairShop);
        $this->assertStringContainsString('برند خودرو', $representation);
    }

    public function test_about_and_contact_pages_have_custom_longer_meta(): void
    {
        $about = MetaDescription::page('درباره ما', 'محتوا');
        $contact = MetaDescription::page('تماس با ما', 'محتوا');

        $this->assertStringContainsString('مرجع جستجوی لوازم یدکی خودرو', $about);
        $this->assertStringContainsString('دسترسی سریع‌تر خریداران', $about);
        $this->assertStringContainsString('ارسال پیام', $contact);
        $this->assertStringContainsString('همکاری فروشگاه‌ها', $contact);
        $this->assertGreaterThanOrEqual(135, mb_strlen($about, 'UTF-8'));
        $this->assertGreaterThanOrEqual(140, mb_strlen($contact, 'UTF-8'));
    }

    public function test_product_and_vehicle_parts_catalog_descriptions_are_distinct(): void
    {
        $vehicleParts = MetaDescription::vehicleParts('هیوندای I10 2023');
        $product = MetaDescription::product('شیشه لچکی ثابت درب عقب هیوندای I10 2023');

        $this->assertStringContainsString('فهرست لوازم یدکی', $vehicleParts);
        $this->assertStringContainsString('قطعه مناسب', $vehicleParts);
        $this->assertStringContainsString('قیمت و فروشندگان', $product);
        $this->assertStringContainsString('اجرت‌های مرتبط', $product);
        $this->assertNotSame($vehicleParts, $product);
    }

    public function test_catalog_car_pages_include_next_catalog_steps(): void
    {
        $description = MetaDescription::catalog('لیست خودرو‌های هیوندای', 'خودروی مورد نظر را انتخاب کنید.');

        $this->assertStringContainsString('انتخاب خودرو', $description);
        $this->assertStringContainsString('مدل‌ها', $description);
        $this->assertStringContainsString('قطعات', $description);
        $this->assertStringContainsString('پارتس‌مال', $description);
    }

    public function test_catalog_model_pages_point_to_parts(): void
    {
        $description = MetaDescription::catalog('لیست مدل‌های هیوندای سانتافه');

        $this->assertStringContainsString('انتخاب مدل خودرو', $description);
        $this->assertStringContainsString('فهرست قطعات', $description);
        $this->assertStringContainsString('فروشندگان', $description);
    }

    public function test_catalog_part_pages_include_search_and_seller_context(): void
    {
        $description = MetaDescription::catalog('لیست تمام قطعات');

        $this->assertStringContainsString('جستجو در قطعات خودرو', $description);
        $this->assertStringContainsString('قیمت‌ها', $description);
        $this->assertStringContainsString('فروشندگان', $description);
    }

    public function test_catalog_descriptions_are_limited(): void
    {
        $description = MetaDescription::catalog(str_repeat('عنوان طولانی ', 20));

        $this->assertLessThanOrEqual(155, mb_strlen($description));
    }

    public function test_all_composed_descriptions_are_limited(): void
    {
        $descriptions = [
            MetaDescription::home(),
            MetaDescription::listing('فروشگاه‌های لوازم یدکی'),
            MetaDescription::listing('تعمیرگاه‌ها'),
            MetaDescription::listing('نمایندگی‌ها'),
            MetaDescription::page('درباره ما', null),
            MetaDescription::page('تماس با ما', null),
            MetaDescription::vehicleParts('هیوندای I10 2023'),
            MetaDescription::product('شیشه لچکی ثابت درب عقب هیوندای I10 2023'),
        ];

        foreach ($descriptions as $description) {
            $this->assertLessThanOrEqual(155, mb_strlen($description, 'UTF-8'), $description);
        }
    }
}
