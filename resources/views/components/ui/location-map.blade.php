@props([
    'latitude',
    'longitude',
    'title' => null,
    'address' => null,
    'embedded' => false,
])

@once
    @push('head')
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        >
        <style>
            .ps-location-map {
                height: 16rem;
                z-index: 0;
            }

            @media (min-width: 640px) {
                .ps-location-map {
                    height: 20rem;
                }
            }

            .ps-location-map--embedded {
                height: 12rem;
            }

            @media (min-width: 1024px) {
                .ps-location-map--embedded {
                    height: 100%;
                    min-height: 11rem;
                }
            }

            .ps-location-map .leaflet-container {
                font-family: inherit;
                border-radius: 0.75rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>
        <script>
            (function () {
                const initMaps = function () {
                    if (typeof L === 'undefined') {
                        return;
                    }

                    document.querySelectorAll('[data-location-map]').forEach(function (element) {
                        if (element.dataset.mapInitialized === 'true') {
                            return;
                        }

                        const latitude = parseFloat(element.dataset.lat);
                        const longitude = parseFloat(element.dataset.lng);

                        if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
                            return;
                        }

                        element.dataset.mapInitialized = 'true';

                        const map = L.map(element, {
                            scrollWheelZoom: false,
                        }).setView([latitude, longitude], 15);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        }).addTo(map);

                        const popupTitle = element.dataset.title || '';

                        if (popupTitle) {
                            L.marker([latitude, longitude]).addTo(map).bindPopup(popupTitle);
                        } else {
                            L.marker([latitude, longitude]).addTo(map);
                        }

                        window.setTimeout(function () {
                            map.invalidateSize();
                        }, 150);
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initMaps);
                } else {
                    initMaps();
                }
            })();
        </script>
    @endpush
@endonce

@php
    $mapClass = $embedded
        ? 'ps-location-map ps-location-map--embedded w-full overflow-hidden rounded-xl border border-line'
        : 'ps-location-map w-full overflow-hidden rounded-xl border border-line';
@endphp

@if ($embedded)
    <div {{ $attributes->merge(['class' => 'flex h-full min-w-0 flex-col']) }}>
        <div
            data-location-map
            data-lat="{{ $latitude }}"
            data-lng="{{ $longitude }}"
            @if ($title) data-title="{{ $title }}" @endif
            class="{{ $mapClass }}"
            role="img"
            aria-label="نقشه موقعیت {{ $title ?? '' }}"
        ></div>

        <a
            href="https://www.google.com/maps?q={{ $latitude }},{{ $longitude }}"
            target="_blank"
            rel="noopener"
            class="ps-btn-secondary mt-3 inline-flex w-full items-center justify-center gap-2 sm:w-auto"
        >
            <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
            مسیریابی در گوگل مپ
        </a>
    </div>
@else
    <section {{ $attributes->merge(['class' => 'ps-card overflow-hidden']) }}>
        <div class="border-b border-line px-5 py-4 sm:px-6">
            <h2 class="text-base font-bold text-ink">موقعیت روی نقشه</h2>
            @if ($address)
                <p class="mt-1 text-sm text-ink-muted">{{ $address }}</p>
            @endif
        </div>

        <div class="p-4 sm:p-5">
            <div
                data-location-map
                data-lat="{{ $latitude }}"
                data-lng="{{ $longitude }}"
                @if ($title) data-title="{{ $title }}" @endif
                class="{{ $mapClass }}"
                role="img"
                aria-label="نقشه موقعیت {{ $title ?? '' }}"
            ></div>

            <a
                href="https://www.google.com/maps?q={{ $latitude }},{{ $longitude }}"
                target="_blank"
                rel="noopener"
                class="ps-btn-secondary mt-3 inline-flex items-center justify-center gap-2"
            >
                <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                مسیریابی در گوگل مپ
            </a>
        </div>
    </section>
@endif
