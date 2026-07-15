const STORAGE_KEY = 'ps-nav-progress';
const VALUE_KEY = 'ps-nav-progress-value';
const BAR_ID = 'ps-navigation-progress';

let trickleTimer = null;
let completeTimer = null;
let resumeStarted = false;

function getBar() {
    return document.getElementById(BAR_ID);
}

function readStoredProgress() {
    const raw = sessionStorage.getItem(VALUE_KEY);
    const value = raw ? Number.parseFloat(raw) : 0;

    return Number.isFinite(value) ? value : 0;
}

function persistProgress(value) {
    sessionStorage.setItem(STORAGE_KEY, 'active');
    sessionStorage.setItem(VALUE_KEY, String(Math.round(value * 10) / 10));
}

function clearProgressStorage() {
    sessionStorage.removeItem(STORAGE_KEY);
    sessionStorage.removeItem(VALUE_KEY);
}

function isBackForwardNavigation() {
    const navigationEntry = performance.getEntriesByType('navigation')[0];

    return navigationEntry?.type === 'back_forward';
}

function shouldResumeNavigationProgress() {
    return sessionStorage.getItem(STORAGE_KEY) === 'active' && !isBackForwardNavigation();
}

function resetNavigationProgress() {
    clearTrickle();
    clearCompleteTimer();
    removeBootStyle();
    resumeStarted = false;
    clearProgressStorage();

    const bar = getBar();

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
    document.getElementById('ps-nav-progress-boot')?.remove();
}

function setProgress(value, { animate = true } = {}) {
    const bar = getBar();

    if (!bar) {
        return;
    }

    const clamped = Math.max(0, Math.min(100, value));

    if (!animate) {
        bar.style.transition = 'none';
    }

    bar.style.width = `${clamped}%`;
    bar.setAttribute('aria-valuenow', String(Math.round(clamped)));

    if (!animate) {
        bar.getBoundingClientRect();
        bar.style.transition = '';
    }

    if (sessionStorage.getItem(STORAGE_KEY) === 'active' && clamped < 100) {
        persistProgress(clamped);
    }
}

function clearTrickle() {
    if (trickleTimer !== null) {
        window.clearInterval(trickleTimer);
        trickleTimer = null;
    }
}

function clearCompleteTimer() {
    if (completeTimer !== null) {
        window.clearTimeout(completeTimer);
        completeTimer = null;
    }
}

function beginTrickle(from) {
    clearTrickle();

    let progress = from;

    trickleTimer = window.setInterval(() => {
        progress = Math.min(progress + Math.random() * 2.5 + 0.8, 94);
        setProgress(progress);

        if (progress >= 94) {
            clearTrickle();
        }
    }, 450);
}

function startNavigationProgress() {
    const bar = getBar() ?? ensureBar();

    clearCompleteTimer();
    clearTrickle();
    removeBootStyle();

    bar.classList.add('is-active');
    bar.classList.remove('is-complete');

    const stored = readStoredProgress();
    const isContinuing = sessionStorage.getItem(STORAGE_KEY) === 'active' && stored > 0;

    if (isContinuing) {
        setProgress(stored, { animate: false });
        beginTrickle(stored);

        return;
    }

    setProgress(4, { animate: false });
    persistProgress(4);

    window.requestAnimationFrame(() => {
        setProgress(18);
        beginTrickle(18);
    });
}

function ensureBar() {
    let bar = getBar();

    if (bar) {
        return bar;
    }

    bar = document.createElement('div');
    bar.id = BAR_ID;
    bar.setAttribute('role', 'progressbar');
    bar.setAttribute('aria-hidden', 'true');
    bar.setAttribute('aria-valuemin', '0');
    bar.setAttribute('aria-valuemax', '100');
    bar.setAttribute('aria-valuenow', '0');
    document.body.prepend(bar);

    return bar;
}

function finishNavigationProgress() {
    const bar = getBar();

    if (!bar) {
        clearProgressStorage();
        return;
    }

    clearTrickle();
    clearCompleteTimer();
    removeBootStyle();

    bar.classList.add('is-active', 'is-complete');
    setProgress(100);

    completeTimer = window.setTimeout(() => {
        bar.classList.remove('is-active');

        window.setTimeout(() => {
            bar.classList.remove('is-complete');
            setProgress(0, { animate: false });
            clearProgressStorage();
        }, 220);
    }, 260);
}

function resumeNavigationProgress() {
    if (!shouldResumeNavigationProgress() || resumeStarted) {
        return;
    }

    resumeStarted = true;

    const bar = getBar();

    if (!bar) {
        return;
    }

    const stored = readStoredProgress();
    const start = stored > 0 ? stored : 28;

    removeBootStyle();
    bar.classList.add('is-active');
    bar.classList.remove('is-complete');
    setProgress(start, { animate: false });
    beginTrickle(start);

    const complete = () => {
        if (sessionStorage.getItem(STORAGE_KEY) !== 'active') {
            return;
        }

        finishNavigationProgress();
    };

    if (document.readyState === 'complete') {
        window.setTimeout(complete, 160);
    } else {
        window.addEventListener('load', () => window.setTimeout(complete, 160), { once: true });
        completeTimer = window.setTimeout(complete, 12000);
    }
}

function shouldTrackLink(link) {
    if (!(link instanceof HTMLAnchorElement)) {
        return false;
    }

    if (link.dataset.noProgress !== undefined) {
        return false;
    }

    if (link.target === '_blank' || link.hasAttribute('download')) {
        return false;
    }

    const href = link.getAttribute('href');

    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) {
        return false;
    }

    let url;

    try {
        url = new URL(link.href, window.location.href);
    } catch {
        return false;
    }

    if (url.origin !== window.location.origin) {
        return false;
    }

    if (
        url.pathname === window.location.pathname
        && url.search === window.location.search
        && url.hash !== ''
    ) {
        return false;
    }

    return true;
}

function shouldTrackForm(form) {
    if (!(form instanceof HTMLFormElement)) {
        return false;
    }

    if (form.dataset.noProgress !== undefined) {
        return false;
    }

    if (form.target === '_blank') {
        return false;
    }

    if (form.hasAttribute('x-data')) {
        return false;
    }

    return true;
}

function bindNavigationProgress() {
    document.addEventListener(
        'click',
        (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const link = event.target instanceof Element
                ? event.target.closest('a[href]')
                : null;

            if (link && shouldTrackLink(link)) {
                startNavigationProgress();
            }
        },
        true,
    );

    document.addEventListener(
        'submit',
        (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (shouldTrackForm(event.target)) {
                startNavigationProgress();
            }
        },
        true,
    );

    window.addEventListener('pageshow', (event) => {
        if (event.persisted || isBackForwardNavigation()) {
            resetNavigationProgress();

            return;
        }

        if (shouldResumeNavigationProgress() && !resumeStarted) {
            resumeNavigationProgress();
        }
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) {
            return;
        }

        if (event.data?.type === 'didar-contact-form-submit') {
            startNavigationProgress();
        }
    });

    if (shouldResumeNavigationProgress()) {
        resumeNavigationProgress();
    } else if (sessionStorage.getItem(STORAGE_KEY) === 'active') {
        resetNavigationProgress();
    }
}

if (typeof window !== 'undefined') {
    bindNavigationProgress();
}

export { finishNavigationProgress, resetNavigationProgress, startNavigationProgress };
