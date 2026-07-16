<div
    id="ps-toast-host"
    class="pointer-events-none fixed inset-x-0 bottom-6 z-[80] flex flex-col items-center gap-2 px-4"
    aria-live="polite"
    aria-atomic="true"
></div>

@once
    @push('head')
        <style>
            .ps-toast {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                border-radius: 9999px;
                border: 1px solid rgb(226 232 240);
                background: #fff;
                padding: 0.625rem 1rem;
                font-size: 0.8125rem;
                font-weight: 500;
                color: #0f172a;
                box-shadow: 0 10px 25px -12px rgb(15 23 42 / 0.25);
                opacity: 0;
                transform: translateY(0.5rem) scale(0.96);
                transition: opacity 0.24s ease, transform 0.24s ease;
            }

            .ps-toast.is-visible {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            .ps-toast__icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 9999px;
                background: rgb(242 124 34 / 0.12);
                color: #f27c22;
                width: 1.125rem;
                height: 1.125rem;
                font-size: 0.625rem;
            }

            @media (prefers-reduced-motion: reduce) {
                .ps-toast {
                    transition: none;
                    transform: none;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                const copyWithExecCommand = function (text) {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'fixed';
                    textarea.style.top = '0';
                    textarea.style.left = '0';
                    textarea.style.opacity = '0';
                    textarea.style.pointerEvents = 'none';
                    document.body.appendChild(textarea);
                    textarea.focus();
                    textarea.select();
                    textarea.setSelectionRange(0, text.length);

                    let copied = false;

                    try {
                        copied = document.execCommand('copy');
                    } catch {
                        copied = false;
                    }

                    document.body.removeChild(textarea);

                    return copied;
                };

                window.psShowToast = function (message) {
                    const host = document.getElementById('ps-toast-host');

                    if (!host || !message) {
                        return;
                    }

                    const toast = document.createElement('div');
                    toast.className = 'ps-toast';

                    const icon = document.createElement('span');
                    icon.className = 'ps-toast__icon';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.innerHTML = '<i class="fa-solid fa-check"></i>';

                    const label = document.createElement('span');
                    label.textContent = message;

                    toast.append(icon, label);
                    host.appendChild(toast);

                    window.requestAnimationFrame(function () {
                        toast.classList.add('is-visible');
                    });

                    window.setTimeout(function () {
                        toast.classList.remove('is-visible');
                        window.setTimeout(function () {
                            toast.remove();
                        }, 280);
                    }, 2800);
                };

                window.psCopyText = function (text) {
                    if (!text) {
                        return Promise.reject(new Error('empty'));
                    }

                    if (copyWithExecCommand(text)) {
                        return Promise.resolve();
                    }

                    if (navigator.clipboard?.writeText) {
                        return navigator.clipboard.writeText(text);
                    }

                    return Promise.reject(new Error('copy_failed'));
                };

                window.psCopyAndToast = function (text, successMessage, errorMessage) {
                    return window.psCopyText(text)
                        .then(function () {
                            window.psShowToast(successMessage || 'کپی شد');
                        })
                        .catch(function () {
                            window.psShowToast(errorMessage || 'کپی نشد');
                        });
                };
            })();
        </script>
    @endpush
@endonce
