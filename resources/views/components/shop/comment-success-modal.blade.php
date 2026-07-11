@if (session('comment_submitted'))
    <x-ui.modal
        id="shop-comment-success-modal"
        data-shop-comment-success-modal
        class="max-w-sm"
    >
        <div class="px-6 py-8 text-center">
            <div class="mx-auto mb-5 flex size-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 shadow-sm ring-8 ring-emerald-50">
                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h3 class="text-lg font-bold text-ink">نظر شما ثبت شد</h3>
            <p class="mt-2 text-sm leading-7 text-ink-muted">
                نظر شما با موفقیت ثبت شد و پس از تایید در این صفحه نمایش داده می‌شود.
            </p>

            <button
                type="button"
                class="ps-btn-primary mt-6 w-full"
                data-shop-comment-success-close
            >
                متوجه شدم
            </button>
        </div>
    </x-ui.modal>

    @push('scripts')
        <script>
            (function () {
                const modal = document.querySelector('[data-shop-comment-success-modal]');

                if (!modal || typeof modal.showModal !== 'function') {
                    return;
                }

                modal.showModal();

                modal.querySelector('[data-shop-comment-success-close]')?.addEventListener('click', function () {
                    modal.close();
                });
            })();
        </script>
    @endpush
@endif
