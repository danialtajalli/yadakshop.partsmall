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

    public function test_page_url_includes_post_body_filters(): void
    {
        $request = Request::create('/shops', 'POST', [
            'q' => 'یدک',
            'state_id' => 3,
            '_token' => 'secret',
        ]);
        $this->app->instance('request', $request);

        $paginator = new LengthAwarePaginator([], 30, 12, 1, ['path' => 'http://localhost/shops']);

        $this->assertSame(
            'http://localhost/shops?q=%DB%8C%D8%AF%DA%A9&state_id=3&page=2',
            Pagination::pageUrl($paginator, 2),
        );
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

    public function test_compact_page_items_shows_all_pages_for_short_lists(): void
    {
        $this->assertSame([1, 2, 3, 4], Pagination::compactPageItems(2, 4));
    }

    public function test_compact_page_items_shows_leading_window_near_the_start(): void
    {
        $this->assertSame([1, 2, 3, '...', 10], Pagination::compactPageItems(2, 10));
        $this->assertSame([1, 2, 3, 4, '...', 10], Pagination::compactPageItems(3, 10));
    }

    public function test_compact_page_items_shows_trailing_window_near_the_end(): void
    {
        $this->assertSame([1, '...', 7, 8, 9, 10], Pagination::compactPageItems(8, 10));
        $this->assertSame([1, '...', 8, 9, 10], Pagination::compactPageItems(9, 10));
    }

    public function test_compact_page_items_shows_neighbors_when_current_page_touches_ellipsis(): void
    {
        $this->assertSame([1, '...', 3, 4, 5, '...', 10], Pagination::compactPageItems(4, 10));
        $this->assertSame([1, '...', 4, 5, 6, '...', 10], Pagination::compactPageItems(5, 10));
    }
}
