@props([
    'id' => null,
])

<dialog
    @if ($id) id="{{ $id }}" @endif
    data-ps-modal
    {{ $attributes->except('class') }}
    class="fixed inset-0 z-50 m-0 max-h-none w-full max-w-none border-0 bg-transparent p-4 shadow-none backdrop:bg-ink/40 open:flex open:items-center open:justify-center open:animate-none"
>
    <div
        data-ps-modal-panel
        {{ $attributes->class([
            'ps-modal-panel relative w-full max-w-md max-h-[calc(100dvh-2rem)] overflow-visible rounded-2xl border border-line bg-white p-0 shadow-2xl',
        ]) }}
    >
        {{ $slot }}
    </div>
</dialog>

@once
    @push('scripts')
        <script>
            (function () {
                const syncBodyScroll = function () {
                    const hasOpenModal = document.querySelector('dialog[data-ps-modal][open]') !== null;
                    document.body.classList.toggle('ps-modal-open', hasOpenModal);
                };

                const bindModal = function (dialog) {
                    if (!(dialog instanceof HTMLDialogElement) || dialog.dataset.psModalBound === 'true') {
                        return;
                    }

                    dialog.dataset.psModalBound = 'true';

                    const nativeShowModal = dialog.showModal.bind(dialog);
                    const nativeClose = dialog.close.bind(dialog);

                    dialog.showModal = function () {
                        nativeShowModal();
                        syncBodyScroll();
                    };

                    dialog.close = function (returnValue) {
                        nativeClose(returnValue);
                        syncBodyScroll();
                    };

                    dialog.addEventListener('click', function (event) {
                        const panel = dialog.querySelector('[data-ps-modal-panel]');

                        if (panel && ! panel.contains(event.target)) {
                            dialog.close();
                        }
                    });

                    dialog.addEventListener('close', syncBodyScroll);
                };

                document.querySelectorAll('dialog[data-ps-modal]').forEach(bindModal);
            })();
        </script>
    @endpush
@endonce
