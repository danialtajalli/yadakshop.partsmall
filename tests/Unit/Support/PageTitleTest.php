<?php

namespace Tests\Unit\Support;

use App\Support\PageTitle;
use PHPUnit\Framework\TestCase;

class PageTitleTest extends TestCase
{
    public function test_it_returns_title_unchanged_for_first_page(): void
    {
        $this->assertSame('شیشه جلو', PageTitle::appendPageNumber('شیشه جلو', 1));
    }

    public function test_it_appends_page_number_for_later_pages(): void
    {
        $this->assertSame('شیشه جلو - صفحه 3', PageTitle::appendPageNumber('شیشه جلو', 3));
    }
}
