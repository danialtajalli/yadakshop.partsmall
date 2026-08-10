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
        <a href="{{ $moreUrl }}" class="ps-btn-secondary inline-flex shrink-0 items-center gap-2">
            {{ $moreLabel }}
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7l-7-7 7-7" />
            </svg>
        </a>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-10 text-center">
            <p class="text-sm text-ink-muted">{{ $emptyMessage }}</p>
        </div>
    @else
        <div class="ps-carousel group" data-entity-carousel>
            <button
                type="button"
                class="ps-carousel-nav ps-carousel-nav--prev"
                data-entity-carousel-prev
                aria-label="اسلاید قبلی"
            >
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>

            <div class="ps-carousel-viewport px-2 sm:px-4" data-entity-carousel-viewport dir="ltr" tabindex="0">
                <div class="ps-carousel-track" data-entity-carousel-track>
                    @foreach ($items as $item)
                        @php
                            $name = data_get($item, 'name');
                            $slug = data_get($item, 'slug');
                            $profileUrl = data_get($item, 'profile_url') ?? (is_object($item) && method_exists($item, 'profileUrl') ? $item->profileUrl() : route($profileRoute, $slug));
                        @endphp
                        <article class="ps-carousel-slide">
                            <a
                                href="{{ $profileUrl }}"
                                class="ps-card-interactive flex h-full flex-col items-center gap-3 p-4 text-center"
                                draggable="false"
                            >
                                <x-ui.company-logo
                                    :name="$name"
                                    :logo-url="data_get($item, 'logo')"
                                    size="carousel"
                                    draggable="false"
                                />
                                <h3 class="line-clamp-2 min-h-[2.5rem] text-sm font-semibold leading-5 text-ink">
                                    {{ $name }}
                                </h3>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>

            <button
                type="button"
                class="ps-carousel-nav ps-carousel-nav--next"
                data-entity-carousel-next
                aria-label="اسلاید بعدی"
            >
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    @endif
</section>

@once
    @push('scripts')
        @unless (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            <script src="https://unpkg.com/embla-carousel@8/embla-carousel.umd.js"></script>
            <script src="https://unpkg.com/embla-carousel-autoplay@8/embla-carousel-autoplay.umd.js"></script>
            <script>
            (function () {
                const AUTO_PLAY_DELAY = 3000;

                const initEntityCarousels = function (root) {
                    if (typeof EmblaCarousel === 'undefined') {
                        return;
                    }

                    root.querySelectorAll('[data-entity-carousel]').forEach(function (carouselRoot) {
                        if (carouselRoot.dataset.entityCarouselReady === 'true') {
                            return;
                        }

                        const viewport = carouselRoot.querySelector('[data-entity-carousel-viewport]');

                        if (!viewport) {
                            return;
                        }

                        const prevButton = carouselRoot.querySelector('[data-entity-carousel-prev]');
                        const nextButton = carouselRoot.querySelector('[data-entity-carousel-next]');
                        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                        const plugins = [];
                        let autoplay = null;

                        if (!prefersReducedMotion && typeof EmblaCarouselAutoplay !== 'undefined') {
                            autoplay = EmblaCarouselAutoplay({
                                delay: AUTO_PLAY_DELAY,
                                stopOnInteraction: true,
                            });
                            plugins.push(autoplay);
                        }

                        const embla = EmblaCarousel(viewport, {
                            loop: true,
                            align: 'start',
                            direction: 'ltr',
                            dragFree: false,
                            containScroll: 'trimSnaps',
                        }, plugins);

                        carouselRoot.dataset.entityCarouselReady = 'true';

                        prevButton?.addEventListener('click', function () {
                            embla.scrollPrev();
                        });

                        nextButton?.addEventListener('click', function () {
                            embla.scrollNext();
                        });

                        if (autoplay) {
                            carouselRoot.addEventListener('mouseenter', autoplay.stop);
                            carouselRoot.addEventListener('mouseleave', autoplay.play);
                        }

                        embla.on('pointerDown', function () {
                            viewport.classList.add('is-dragging');
                        });

                        embla.on('pointerUp', function () {
                            viewport.classList.remove('is-dragging');
                        });
                    });
                };

                const boot = function () {
                    initEntityCarousels(document);
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', boot);
                } else {
                    boot();
                }

                window.initEntityCarousels = initEntityCarousels;
            })();
        </script>
        @endunless
    @endpush
@endonce
