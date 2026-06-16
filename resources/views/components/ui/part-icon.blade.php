@props([
    'part',
    'type' => null,
])

@php
    use App\Support\PartIcon;

    $iconType = $type ?? PartIcon::type($part);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-lg bg-brand-soft text-brand']) }} aria-hidden="true">
    <x-ui.part-icon-svg :icon="$iconType" class="size-[1.125rem]" />
</span>
