@props([
    'logoUrl',
])

@if (filled($logoUrl))
    <div
        {{ $attributes->merge(['class' => 'pointer-events-none absolute inset-y-0 end-0 w-[55%] max-w-md sm:w-[45%]']) }}
        aria-hidden="true"
    >
        <img
            src="{{ $logoUrl }}"
            alt=""
            class="absolute end-[-10%] top-1/2 h-[140%] w-auto max-w-none -translate-y-1/2 object-contain opacity-[0.12] sm:opacity-[0.14]"
            loading="lazy"
            decoding="async"
        >
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-white"></div>
    </div>
@endif
