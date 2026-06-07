@props(['items' => []])

{{--
    Site breadcrumb — public pages only.
    Each item: ['label' => string, 'url' => ?string, 'active' => ?bool]
--}}

<nav {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap items-center gap-1.5 text-sm text-ink-muted']) }} aria-label="مسیر">
    @foreach ($items as $index => $item)
        @if ($index > 0)
            <span class="text-line select-none">/</span>
        @endif

        @if (! empty($item['url']))
            <a
                href="{{ $item['url'] }}"
                class="rounded-md px-1.5 py-0.5 transition hover:bg-brand-soft hover:text-brand"
            >
                {{ $item['label'] }}
            </a>
        @else
            <span @class([
                'px-1.5 py-0.5',
                'rounded-md font-medium text-ink' => $item['emphasized'] ?? false,
                'text-ink' => $item['active'] ?? false,
            ])>{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
