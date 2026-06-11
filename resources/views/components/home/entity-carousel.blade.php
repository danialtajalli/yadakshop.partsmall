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
    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <x-ui.section-heading
            class="mb-0"
            :title="$title"
            :description="$description"
        />
        <a href="{{ $moreUrl }}" class="ps-btn-secondary shrink-0">
            {{ $moreLabel }}
            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
        </a>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-10 text-center">
            <p class="text-sm text-ink-muted">{{ $emptyMessage }}</p>
        </div>
    @else
        <div class="ps-carousel group" data-carousel>
            <button
                type="button"
                class="ps-carousel-nav ps-carousel-nav--prev"
                data-carousel-prev
                aria-label="اسلاید قبلی"
            >
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>

            <div class="ps-carousel-viewport px-2 sm:px-4" data-carousel-viewport dir="ltr" tabindex="0">
                <div class="ps-carousel-track" data-carousel-track>
                    @foreach ($items as $item)
                        <article class="ps-carousel-slide">
                            <a
                                href="{{ route($profileRoute, $item->slug) }}"
                                class="ps-card-interactive flex h-full flex-col items-center gap-3 p-4 text-center"
                            >
                                <div class="flex size-[4.5rem] shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand-soft to-accent-soft text-lg font-bold text-brand-dark ring-1 ring-line sm:size-20">
                                    @if ($item->logo ?? null)
                                        <img
                                            src="{{ $item->logo }}"
                                            alt="{{ $item->name }}"
                                            class="size-full object-cover"
                                            loading="lazy"
                                        >
                                    @else
                                        {{ mb_substr($item->name, 0, 1) }}
                                    @endif
                                </div>
                                <h3 class="line-clamp-2 min-h-[2.5rem] text-sm font-semibold leading-5 text-ink">
                                    {{ $item->name }}
                                </h3>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>

            <button
                type="button"
                class="ps-carousel-nav ps-carousel-nav--next"
                data-carousel-next
                aria-label="اسلاید بعدی"
            >
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    @endif
</section>
