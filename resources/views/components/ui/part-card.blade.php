@props([
    'part',
    'url',
])

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'ps-card-interactive flex items-center gap-2.5 p-3']) }}>
    <x-ui.part-icon :part="$part" class="size-8 shrink-0 rounded-lg" />
    <div class="min-w-0 flex-1">
        <h3 class="truncate text-sm font-semibold leading-5 text-ink">{{ $part->name }}</h3>
        @if ($part->partsCategory)
            <p class="mt-0.5 truncate text-[11px] font-medium text-brand">{{ $part->partsCategory->name }}</p>
        @endif
    </div>
</a>
