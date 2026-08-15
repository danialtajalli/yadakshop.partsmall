<?php

namespace Tests\Unit;

use App\Support\MetaDescription;
use PHPUnit\Framework\TestCase;

class MetaDescriptionTest extends TestCase
{
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
}
