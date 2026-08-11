@props(['items' => []])

{{--
    SEO breadcrumb — semantic nav + Schema.org BreadcrumbList (JSON-LD).
    Each item: ['label' => string, 'url' => ?string, 'active' => ?bool, 'emphasized' => ?bool]
    Items with a url are always rendered as links, including the current page when appropriate.
--}}

@php
    $siteName = 'پارتس‌مال';
    $crumbs = collect($items)
        ->map(function (array $item) use ($siteName): array {
            if (($item['label'] ?? null) === 'خانه') {
                $item['label'] = $siteName;
            }

            return $item;
        })
        ->values();
    $lastIndex = $crumbs->count() - 1;

    $schemaElements = [];

    foreach ($crumbs as $index => $item) {
        $isLast = $index === $lastIndex;
        $href = $item['url'] ?? null;

        if (! $href && $isLast) {
            $href = url()->current();
        }

        $element = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['label'],
        ];

        if ($href) {
            $element['item'] = str_starts_with((string) $href, 'http')
                ? $href
                : url($href);
        }

        $schemaElements[] = $element;
    }

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $schemaElements,
    ];
@endphp

@if ($crumbs->isNotEmpty())
    @push('head')
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endpush

    <nav {{ $attributes->merge(['class' => 'mb-4 text-xs font-medium text-ink-muted sm:text-sm']) }} aria-label="مسیر صفحه">
        <ol class="m-0 flex list-none flex-wrap items-center gap-1.5 p-0">
            @foreach ($crumbs as $index => $item)
                @php
                    $isLast = $index === $lastIndex;
                    $href = $item['url'] ?? null;
                    $isEmphasized = (bool) ($item['emphasized'] ?? false);
                    $isActive = (bool) ($item['active'] ?? false);
                @endphp

                <li class="flex items-center gap-1.5">
                    @if ($index > 0)
                        <span class="text-line select-none" aria-hidden="true">/</span>
                    @endif

                    @if ($href)
                        <a
                            href="{{ $href }}"
                            @class([
                                'transition hover:text-brand',
                                'font-medium text-ink' => $isEmphasized || $isActive || $isLast,
                            ])
                            @if ($isLast) aria-current="page" @endif
                        >
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span
                            @class([
                                'font-medium text-ink' => $isEmphasized || $isActive || $isLast,
                            ])
                            @if ($isLast) aria-current="page" @endif
                        >{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
