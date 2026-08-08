@props([
    'url',
    'title' => '',
    'dialogId' => 'shop-share-sheet',
])

@php
    $encodedUrl = rawurlencode($url);
    $encodedText = rawurlencode($title !== '' ? $title : $url);
    $telegramShareUrl = 'https://t.me/share/url?url='.$encodedUrl.'&text='.$encodedText;
    $baleShareUrl = 'https://ble.ir/share/url?url='.$encodedUrl;
@endphp

<x-ui.modal :id="$dialogId" class="z-[70]" data-ps-share-sheet data-ps-share-url="{{ $url }}" data-ps-share-title="{{ $title }}">
    <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3 sm:px-5">
        <div class="min-w-0">
            <p class="text-sm font-semibold text-ink">اشتراک‌گذاری</p>
            @if ($title)
                <p class="mt-0.5 truncate text-xs text-ink-muted">{{ $title }}</p>
            @endif
        </div>
        <button
            type="button"
            class="flex size-9 shrink-0 items-center justify-center rounded-lg text-ink-muted transition hover:bg-surface hover:text-ink"
            data-ps-share-close
            aria-label="بستن"
        >
            <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
        </button>
    </div>

    <div class="px-4 py-4 sm:px-5 sm:py-5">
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
            <a
                href="{{ $telegramShareUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="ps-share-sheet__channel"
            >
                <span class="ps-share-sheet__icon bg-[#229ED9]">
                    <i class="fa-brands fa-telegram text-lg text-white" aria-hidden="true"></i>
                </span>
                <span>تلگرام</span>
            </a>

            <a
                href = "#"
                type="button"
                class="ps-share-sheet__channel"
                data-ps-share-instagram
            >
                <span class="ps-share-sheet__icon bg-gradient-to-br from-[#f58529] via-[#dd2a7b] to-[#8134af]">
                    <i class="fa-brands fa-instagram text-lg text-white" aria-hidden="true"></i>
                </span>
                <span>اینستاگرام</span>
            </a>

            <a
                href="{{ $baleShareUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="ps-share-sheet__channel"
            >
                <span class="ps-share-sheet__icon bg-transparent">
                    <img
                        src="{{ asset('img/brands/bale.svg') }}"
                        alt=""
                        width="28"
                        height="28"
                        class="size-7"
                        loading="lazy"
                        decoding="async"
                    >
                </span>
                <span>بله</span>
            </a>

            <a
                href = "#"
                type="button"
                class="ps-share-sheet__channel"
                data-ps-share-rubika
            >
                <span class="ps-share-sheet__icon bg-transparent">
                    <img
                        src="{{ asset('img/brands/rubika.png') }}"
                        alt=""
                        width="28"
                        height="28"
                        class="size-7 rounded-md object-contain"
                        loading="lazy"
                        decoding="async"
                    >
                </span>
                <span>روبیکا</span>
            </a>
        </div>

        <button
            type="button"
            class="ps-share-sheet__copy mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-line bg-surface px-4 py-3 text-sm font-medium text-ink transition hover:border-brand/30 hover:bg-brand-soft/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30"
            data-ps-share-copy
        >
            <i class="fa-solid fa-link text-sm text-brand" data-ps-share-copy-icon aria-hidden="true"></i>
            <span data-ps-share-copy-label>کپی لینک</span>
        </button>

        <p class="mt-3 truncate text-center text-[11px] text-ink-muted" dir="ltr">{{ $url }}</p>
    </div>
</x-ui.modal>

