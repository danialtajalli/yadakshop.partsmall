@props([
    'part',
    'type' => null,
])

@php
    use App\Support\PartIcon;

    $iconType = $type ?? PartIcon::type($part);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-lg bg-brand-soft text-brand']) }} aria-hidden="true">
    <svg class="size-[1.125rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        @switch($iconType)
            @case('engine')
                <path d="M9 8h6l1 3v5H8v-5l1-3Z" />
                <path d="M9 8V6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                <path d="M12 11v2" />
                @break

            @case('brake')
                <circle cx="12" cy="12" r="7" />
                <circle cx="12" cy="12" r="2.5" />
                <path d="M12 5v2M12 17v2M5 12h2M17 12h2" />
                @break

            @case('suspension')
                <path d="M6 5v14M18 5v14" />
                <path d="M6 8h12M6 12h12M6 16h12" />
                @break

            @case('electric')
                <path d="M13 3 7 13h5l-1 8 8-12h-6l0-6Z" />
                @break

            @case('filter')
                <path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z" />
                @break

            @case('gearbox')
                <circle cx="8" cy="12" r="3" />
                <circle cx="16" cy="12" r="3" />
                <path d="M11 12h2" />
                @break

            @case('body')
                <path d="M5 15h14l-1.5-4.5a1 1 0 0 0-.95-.7H7.45a1 1 0 0 0-.95.7L5 15Z" />
                <path d="M7 15l1-3h8l1 3" />
                <circle cx="8" cy="16.5" r="1.25" />
                <circle cx="16" cy="16.5" r="1.25" />
                @break

            @case('tire')
                <circle cx="12" cy="12" r="7" />
                <circle cx="12" cy="12" r="3" />
                <path d="M12 5v2M12 17v2M5 12h2M17 12h2" />
                @break

            @case('cooling')
                <path d="M12 3v18" />
                <path d="M8 7h8M7 12h10M8 17h8" />
                @break

            @default
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z" />
        @endswitch
    </svg>
</span>
