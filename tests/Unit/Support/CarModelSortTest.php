<?php

namespace Tests\Unit\Support;

use App\Support\CarModelSort;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CarModelSortTest extends TestCase
{
    public function test_it_orders_miladi_then_shamsi_then_cc_then_rest(): void
    {
        $sorted = CarModelSort::prioritize(collect([
            ['slug' => 'sport', 'name' => 'اسپرت', 'url' => '/a', 'category_slug' => 'term'],
            ['slug' => '1600', 'name' => '۱۶۰۰', 'url' => '/b', 'category_slug' => 'cc'],
            ['slug' => '1401', 'name' => 'سال ۱۴۰۱', 'url' => '/c', 'category_slug' => 'year-shamsi'],
            ['slug' => '2018', 'name' => 'سال ۲۰۱۸', 'url' => '/d', 'category_slug' => 'year-miladi'],
            ['slug' => '2020', 'name' => 'سال ۲۰۲۰', 'url' => '/e', 'category_slug' => 'year-miladi'],
            ['slug' => '2000', 'name' => '۲۰۰۰', 'url' => '/f', 'category_slug' => 'cc'],
            ['slug' => '1402', 'name' => 'سال ۱۴۰۲', 'url' => '/g', 'category_slug' => 'year-fa'],
        ]));

        $this->assertSame(
            ['2020', '2018', '1402', '1401', '1600', '2000', 'sport'],
            array_column($sorted, 'slug'),
        );
    }

    public function test_bucket_for_category_matches_priority_order(): void
    {
        $this->assertSame(0, CarModelSort::bucketForCategory('year-miladi'));
        $this->assertSame(1, CarModelSort::bucketForCategory('year-shamsi'));
        $this->assertSame(1, CarModelSort::bucketForCategory('year-fa'));
        $this->assertSame(2, CarModelSort::bucketForCategory('cc'));
        $this->assertSame(3, CarModelSort::bucketForCategory('term'));
    }
}
