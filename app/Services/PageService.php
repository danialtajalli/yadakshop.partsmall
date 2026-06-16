<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class PageService
{
    /** @var list<string> */
    private const NAVIGATION_SLUGS = [
        'about',
        'contact',
        'guide',
        'terms',
    ];

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
        $pages = Page::query()
            ->whereIn('slug', self::NAVIGATION_SLUGS)
            ->get()
            ->keyBy('slug');

        return collect(self::NAVIGATION_SLUGS)
            ->map(fn (string $slug) => $pages->get($slug))
            ->filter()
            ->values();
    }

    private function sanitizeContent(?string $content): ?string
    {
        if ($content === null || $content === '') {
            return $content;
        }

        return str_replace('rn', '', $content);
    }
}
