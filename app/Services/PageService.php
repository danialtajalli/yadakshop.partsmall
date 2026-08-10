<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PageService
{
    /** @var list<string> */
    private const NAVIGATION_SLUGS = [
        'about',
        'contact',
        'guide',
        'terms',
    ];
    private const NAVIGATION_CACHE_TTL = 86400;

    /**
     * @return array{
     *     page: Page,
     *     title: string,
     *     breadcrumbs: list<array{label: string, url?: string, active?: bool}>,
     * }
     */
    public function getPageData(string $slug): array
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->first();

        if ($page === null || blank($page->slug)) {
            throw (new ModelNotFoundException)->setModel(Page::class, [$slug]);
        }

        $page->content = $this->sanitizeContent($page->content);

        return [
            'page' => $page,
            'title' => $page->title ?? $page->slug,
            'breadcrumbs' => [
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => $page->title ?? $page->slug, 'active' => true],
            ],
        ];
    }

    /**
     * @return Collection<int, Page>
     */
    public function getNavigationPages(): Collection
    {
        $pages = $this->rememberNavigationPages();

        return collect(self::NAVIGATION_SLUGS)
            ->map(fn (string $slug) => $pages[$slug] ?? null)
            ->filter()
            ->map(fn (array $page): object => (object) $page)
            ->values();
    }

    /**
     * @return array<string, array{title: ?string, slug: ?string}>
     */
    private function rememberNavigationPages(): array
    {
        $callback = fn (): array => Page::query()
            ->whereIn('slug', self::NAVIGATION_SLUGS)
            ->get(['title', 'slug'])
            ->keyBy('slug')
            ->map(fn (Page $page): array => [
                'title' => $page->title,
                'slug' => $page->slug,
            ])
            ->all();

        if (app()->environment('testing')) {
            return $callback();
        }

        return Cache::remember('pages:navigation:v1', self::NAVIGATION_CACHE_TTL, $callback);
    }

    private function sanitizeContent(?string $content): ?string
    {
        if ($content === null || $content === '') {
            return $content;
        }

        return str_replace('rn', '', $content);
    }
}
