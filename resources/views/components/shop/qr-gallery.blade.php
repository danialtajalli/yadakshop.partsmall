@props([
    'url',
    'imageUrl' => null,
    'title' => '',
    'variant' => 'sidebar',
    'dialogId' => null,
    'captionUrl' => null,
    'captionLabel' => null,
])

@php
    $resolvedDialogId = $dialogId ?? ('shop-qr-gallery-'.md5($url));
    $hasQrImage = filled($imageUrl);
@endphp

@if ($variant === 'dialog')
    <dialog
        id="{{ $resolvedDialogId }}"
        data-ps-qr-gallery
        data-ps-qr-url="{{ $url }}"
        data-ps-qr-title="{{ $title }}"
        @if ($hasQrImage) data-ps-qr-image-url="{{ $imageUrl }}" @endif
        class="ps-qr-gallery fixed inset-0 z-[70] m-0 max-h-none w-full max-w-none border-0 bg-transparent p-0 shadow-none backdrop:bg-ink/75 open:flex open:items-center open:justify-center"
        aria-label="نمایش QR صفحه فروشگاه"
    >
        <div class="ps-qr-gallery__shell relative flex max-h-[calc(100dvh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-white/10 bg-ink shadow-2xl">
            <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white">QR صفحه فروشگاه</p>
                    @if ($title)
                        <p class="mt-0.5 truncate text-xs text-white/60">{{ $title }}</p>
                    @endif
                </div>
                <button
                    type="button"
                    class="flex size-9 shrink-0 items-center justify-center rounded-lg text-white/70 transition hover:bg-white/10 hover:text-white"
                    data-ps-qr-close
                    aria-label="بستن"
                >
                    <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                </button>
            </div>

            <div class="flex flex-1 flex-col items-center justify-center px-4 py-6 sm:px-6">
                <div
                    class="ps-qr-gallery__stage flex max-h-[min(60dvh,24rem)] w-full items-center justify-center overflow-hidden"
                    data-ps-qr-stage
                >
                    <div class="ps-qr-gallery__image-wrap origin-center transition-transform duration-200 ease-out" data-ps-qr-image-wrap>
                        @if ($hasQrImage)
                            <img
                                src="{{ $imageUrl }}"
                                alt="QR صفحه {{ $title }}"
                                width="280"
                                height="280"
                                class="max-h-full max-w-full rounded-xl bg-white p-3 shadow-lg ring-1 ring-white/20"
                                data-ps-qr-image
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <x-shop.qr-placeholder
                                :seed="$url"
                                :size="280"
                                class="max-h-full max-w-full rounded-xl bg-white p-3 shadow-lg ring-1 ring-white/20"
                                data-ps-qr-image
                            />
                        @endif
                    </div>
                </div>

                <p class="mt-4 max-w-full truncate text-center text-xs text-white/60" dir="ltr">{{ $url }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2 border-t border-white/10 p-3 sm:grid-cols-5 sm:gap-2">
                <button type="button" class="ps-qr-gallery__tool" data-ps-qr-zoom-out aria-label="کوچک‌تر">
                    <i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>
                    <span>کوچک‌تر</span>
                </button>
                <button type="button" class="ps-qr-gallery__tool" data-ps-qr-zoom-in aria-label="بزرگ‌تر">
                    <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
                    <span>بزرگ‌تر</span>
                </button>
                <button type="button" class="ps-qr-gallery__tool" data-ps-qr-reset aria-label="بازنشانی زوم">
                    <i class="fa-solid fa-expand" aria-hidden="true"></i>
                    <span>بازنشانی</span>
                </button>
                <button type="button" class="ps-qr-gallery__tool" data-ps-qr-download aria-label="دانلود QR">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                    <span>دانلود</span>
                </button>
                <button type="button" class="ps-qr-gallery__tool col-span-2 sm:col-span-1" data-ps-qr-copy aria-label="کپی لینک">
                    <i class="fa-solid fa-link" aria-hidden="true"></i>
                    <span data-ps-qr-copy-label>کپی لینک</span>
                </button>
            </div>
        </div>
    </dialog>
@elseif ($variant === 'compact')
    <button
        type="button"
        {{ $attributes->merge(['class' => 'group inline-flex shrink-0 items-center gap-2 rounded-lg border border-line bg-white px-2.5 py-1.5 text-start transition hover:border-brand/30 hover:bg-brand-soft/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30']) }}
        data-ps-qr-open="{{ $resolvedDialogId }}"
        aria-haspopup="dialog"
        aria-controls="{{ $resolvedDialogId }}"
        aria-label="مشاهده QR صفحه"
    >
        @if ($hasQrImage)
            <img
                src="{{ $imageUrl }}"
                alt=""
                width="32"
                height="32"
                class="size-8 rounded bg-white object-contain ring-1 ring-line transition group-hover:ring-brand/20"
                loading="lazy"
                decoding="async"
            >
        @else
            <x-shop.qr-placeholder
                :seed="$url"
                :size="32"
                class="rounded ring-1 ring-line transition group-hover:ring-brand/20"
            />
        @endif
        <span class="text-[11px] font-medium text-ink-muted transition group-hover:text-ink">QR صفحه</span>
    </button>
@elseif ($variant === 'hero')
    @php
        $resolvedCaptionUrl = $captionUrl ?: null;
        $resolvedCaptionLabel = $captionLabel
            ?: ($resolvedCaptionUrl ? preg_replace('#^https?://#', '', $resolvedCaptionUrl) : null);
    @endphp
    <div {{ $attributes->merge(['class' => 'flex w-full flex-col items-stretch gap-2']) }}>
        <button
            type="button"
            class="group flex aspect-square w-full items-center justify-center rounded-xl bg-white p-2 ring-1 ring-line transition hover:ring-brand/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30"
            data-ps-qr-open="{{ $resolvedDialogId }}"
            aria-haspopup="dialog"
            aria-controls="{{ $resolvedDialogId }}"
            aria-label="مشاهده QR صفحه"
        >
            @if ($hasQrImage)
                <img
                    src="{{ $imageUrl }}"
                    alt="QR صفحه {{ $title }}"
                    width="104"
                    height="104"
                    class="h-auto w-full rounded-md object-contain"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <x-shop.qr-placeholder
                    :seed="$url"
                    :size="104"
                    class="h-auto w-full rounded-md"
                />
            @endif
        </button>

        @if ($resolvedCaptionUrl)
            <a
                href="{{ $resolvedCaptionUrl }}"
                target="_blank"
                rel="noopener"
                class="flex min-w-0 items-center gap-1.5 rounded-lg px-0.5 py-0.5 transition hover:text-brand"
                title="{{ $resolvedCaptionUrl }}"
            >
                <i class="fa-solid fa-globe shrink-0 text-[10px] text-[#2563eb]" aria-hidden="true"></i>
                <span class="min-w-0 truncate text-[10px] font-medium text-ink-muted sm:text-[11px]" dir="ltr">{{ $resolvedCaptionLabel }}</span>
            </a>
        @endif
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-3']) }}>
        <button
            type="button"
            class="group flex w-full max-w-[9.5rem] flex-col items-center gap-2 rounded-xl border border-line bg-white p-3 transition hover:border-brand/30 hover:bg-brand-soft/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30"
            data-ps-qr-open="{{ $resolvedDialogId }}"
            aria-haspopup="dialog"
            aria-controls="{{ $resolvedDialogId }}"
        >
            @if ($hasQrImage)
                <img
                    src="{{ $imageUrl }}"
                    alt="QR صفحه {{ $title }}"
                    width="88"
                    height="88"
                    class="rounded-md bg-white object-contain ring-1 ring-line transition group-hover:ring-brand/20"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <x-shop.qr-placeholder
                    :seed="$url"
                    :size="88"
                    class="rounded-md ring-1 ring-line transition group-hover:ring-brand/20"
                />
            @endif
            <span class="text-xs font-medium text-ink-muted transition group-hover:text-ink">مشاهده QR صفحه</span>
        </button>
        <p class="text-center text-[11px] leading-5 text-ink-muted">برای اشتراک سریع پروفایل اسکن کنید</p>
    </div>
@endif

@once
    @push('head')
        <style>
            .ps-qr-gallery:not([open]) {
                display: none;
            }

            .ps-qr-gallery__shell {
                animation: ps-qr-gallery-enter 0.32s cubic-bezier(0.22, 1, 0.36, 1);
            }

            @keyframes ps-qr-gallery-enter {
                from {
                    opacity: 0;
                    transform: scale(0.92) translateY(0.75rem);
                }

                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            .ps-qr-gallery__tool {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.25rem;
                border-radius: 0.75rem;
                background: rgb(255 255 255 / 0.06);
                padding: 0.625rem 0.5rem;
                font-size: 0.6875rem;
                font-weight: 500;
                color: rgb(255 255 255 / 0.85);
                transition: background-color 150ms ease, color 150ms ease;
            }

            .ps-qr-gallery__tool:hover {
                background: rgb(255 255 255 / 0.12);
                color: #fff;
            }

            .ps-qr-gallery__tool i {
                font-size: 0.875rem;
            }

            @media (prefers-reduced-motion: reduce) {
                .ps-qr-gallery__shell {
                    animation: none;
                }

                .ps-qr-gallery__image-wrap {
                    transition: none;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                const minScale = 0.75;
                const maxScale = 2.5;
                const scaleStep = 0.25;

                const syncBodyScroll = function () {
                    const hasOpenGallery = document.querySelector('dialog[data-ps-qr-gallery][open]') !== null;
                    const hasOpenModal = document.querySelector('dialog[data-ps-modal][open]') !== null;
                    document.body.classList.toggle('ps-modal-open', hasOpenGallery || hasOpenModal);
                };

                const setGalleryScale = function (dialog, scale) {
                    const wrap = dialog.querySelector('[data-ps-qr-image-wrap]');

                    if (!wrap) {
                        return;
                    }

                    const nextScale = Math.min(maxScale, Math.max(minScale, scale));
                    wrap.dataset.scale = String(nextScale);
                    wrap.style.transform = 'scale(' + nextScale + ')';
                };

                const resetGalleryScale = function (dialog) {
                    setGalleryScale(dialog, 1);
                };

                const bindGallery = function (dialog) {
                    if (!(dialog instanceof HTMLDialogElement) || dialog.dataset.psQrBound === 'true') {
                        return;
                    }

                    dialog.dataset.psQrBound = 'true';

                    const nativeShowModal = dialog.showModal.bind(dialog);
                    const nativeClose = dialog.close.bind(dialog);

                    dialog.showModal = function () {
                        resetGalleryScale(dialog);
                        nativeShowModal();
                        syncBodyScroll();
                    };

                    dialog.close = function (returnValue) {
                        nativeClose(returnValue);
                        syncBodyScroll();
                    };

                    dialog.querySelector('[data-ps-qr-close]')?.addEventListener('click', function () {
                        dialog.close();
                    });

                    dialog.addEventListener('click', function (event) {
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });

                    dialog.addEventListener('close', syncBodyScroll);

                    dialog.querySelector('[data-ps-qr-zoom-in]')?.addEventListener('click', function () {
                        const wrap = dialog.querySelector('[data-ps-qr-image-wrap]');
                        const current = Number(wrap?.dataset.scale || 1);
                        setGalleryScale(dialog, current + scaleStep);
                    });

                    dialog.querySelector('[data-ps-qr-zoom-out]')?.addEventListener('click', function () {
                        const wrap = dialog.querySelector('[data-ps-qr-image-wrap]');
                        const current = Number(wrap?.dataset.scale || 1);
                        setGalleryScale(dialog, current - scaleStep);
                    });

                    dialog.querySelector('[data-ps-qr-reset]')?.addEventListener('click', function () {
                        resetGalleryScale(dialog);
                    });

                    dialog.querySelector('[data-ps-qr-copy]')?.addEventListener('click', async function () {
                        const url = dialog.dataset.psQrUrl || '';
                        const label = dialog.querySelector('[data-ps-qr-copy-label]');
                        const defaultLabel = label?.textContent?.trim() || 'کپی لینک';

                        try {
                            await navigator.clipboard.writeText(url);

                            if (label) {
                                label.textContent = 'کپی شد';
                                window.setTimeout(function () {
                                    label.textContent = defaultLabel;
                                }, 2000);
                            }
                        } catch {
                            window.prompt('لینک صفحه:', url);
                        }
                    });

                    dialog.querySelector('[data-ps-qr-download]')?.addEventListener('click', function () {
                        const title = dialog.dataset.psQrTitle || 'shop-qr';
                        const safeName = title.replace(/\s+/g, '-').toLowerCase();
                        const imageUrl = dialog.dataset.psQrImageUrl || '';
                        const image = dialog.querySelector('[data-ps-qr-image]');

                        if (imageUrl !== '') {
                            const link = document.createElement('a');
                            link.href = imageUrl;
                            link.download = safeName + '-qr.png';
                            link.click();

                            return;
                        }

                        if (!(image instanceof SVGElement)) {
                            return;
                        }

                        const clone = image.cloneNode(true);
                        clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');

                        const blob = new Blob([new XMLSerializer().serializeToString(clone)], {
                            type: 'image/svg+xml;charset=utf-8',
                        });
                        const objectUrl = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = objectUrl;
                        link.download = safeName + '-qr.svg';
                        link.click();
                        URL.revokeObjectURL(objectUrl);
                    });
                };

                document.querySelectorAll('[data-ps-qr-gallery]').forEach(bindGallery);

                document.querySelectorAll('[data-ps-qr-open]').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        const dialogId = trigger.getAttribute('data-ps-qr-open');
                        const dialog = dialogId ? document.getElementById(dialogId) : null;

                        if (dialog instanceof HTMLDialogElement) {
                            bindGallery(dialog);
                            dialog.showModal();
                        }
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    const openGallery = document.querySelector('dialog[data-ps-qr-gallery][open]');

                    if (openGallery instanceof HTMLDialogElement) {
                        openGallery.close();
                    }
                });
            })();
        </script>
    @endpush
@endonce
