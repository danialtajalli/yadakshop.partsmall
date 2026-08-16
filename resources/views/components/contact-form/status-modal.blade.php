@php
    $statusMessage = session('contact_status_message');
    $statusType = session('contact_status_type', 'error');
    $isSuccess = $statusType === 'success';
@endphp

@if ($statusMessage)
    <x-ui.modal
        id="contact-form-status-modal"
        data-contact-form-status-modal
        class="max-w-sm"
    >
        <div class="px-6 py-8 text-center">
            <div @class([
                'mx-auto mb-5 flex size-16 items-center justify-center rounded-full shadow-sm ring-8',
                'bg-emerald-100 text-emerald-600 ring-emerald-50' => $isSuccess,
                'bg-red-100 text-red-600 ring-red-50' => ! $isSuccess,
            ])>
                @if ($isSuccess)
                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z" />
                    </svg>
                @endif
            </div>

            <h3 class="text-lg font-bold text-ink">
                {{ $isSuccess ? 'درخواست شما ثبت شد' : 'ثبت درخواست ناموفق بود' }}
            </h3>
            <p class="mt-2 text-sm leading-7 text-ink-muted">
                {{ $statusMessage }}
            </p>

            <button
                type="button"
                class="ps-btn-primary mt-6 w-full"
                data-contact-form-status-close
            >
                متوجه شدم
            </button>
        </div>
    </x-ui.modal>

    @push('scripts')
        <script>
            (function () {
                const openContactStatusModal = function () {
                    const modal = document.querySelector('[data-contact-form-status-modal]');

                    if (!modal || typeof modal.showModal !== 'function') {
                        return;
                    }

                    modal.showModal();
                };

                document.querySelector('[data-contact-form-status-close]')?.addEventListener('click', function () {
                    document.querySelector('[data-contact-form-status-modal]')?.close();
                });

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', openContactStatusModal);
                } else {
                    openContactStatusModal();
                }
            })();
        </script>
    @endpush
@endif
