<?php

namespace Tests\Unit\Support;

use App\Support\Pagination;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    public function test_page_url_omits_page_query_for_first_page(): void
    {
        $request = Request::create('/shops', 'GET', ['page' => 2, 'q' => 'یدک']);
        $this->app->instance('request', $request);

        $paginator = new LengthAwarePaginator([], 30, 12, 2, ['path' => 'http://localhost/shops']);

        $this->assertSame('http://localhost/shops?q=%DB%8C%D8%AF%DA%A9', Pagination::pageUrl($paginator, 1));
    }

    public function test_page_url_includes_page_query_for_later_pages(): void
    {
        $request = Request::create('/shops', 'GET', ['q' => 'یدک']);
        $this->app->instance('request', $request);

        $paginator = new LengthAwarePaginator([], 30, 12, 1, ['path' => 'http://localhost/shops']);

        $this->assertSame('http://localhost/shops?q=%DB%8C%D8%AF%DA%A9&page=3', Pagination::pageUrl($paginator, 3));
    }

    public function test_previous_page_url_from_second_page_points_to_first_page_without_page_param(): void
    {
        $request = Request::create('/shops', 'GET', ['page' => 2]);
        $this->app->instance('request', $request);

        $paginator = new LengthAwarePaginator([], 30, 12, 2, ['path' => 'http://localhost/shops']);

        $this->assertSame('http://localhost/shops', Pagination::previousPageUrl($paginator));
    }

    public function test_build_breadcrumbs_keeps_terminal_label_on_first_page(): void
    {
        $breadcrumbs = Pagination::buildBreadcrumbs(
            [
                ['label' => 'خانه', 'url' => '/'],
                ['label' => 'فروشگاه‌ها'],
            ],
            1,
            '/shops',
        );

        $this->assertSame([
            ['label' => 'خانه', 'url' => '/'],
            ['label' => 'فروشگاه‌ها', 'active' => true],
        ], $breadcrumbs);
    }

    public function test_build_breadcrumbs_appends_unlinked_page_number_on_later_pages(): void
    {
        $breadcrumbs = Pagination::buildBreadcrumbs(
            [
                ['label' => 'خانه', 'url' => '/'],
                ['label' => 'طبق'],
            ],
            2,
            '/part/arm',
        );

        $this->assertSame([
            ['label' => 'خانه', 'url' => '/'],
            ['label' => 'طبق', 'url' => '/part/arm'],
            ['label' => 'صفحه 2', 'active' => true],
        ], $breadcrumbs);
    }
}