@once
    @push('head')
        <style>
            .ps-share-sheet__channel {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                border-radius: 0.875rem;
                border: 1px solid var(--color-line, #e5e7eb);
                background: #fff;
                padding: 0.875rem 0.5rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--color-ink, #0f172a);
                transition: border-color 150ms ease, background-color 150ms ease, transform 120ms ease;
            }

            .ps-share-sheet__channel:hover {
                border-color: color-mix(in srgb, var(--color-brand, #f27c22) 35%, transparent);
                background: color-mix(in srgb, var(--color-brand, #f27c22) 8%, white);
            }

            .ps-share-sheet__channel:active {
                transform: scale(0.98);
            }

            .ps-share-sheet__icon {
                display: inline-flex;
                width: 2.5rem;
                height: 2.5rem;
                align-items: center;
                justify-content: center;
                border-radius: 9999px;
            }

            dialog[data-ps-share-sheet] {
                z-index: 70;
            }

            .ps-share-sheet__copy.is-copied {
                border-color: rgb(16 185 129 / 0.45);
                background: rgb(236 253 245);
                color: rgb(6 95 70);
                animation: ps-share-copy-pulse 0.45s ease;
            }

            .ps-share-sheet__copy.is-copied [data-ps-share-copy-icon] {
                color: rgb(16 185 129);
            }

            .ps-share-sheet__copy [data-ps-share-copy-label] {
                display: inline-block;
            }

            .ps-share-sheet__copy.is-copied [data-ps-share-copy-label] {
                animation: ps-share-copy-label 0.35s ease;
            }

            @keyframes ps-share-copy-pulse {
                0% {
                    transform: scale(1);
                }

                40% {
                    transform: scale(1.02);
                }

                100% {
                    transform: scale(1);
                }
            }

            @keyframes ps-share-copy-label {
                0% {
                    opacity: 0;
                    transform: translateY(0.25rem);
                }

                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .ps-share-sheet__copy.is-copied,
                .ps-share-sheet__copy.is-copied [data-ps-share-copy-label] {
                    animation: none;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                const bindShareSheet = function (dialog) {
                    if (!(dialog instanceof HTMLDialogElement) || dialog.dataset.psShareBound === 'true') {
                        return;
                    }

                    dialog.dataset.psShareBound = 'true';

                    dialog.querySelector('[data-ps-share-close]')?.addEventListener('click', function () {
                        dialog.close();
                    });

                    dialog.querySelector('[data-ps-share-copy]')?.addEventListener('click', function () {
                        const url = dialog.dataset.psShareUrl || window.location.href;
                        const button = dialog.querySelector('[data-ps-share-copy]');
                        const label = dialog.querySelector('[data-ps-share-copy-label]');
                        const icon = dialog.querySelector('[data-ps-share-copy-icon]');
                        const defaultLabel = 'کپی لینک';

                        window.psCopyAndToast?.(url, 'لینک صفحه کپی شد', 'کپی لینک انجام نشد');

                        if (!button || !label) {
                            return;
                        }

                        button.classList.remove('is-copied');
                        void button.offsetWidth;
                        button.classList.add('is-copied');
                        label.textContent = 'لینک کپی شد';

                        if (icon) {
                            icon.classList.remove('fa-link');
                            icon.classList.add('fa-check');
                        }

                        window.clearTimeout(Number(button.dataset.copyResetTimer || 0));
                        button.dataset.copyResetTimer = String(window.setTimeout(function () {
                            button.classList.remove('is-copied');
                            label.textContent = defaultLabel;

                            if (icon) {
                                icon.classList.remove('fa-check');
                                icon.classList.add('fa-link');
                            }
                        }, 2200));
                    });

                    dialog.querySelector('[data-ps-share-instagram]')?.addEventListener('click', function () {
                        const url = dialog.dataset.psShareUrl || window.location.href;

                        window.psCopyAndToast?.(
                            url,
                            'لینک کپی شد؛ در اینستاگرام بچسبانید',
                            'کپی لینک انجام نشد',
                        );

                        window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
                    });

                    dialog.querySelector('[data-ps-share-rubika]')?.addEventListener('click', function () {
                        const url = dialog.dataset.psShareUrl || window.location.href;

                        window.psCopyAndToast?.(
                            url,
                            'لینک کپی شد؛ در روبیکا بچسبانید',
                            'کپی لینک انجام نشد',
                        );

                        window.open('https://m.rubika.ir/', '_blank', 'noopener,noreferrer');
                    });
                };

                document.querySelectorAll('dialog[data-ps-share-sheet]').forEach(bindShareSheet);

                document.querySelectorAll('[data-shop-share]').forEach(function (trigger) {
                    trigger.addEventListener('click', function () {
                        const dialogId = trigger.getAttribute('data-shop-share-target') || 'shop-share-sheet';
                        const dialog = document.getElementById(dialogId);

                        if (dialog instanceof HTMLDialogElement) {
                            bindShareSheet(dialog);
                            dialog.showModal();
                        }
                    });
                });
            })();
        </script>
    @endpush
@endonce
