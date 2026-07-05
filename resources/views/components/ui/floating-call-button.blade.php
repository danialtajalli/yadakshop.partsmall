@php
    $displayNumber = config('partsmall.floating_call.display', '021 77 222 4 99');
    $telNumber = config('partsmall.floating_call.tel', '02177222499');
@endphp

<div
    class="fixed bottom-5 end-5 z-50 flex flex-col items-end gap-3"
    data-floating-call
>
    <div
        id="floating-call-panel"
        class="hidden w-56 origin-bottom rounded-2xl border border-line bg-white p-4 shadow-card"
        data-floating-call-panel
        role="dialog"
        aria-label="تماس تلفنی"
        hidden
    >
        <p class="mb-3 text-xs font-medium text-ink-muted">پشتیبانی پارتس‌مال</p>
        <a
            href="tel:{{ $telNumber }}"
            class="ps-btn-primary w-full justify-center tabular-nums"
            dir="ltr"
        >
            <i class="fa-solid fa-phone" aria-hidden="true"></i>
            {{ $displayNumber }}
        </a>
    </div>

    <button
        type="button"
        class="flex size-14 items-center justify-center rounded-full bg-brand text-white shadow-lg ring-1 ring-black/5 transition hover:bg-brand-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 active:scale-95"
        data-floating-call-toggle
        aria-expanded="false"
        aria-controls="floating-call-panel"
        aria-label="نمایش شماره تماس"
    >
        <i class="fa-solid fa-phone text-lg" aria-hidden="true"></i>
    </button>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const root = document.querySelector('[data-floating-call]');

                if (!root) {
                    return;
                }

                const toggle = root.querySelector('[data-floating-call-toggle]');
                const panel = root.querySelector('[data-floating-call-panel]');

                if (!toggle || !panel) {
                    return;
                }

                const setOpen = function (open) {
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    panel.hidden = !open;
                    panel.classList.toggle('hidden', !open);
                };

                toggle.addEventListener('click', function (event) {
                    event.stopPropagation();
                    setOpen(panel.hidden);
                });

                document.addEventListener('click', function (event) {
                    if (!root.contains(event.target)) {
                        setOpen(false);
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        setOpen(false);
                    }
                });
            })();
        </script>
    @endpush
@endonce
