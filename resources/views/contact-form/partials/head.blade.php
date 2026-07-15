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
        transition: width 0.4s ease, opacity 0.22s ease;
    }

    #ps-navigation-progress.is-active {
        opacity: 1;
    }

    #ps-navigation-progress.is-complete {
        transition: width 0.22s ease-out, opacity 0.22s ease;
    }
</style>

<x-site.navigation-progress-boot />

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
    var valueKey = 'ps-nav-progress-value';
    var trickleTimer = null;
    var resumeStarted = false;

    function readStoredProgress() {
        var raw = sessionStorage.getItem(valueKey);
        var value = raw ? parseFloat(raw) : 0;
        return isFinite(value) ? value : 0;
    }

    function persistProgress(value) {
        sessionStorage.setItem(progressKey, 'active');
        sessionStorage.setItem(valueKey, String(Math.round(value * 10) / 10));
    }

    function clearProgressStorage() {
        sessionStorage.removeItem(progressKey);
        sessionStorage.removeItem(valueKey);
    }

    function isBackForwardNavigation() {
        var navigationEntry = performance.getEntriesByType('navigation')[0];

        return navigationEntry && navigationEntry.type === 'back_forward';
    }

    function shouldResumeNavigationProgress() {
        return sessionStorage.getItem(progressKey) === 'active' && !isBackForwardNavigation();
    }

    function resetContactNavigationProgress() {
        clearTrickle();
        removeBootStyle();
        resumeStarted = false;
        clearProgressStorage();

        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            return;
        }

        bar.classList.remove('is-active', 'is-complete');
        bar.style.transition = 'none';
        bar.style.width = '0%';
        bar.style.opacity = '';
        bar.setAttribute('aria-valuenow', '0');
        bar.getBoundingClientRect();
        bar.style.transition = '';
    }

    function removeBootStyle() {
        var boot = document.getElementById('ps-nav-progress-boot');
        if (boot) {
            boot.remove();
        }
    }

    function setBarWidth(value, animate) {
        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            return;
        }

        var clamped = Math.max(0, Math.min(100, value));

        if (animate === false) {
            bar.style.transition = 'none';
        }

        bar.style.width = clamped + '%';
        bar.setAttribute('aria-valuenow', String(Math.round(clamped)));

        if (animate === false) {
            bar.getBoundingClientRect();
            bar.style.transition = '';
        }

        if (sessionStorage.getItem(progressKey) === 'active' && clamped < 100) {
            persistProgress(clamped);
        }
    }

    function clearTrickle() {
        if (trickleTimer !== null) {
            window.clearInterval(trickleTimer);
            trickleTimer = null;
        }
    }

    function beginTrickle(from) {
        clearTrickle();
        var progress = from;

        trickleTimer = window.setInterval(function () {
            progress = Math.min(progress + Math.random() * 2.5 + 0.8, 94);
            setBarWidth(progress, true);

            if (progress >= 94) {
                clearTrickle();
            }
        }, 450);
    }

    window.startContactNavigationProgress = function () {
        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            return;
        }

        removeBootStyle();
        bar.classList.add('is-active');
        bar.classList.remove('is-complete');

        var stored = readStoredProgress();
        var start = stored > 0 ? stored : 4;

        setBarWidth(start, false);
        persistProgress(start);

        window.requestAnimationFrame(function () {
            if (start < 18) {
                setBarWidth(18, true);
                beginTrickle(18);
            } else {
                beginTrickle(start);
            }
        });

        if (window.parent !== window) {
            window.parent.postMessage({ type: 'didar-contact-form-submit' }, window.location.origin);
        }
    };

    function finishContactNavigationProgress() {
        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            clearProgressStorage();
            return;
        }

        clearTrickle();
        removeBootStyle();
        bar.classList.add('is-active', 'is-complete');
        setBarWidth(100, true);

        window.setTimeout(function () {
            bar.classList.remove('is-active');

            window.setTimeout(function () {
                bar.classList.remove('is-complete');
                setBarWidth(0, false);
                clearProgressStorage();
            }, 220);
        }, 260);
    }

    function resumeContactNavigationProgress() {
        if (!shouldResumeNavigationProgress() || resumeStarted) {
            return;
        }

        resumeStarted = true;

        var bar = document.getElementById('ps-navigation-progress');
        if (!bar) {
            return;
        }

        var stored = readStoredProgress();
        var start = stored > 0 ? stored : 28;

        removeBootStyle();
        bar.classList.add('is-active');
        bar.classList.remove('is-complete');
        setBarWidth(start, false);
        beginTrickle(start);

        window.setTimeout(finishContactNavigationProgress, 160);
    }

    window.addEventListener('pageshow', function (event) {
        if (event.persisted || isBackForwardNavigation()) {
            resetContactNavigationProgress();

            return;
        }

        if (shouldResumeNavigationProgress() && !resumeStarted) {
            resumeContactNavigationProgress();
        }
    });

    if (shouldResumeNavigationProgress()) {
        resumeContactNavigationProgress();
    } else if (sessionStorage.getItem(progressKey) === 'active') {
        resetContactNavigationProgress();
    }
})();
</script>
