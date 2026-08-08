@props([
    'logoUrl',
])

@if (filled($logoUrl))
    <div
        {{ $attributes->merge(['class' => 'pointer-events-none absolute inset-y-0 end-0 w-[48%] max-w-sm overflow-hidden sm:w-[40%]']) }}
        aria-hidden="true"
    >
        <img
            src="{{ $logoUrl }}"
            alt=""
            class="absolute inset-0 size-full object-contain object-center p-3 opacity-[0.12] sm:p-5 sm:opacity-[0.14]"
            loading="lazy"
            decoding="async"
        >
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/35 to-white"></div>
    </div>
@endif
