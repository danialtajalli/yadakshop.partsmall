@props([
    'title',
    'description' => null,
    'items',
    'moreUrl',
    'moreLabel' => 'مشاهده همه',
    'profileRoute' => null,
    'emptyMessage' => 'موردی برای نمایش ثبت نشده است.',
])

<section {{ $attributes->merge(['class' => 'mb-12']) }}>
    <x-ui.section-heading
        class="mb-5"
        :title="$title"
        :description="$description"
        :more-url="$moreUrl"
        :more-label="$moreLabel"
    />

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        @foreach ($items as $item)
            <a
                href="{{ route($profileRoute, $item->slug) }}"
                class="ps-card-interactive flex items-center gap-3 p-3"
            >
                <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-line bg-white text-sm font-bold text-brand shadow-sm">
                    @if ($item->logo ?? null)
                        <img
                            src="{{ $item->logo }}"
                            alt="{{ $item->name }}"
                            class="size-full object-contain p-1.5"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        {{ mb_substr($item->name, 0, 1) }}
                    @endif
                </span>

                <span class="min-w-0">
                    <span class="line-clamp-2 block text-sm font-semibold leading-5 text-ink">
                        {{ $item->name }}
                    </span>
                </span>
            </a>
        @endforeach

        <a
            href="{{ route('page.show', ['slug' => 'register']) }}"
            class="flex items-center gap-3 rounded-2xl border border-dashed border-brand/35 bg-white p-3 text-start shadow-card transition hover:border-brand/60 hover:bg-brand-soft/20"
        >
            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-brand/25 bg-brand-soft/20 text-brand">
                <svg class="size-7" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="6" y="7" width="20" height="18" rx="4" />
                    <path d="M11 20h10" />
                    <path d="M12 16l3-3 3 3 2-2 3 3" />
                    <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
                </svg>
            </span>

            <span class="min-w-0">
                <span class="block text-sm font-bold text-ink">لوگوی شما اینجا</span>
                <span class="mt-0.5 block text-xs text-ink-muted">ثبت‌نام و نمایش در پارتس‌مال</span>
            </span>
        </a>
    </div>

    @if ($items->isEmpty())
        <p class="mt-3 text-sm text-ink-muted">{{ $emptyMessage }}</p>
    @endif
</section>
