@props([
    'links' => null,
    'items' => [],
    'colorfulBranded' => false,
])

@php
    use App\Support\ContactIcon;

    if ($links) {
        $linkGroups = $links->groupBy(fn ($link) => $link->link_type->value);
    } else {
        $linkGroups = collect($items)->groupBy(fn (array $item) => $item['label'] ?? $item['kind'] ?? 'social');
    }
@endphp

<ul {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($links)
        @foreach ($linkGroups as $linksInGroup)
            @php
                $linkType = $linksInGroup->first()->link_type;
                $isGrouped = $linksInGroup->count() > 1;
                $sponsoredShopIds = [1, 2, 3];
                $iconClass = $colorfulBranded && $linkType->brandIconClass()
                    ? $linkType->brandIconClass()
                    : 'text-ink-muted';
            @endphp

            <li @class(['rounded-xl border border-line', 'overflow-hidden' => $isGrouped])>
                @if ($isGrouped)
                    <div class="flex items-center justify-between gap-3 border-b border-line bg-surface/50 px-3 py-2.5">
                        <span class="font-medium text-ink">{{ $linkType->label() }}</span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white text-base {{ $iconClass }}">
                            <i class="{{ $linkType->icon() }}" aria-hidden="true"></i>
                        </span>
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($linksInGroup as $link)
                            <li>
                                <a
                                    href="{{ $linkType->actionUrl($link->name) }}"
                                    target="_blank"
                                    @if (! in_array($link->shop?->id, $sponsoredShopIds, true))
                                        rel="noopener nofollow sponsored"
                                    @else
                                        rel="noopener"
                                    @endif
                                    class="block truncate px-3 py-2.5 text-sm text-brand transition hover:bg-brand-soft/40"
                                    dir="ltr"
                                >
                                    {{ $linkType->displayName($link->name) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    @php $link = $linksInGroup->first(); @endphp
                    <a
                        href="{{ $linkType->actionUrl($link->name) }}"
                        target="_blank"
                        @if (! in_array($link->shop?->id, $sponsoredShopIds, true))
                            rel="noopener nofollow sponsored"
                        @else
                            rel="noopener"
                        @endif
                        class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                    >
                        <span class="min-w-0 truncate font-medium text-ink">{{ $linkType->label() }}</span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base {{ $iconClass }}">
                            <i class="{{ $linkType->icon() }}" aria-hidden="true"></i>
                        </span>
                    </a>
                @endif
            </li>
        @endforeach
    @else
        @foreach ($linkGroups as $itemsInGroup)
            @php
                $item = $itemsInGroup->first();
                $label = $item['label'] ?? 'لینک';
                $isGrouped = $itemsInGroup->count() > 1;
            @endphp

            <li @class(['rounded-xl border border-line', 'overflow-hidden' => $isGrouped])>
                @if ($isGrouped)
                    <div class="flex items-center justify-between gap-3 border-b border-line bg-surface/50 px-3 py-2.5">
                        <span class="font-medium text-ink">{{ $label }}</span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white text-base text-brand">
                            <i class="{{ ContactIcon::forKind($item['kind'] ?? 'social') }}" aria-hidden="true"></i>
                        </span>
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($itemsInGroup as $groupItem)
                            <li>
                                <a
                                    href="{{ $groupItem['url'] }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="block truncate px-3 py-2.5 text-sm text-brand transition hover:bg-brand-soft/40"
                                    dir="ltr"
                                >
                                    {{ $groupItem['value'] ?? $groupItem['url'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <a
                        href="{{ $item['url'] }}"
                        target="_blank"
                        rel="noopener"
                        class="flex items-center justify-between gap-3 px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                    >
                        <span class="font-medium text-ink">{{ $label }}</span>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface text-base text-brand">
                            <i class="{{ ContactIcon::forKind($item['kind'] ?? 'social') }}" aria-hidden="true"></i>
                        </span>
                    </a>
                @endif
            </li>
        @endforeach
    @endif
</ul>
