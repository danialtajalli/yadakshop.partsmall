@props([
    'links' => null,
    'items' => [],
    'resolveUrl' => null,
])

@php
    use App\Support\ContactIcon;
@endphp

<ul {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($links)
        @foreach ($links as $link)
            @php
                $href = $resolveUrl ? ($resolveUrl)($link->name) : (str_starts_with($link->name, 'http') ? $link->name : '#');
            @endphp
            <li>
                <a
                    href="{{ $href }}"
                    target="_blank"
                    rel="noopener"
                    class="flex items-center justify-between gap-3 rounded-xl border border-line px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                >
                    <span class="font-medium text-ink">{{ $link->link_type->label() }}</span>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base text-brand">
                        <i class="{{ $link->link_type->icon() }}" aria-hidden="true"></i>
                    </span>
                </a>
            </li>
        @endforeach
    @else
        @foreach ($items as $item)
            <li>
                <a
                    href="{{ $item['url'] }}"
                    target="_blank"
                    rel="noopener"
                    class="flex items-center justify-between gap-3 rounded-xl border border-line px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                >
                    <span class="font-medium text-ink">{{ $item['label'] }}</span>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base text-brand">
                        <i class="{{ ContactIcon::forKind($item['kind'] ?? 'social') }}" aria-hidden="true"></i>
                    </span>
                </a>
            </li>
        @endforeach
    @endif
</ul>
