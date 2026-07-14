<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'فرم تماس' }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Vazirmatn', 'Segoe UI', 'Tahoma', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                },
                colors: {
                    brand: {
                        DEFAULT: '#3f4857',
                        dark: '#222c3d',
                        soft: '#f27c22',
                    },
                    ink: {
                        DEFAULT: '#0f172a',
                        muted: '#64748b',
                    },
                    surface: '#f1f5f9',
                    line: '#e2e8f0',
                },
            },
        },
    };
</script>

<style>
    html, body {
        margin: 0;
        padding: 0;
        background: transparent;
        font-family: Vazirmatn, 'Segoe UI', Tahoma, ui-sans-serif, system-ui, sans-serif;
        color: #0f172a;
        direction: rtl;
    }

    .didar-contact-form__input--error {
        border-color: #f87171 !important;
        background-color: #fffafa !important;
    }

    .didar-contact-form__input--error:focus {
        border-color: #ef4444 !important;
        --tw-ring-color: rgb(239 68 68 / 0.25) !important;
    }

    button,
    a[href] {
        -webkit-tap-highlight-color: rgb(63 72 87 / 0.18);
        touch-action: manipulation;
    }

    .ps-contact-submit {
        user-select: none;
        transition: transform 120ms ease, background-color 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
    }

    .ps-contact-submit:active,
    .ps-contact-submit.ps-touch-pressed {
        transform: scale(0.96);
        background-color: #222c3d;
        box-shadow: inset 0 2px 6px rgb(0 0 0 / 0.22);
    }

    .ps-contact-submit:disabled,
    .ps-contact-submit[aria-busy='true'] {
        cursor: wait;
        opacity: 0.85;
        transform: none;
        box-shadow: none;
    }

    @keyframes ps-contact-spin {
        to { transform: rotate(360deg); }
    }

    .ps-contact-submit__spinner {
        animation: ps-contact-spin 0.75s linear infinite;
    }

    #ps-navigation-progress {
        position: fixed;
        inset-inline-start: 0;
        top: 0;
        z-index: 9999;
        height: 3px;
        width: 0;
        pointer-events: none;
        opacity: 0;
        background: linear-gradient(90deg, #3f4857 0%, #f27c22 100%);
        box-shadow: 0 0 10px rgb(63 72 87 / 0.35);
        transition: width 0.28s ease, opacity 0.2s ease;
    }

    #ps-navigation-progress.is-active {
        opacity: 1;
    }
</style>

<script>
(function () {
    function clearPressed() {
        document.querySelectorAll('.ps-touch-pressed').forEach(function (el) {
            el.classList.remove('ps-touch-pressed');
        });
    }

    document.addEventListener('touchstart', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        var pressable = target.closest('button, a[href]');
        if (!pressable || pressable.disabled) {
            return;
        }

        clearPressed();
        pressable.classList.add('ps-touch-pressed');
    }, { passive: true });

    document.addEventListener('touchend', clearPressed, { passive: true });
    document.addEventListener('touchcancel', clearPressed, { passive: true });

    var progressKey = 'ps-nav-progress';

    function setBarWidth(value) {
        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            return;
        }

        bar.style.width = value + '%';
        bar.setAttribute('aria-valuenow', String(Math.round(value)));
    }

    window.startContactNavigationProgress = function () {
        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            return;
        }

        bar.classList.add('is-active');
        setBarWidth(12);
        sessionStorage.setItem(progressKey, 'active');

        window.requestAnimationFrame(function () {
            setBarWidth(38);
        });

        window.setTimeout(function () {
            setBarWidth(72);
        }, 180);

        if (window.parent !== window) {
            window.parent.postMessage({ type: 'didar-contact-form-submit' }, window.location.origin);
        }
    };

    function finishContactNavigationProgress() {
        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            sessionStorage.removeItem(progressKey);
            return;
        }

        setBarWidth(100);

        window.setTimeout(function () {
            bar.classList.remove('is-active');
            setBarWidth(0);
            sessionStorage.removeItem(progressKey);
        }, 280);
    }

    window.addEventListener('pageshow', function () {
        if (sessionStorage.getItem(progressKey) === 'active') {
            setBarWidth(72);
            finishContactNavigationProgress();
        }
    });

    if (sessionStorage.getItem(progressKey) === 'active') {
        var bar = document.getElementById('ps-navigation-progress');
        if (bar) {
            bar.classList.add('is-active');
            setBarWidth(72);
        }
        finishContactNavigationProgress();
    }
})();
</script>
