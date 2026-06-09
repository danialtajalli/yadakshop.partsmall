@if ($paginator->hasPages())
    <nav role="navigation" aria-label="صفحه‌بندی" class="flex items-center justify-center">
        <div class="inline-flex flex-wrap items-center gap-1 rounded-2xl border border-line bg-white p-1 shadow-card">
            @if ($paginator->onFirstPage())
                <span class="rounded-xl px-3 py-2 text-sm text-ink-muted/50">قبلی</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-surface">قبلی</a>
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
                            <a href="{{ $url }}" class="rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-surface">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-surface">بعدی</a>
            @else
                <span class="rounded-xl px-3 py-2 text-sm text-ink-muted/50">بعدی</span>
            @endif
        </div>
    </nav>
@endif
