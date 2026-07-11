@php
    use App\Support\Pagination as PaginationUrl;
@endphp

@if ($paginator->hasPages())
    @php
        $pageItems = PaginationUrl::compactPageItems($paginator->currentPage(), $paginator->lastPage());
    @endphp

    <nav role="navigation" aria-label="صفحه‌بندی" class="flex items-center justify-center">
        <div class="inline-flex max-w-full flex-nowrap items-center gap-1 overflow-x-auto rounded-2xl border border-line bg-white p-1 shadow-card">
            @if ($paginator->onFirstPage())
                <span class="shrink-0 rounded-xl px-2.5 py-2 text-sm text-ink-muted/50">قبلی</span>
            @else
                <a href="{{ PaginationUrl::previousPageUrl($paginator) }}" class="shrink-0 rounded-xl px-2.5 py-2 text-sm text-ink transition hover:bg-surface">قبلی</a>
            @endif

            @foreach ($pageItems as $page)
                @if ($page === '...')
                    <span class="shrink-0 px-1 text-sm text-ink-muted">...</span>
                @elseif ($page == $paginator->currentPage())
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
@endif
