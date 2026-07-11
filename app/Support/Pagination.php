<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;

class Pagination
{
    public static function pageUrl(PaginatorContract $paginator, int $page): string
    {
        $query = collect(request()->query())
            ->except('page')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        if ($page <= 1) {
            $base = $paginator->path();

            return $query === [] ? $base : $base.'?'.http_build_query($query);
        }

        $query['page'] = $page;

        return $paginator->path().'?'.http_build_query($query);
    }

    public static function previousPageUrl(PaginatorContract $paginator): ?string
    {
        if ($paginator->onFirstPage()) {
            return null;
        }

        return self::pageUrl($paginator, $paginator->currentPage() - 1);
    }

    public static function nextPageUrl(PaginatorContract $paginator): ?string
    {
        if (! $paginator->hasMorePages()) {
            return null;
        }

        return self::pageUrl($paginator, $paginator->currentPage() + 1);
    }

    /**
     * @return list<int|string>
     */
    public static function compactPageItems(int $currentPage, int $lastPage): array
    {
        if ($lastPage <= 4) {
            return range(1, $lastPage);
        }

        if ($currentPage <= 3) {
            $items = [1, 2, 3];

            if ($currentPage === 3) {
                $items[] = 4;
            }

            $items[] = '...';
            $items[] = $lastPage;

            return $items;
        }

        if ($currentPage >= $lastPage - 2) {
            $items = [1, '...'];

            if ($currentPage === $lastPage - 2 && $lastPage - 3 > 3) {
                $items[] = $lastPage - 3;
            }

            return array_merge($items, range($lastPage - 2, $lastPage));
        }

        return [1, '...', $currentPage - 1, $currentPage, $currentPage + 1, '...', $lastPage];
    }

    /**
     * @param  list<array{label: string, url?: string, active?: bool, emphasized?: bool}>  $baseCrumbs
     * @return list<array{label: string, url?: string, active?: bool, emphasized?: bool}>
     */
    public static function buildBreadcrumbs(array $baseCrumbs, int $page, string $firstPageUrl): array
    {
        $crumbs = array_values($baseCrumbs);
        $lastIndex = count($crumbs) - 1;

        if ($page <= 1) {
            if ($lastIndex >= 0) {
                unset($crumbs[$lastIndex]['url']);
                $crumbs[$lastIndex]['active'] = true;
            }

            return $crumbs;
        }

        if ($lastIndex >= 0) {
            $crumbs[$lastIndex]['url'] = $firstPageUrl;
            unset($crumbs[$lastIndex]['active']);
        }

        $crumbs[] = [
            'label' => 'صفحه '.$page,
            'active' => true,
        ];

        return $crumbs;
    }
}
