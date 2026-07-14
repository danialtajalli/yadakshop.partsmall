const STORAGE_KEY = 'ps-nav-progress';
const BAR_ID = 'ps-navigation-progress';

let trickleTimer = null;

function getBar() {
    return document.getElementById(BAR_ID);
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

function setProgress(value) {
    const bar = ensureBar();
    const clamped = Math.max(0, Math.min(100, value));

    bar.style.width = `${clamped}%`;
    bar.setAttribute('aria-valuenow', String(Math.round(clamped)));
}

function clearTrickle() {
    if (trickleTimer !== null) {
        window.clearInterval(trickleTimer);
        trickleTimer = null;
    }
}

function startNavigationProgress() {
    const bar = ensureBar();

    clearTrickle();
    bar.classList.add('is-active');
    bar.classList.remove('is-complete');
    setProgress(8);
    sessionStorage.setItem(STORAGE_KEY, 'active');

    window.requestAnimationFrame(() => {
        setProgress(35);
    });

    let progress = 35;

    trickleTimer = window.setInterval(() => {
        progress = Math.min(progress + Math.random() * 8, 88);
        setProgress(progress);

        if (progress >= 88) {
            clearTrickle();
        }
    }, 350);
}

function finishNavigationProgress() {
    const bar = getBar();

    if (!bar) {
        sessionStorage.removeItem(STORAGE_KEY);
        return;
    }

    clearTrickle();
    bar.classList.add('is-complete');
    setProgress(100);

    window.setTimeout(() => {
        bar.classList.remove('is-active', 'is-complete');
        setProgress(0);
        sessionStorage.removeItem(STORAGE_KEY);
    }, 280);
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

    window.addEventListener('pageshow', () => {
        if (sessionStorage.getItem(STORAGE_KEY) === 'active') {
            finishNavigationProgress();
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

    if (sessionStorage.getItem(STORAGE_KEY) === 'active') {
        const bar = ensureBar();
        bar.classList.add('is-active');
        setProgress(72);
        finishNavigationProgress();
    }
}

if (typeof window !== 'undefined') {
    bindNavigationProgress();
}

export { finishNavigationProgress, startNavigationProgress };
