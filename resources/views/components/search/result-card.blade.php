@props(['item'])

<a
    href="{{ $item['url'] }}"
    {{ $attributes->merge(['class' => 'ps-card-interactive flex h-full min-w-0 flex-col overflow-hidden p-4']) }}
>
    <div class="mb-3 flex items-start gap-3">
        <x-ui.company-logo
            :name="$item['title']"
            :logo-url="$item['image_url'] ?? null"
            size="listing"
        />

        <div class="min-w-0 flex-1">
            <span class="text-[10px] font-bold text-brand">{{ $item['type'] }}</span>
            <span class="mt-0.5 block truncate text-sm font-semibold text-ink">{{ $item['title'] }}</span>

            @if ($item['subtitle'])
                <span class="mt-0.5 block truncate text-xs text-ink-muted">{{ $item['subtitle'] }}</span>
            @endif
        </div>
    </div>
</a>
