const DURATION_MS = 1600;
const STAGGER_MS = 120;
const LIVE_JITTER = 40;
const LIVE_MIN_INTERVAL_MS = 1000;
const LIVE_MAX_INTERVAL_MS = 45000;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function easeOutCubic(t) {
    return 1 - ((1 - t) ** 3);
}

function formatCount(value) {
    return new Intl.NumberFormat('en-US').format(Math.round(value));
}

function randomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function approximateTarget(base) {
    const floor = Math.max(0, base - LIVE_JITTER);

    return randomInt(floor, base + LIVE_JITTER);
}

function scheduleLiveIncrements(el, startValue) {
    let current = Math.round(startValue);

    const bump = () => {
        current += randomInt(1, 3);
        el.textContent = formatCount(current);
        el.dataset.current = String(current);
        window.setTimeout(bump, randomInt(LIVE_MIN_INTERVAL_MS, LIVE_MAX_INTERVAL_MS));
    };

    window.setTimeout(bump, randomInt(LIVE_MIN_INTERVAL_MS, LIVE_MAX_INTERVAL_MS));
}

function animateValue(el, target, duration, delay, onComplete) {
    const start = () => {
        const startedAt = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - startedAt) / duration, 1);
            el.textContent = formatCount(target * easeOutCubic(progress));

            if (progress < 1) {
                window.requestAnimationFrame(tick);
            } else {
                el.textContent = formatCount(target);
                el.closest('[data-stats-item]')?.classList.add('is-complete');
                onComplete?.(target);
            }
        };

        window.requestAnimationFrame(tick);
    };

    if (delay > 0) {
        window.setTimeout(start, delay);
    } else {
        start();
    }
}

function revealStrip(strip) {
    strip.classList.add('is-visible');

    const items = strip.querySelectorAll('[data-stats-item]');
    const reduced = prefersReducedMotion();

    items.forEach((item, index) => {
        const valueEl = item.querySelector('[data-stats-value]');
        const baseTarget = Number(valueEl?.dataset.target || 0);
        const isLive = valueEl?.hasAttribute('data-stats-live');

        if (! valueEl || ! Number.isFinite(baseTarget)) {
            return;
        }

        const target = isLive ? approximateTarget(baseTarget) : baseTarget;

        if (reduced) {
            valueEl.textContent = formatCount(target);
            item.classList.add('is-visible', 'is-complete');

            if (isLive) {
                scheduleLiveIncrements(valueEl, target);
            }

            return;
        }

        valueEl.textContent = '0';
        item.style.setProperty('--stats-delay', `${index * STAGGER_MS}ms`);
        item.classList.add('is-visible');
        animateValue(
            valueEl,
            target,
            DURATION_MS,
            index * STAGGER_MS,
            isLive ? (finalValue) => scheduleLiveIncrements(valueEl, finalValue) : undefined,
        );
    });
}

function initStatsStrips() {
    const strips = document.querySelectorAll('[data-stats-strip]');

    if (strips.length === 0) {
        return;
    }

    if (! ('IntersectionObserver' in window)) {
        strips.forEach(revealStrip);

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            revealStrip(entry.target);
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.35,
        rootMargin: '0px 0px -8% 0px',
    });

    strips.forEach((strip) => observer.observe(strip));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStatsStrips);
} else {
    initStatsStrips();
}
