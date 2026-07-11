@php
    use App\Support\Pagination as PaginationUrl;
@endphp

@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        $mobilePageItems = [];

        if ($lastPage <= 4) {
            $mobilePageItems = range(1, $lastPage);
        } elseif ($currentPage <= 3) {
            $mobilePageItems = [1, 2, 3, '...', $lastPage];
        } elseif ($currentPage >= $lastPage - 2) {
            $mobilePageItems = [1, '...', $lastPage - 2, $lastPage - 1, $lastPage];
        } else {
            $mobilePageItems = [1, '...', $currentPage, '...', $lastPage];
        }
    @endphp

    {{-- Mobile: compact single row with ellipsis --}}
    <nav role="navigation" aria-label="صفحه‌بندی" class="flex items-center justify-center sm:hidden">
        <div class="inline-flex max-w-full flex-nowrap items-center gap-1 overflow-x-auto rounded-2xl border border-line bg-white p-1 shadow-card">
            @if ($paginator->onFirstPage())
                <span class="shrink-0 rounded-xl px-2.5 py-2 text-sm text-ink-muted/50">قبلی</span>
            @else
                <a href="{{ PaginationUrl::previousPageUrl($paginator) }}" class="shrink-0 rounded-xl px-2.5 py-2 text-sm text-ink transition hover:bg-surface">قبلی</a>
            @endif

            @foreach ($mobilePageItems as $page)
                @if ($page === '...')
                    <span class="shrink-0 px-1 text-sm text-ink-muted">...</span>
                @elseif ($page == $currentPage)
                    <span class="shrink-0 rounded-xl bg-brand px-2.5 py-2 text-sm font-medium text-white">{{ $page }}</span>
                @else
                    <a href="{{ PaginationUrl::pageUrl($paginator, $page) }}" class="shrink-0 rounded-xl px-2.5 py-2 text-sm text-ink transition hover:bg-surface">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ PaginationUrl::nextPageUrl($paginator) }}" class="shrink-0 rounded-xl px-2.5 py-2 text-sm text-ink transition hover:bg-surface">بعدی</a>
            @else
                <span class="shrink-0 rounded-xl px-2.5 py-2 text-sm text-ink-muted/50">بعدی</span>
            @endif
        </div>
    </nav>

    {{-- Desktop: full pagination --}}
    <nav role="navigation" aria-label="صفحه‌بندی" class="hidden items-center justify-center sm:flex">
        <div class="inline-flex flex-wrap items-center gap-1 rounded-2xl border border-line bg-white p-1 shadow-card">
            @if ($paginator->onFirstPage())
                <span class="rounded-xl px-3 py-2 text-sm text-ink-muted/50">قبلی</span>
            @else
                <a href="{{ PaginationUrl::previousPageUrl($paginator) }}" class="rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-surface">قبلی</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 text-sm text-ink-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="rounded-xl bg-brand px-3 py-2 text-sm font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ PaginationUrl::pageUrl($paginator, $page) }}" class="rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-surface">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ PaginationUrl::nextPageUrl($paginator) }}" class="rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-surface">بعدی</a>
            @else
                <span class="rounded-xl px-3 py-2 text-sm text-ink-muted/50">بعدی</span>
            @endif
        </div>
    </nav>
@endif
