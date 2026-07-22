const GRID = 28;
const PAD = 14;
const CYCLE_MS = 7000;
const CORNER_RADIUS = 10;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function snap(value) {
    return Math.round(value / GRID) * GRID;
}

function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
}

function distance(a, b) {
    return Math.hypot(b.x - a.x, b.y - a.y);
}

function pointAlong(from, to, dist) {
    const len = distance(from, to) || 1;
    const t = Math.min(dist, len) / len;

    return {
        x: from.x + (to.x - from.x) * t,
        y: from.y + (to.y - from.y) * t,
    };
}

/** Orthogonal polyline with rounded corners so the beam glides through turns. */
function roundedPolyline(points, radius = CORNER_RADIUS) {
    if (points.length < 2) {
        return '';
    }

    let d = `M ${points[0].x} ${points[0].y}`;

    for (let i = 1; i < points.length - 1; i += 1) {
        const prev = points[i - 1];
        const curr = points[i];
        const next = points[i + 1];
        const incoming = distance(prev, curr);
        const outgoing = distance(curr, next);
        const corner = Math.min(radius, incoming / 2, outgoing / 2);

        if (corner < 1) {
            d += ` L ${curr.x} ${curr.y}`;
            continue;
        }

        const before = pointAlong(curr, prev, corner);
        const after = pointAlong(curr, next, corner);

        d += ` L ${before.x} ${before.y}`;
        d += ` Q ${curr.x} ${curr.y} ${after.x} ${after.y}`;
    }

    const last = points[points.length - 1];

    d += ` L ${last.x} ${last.y}`;

    return d;
}

/**
 * Mobile: beam enters from the right edge and splits into a full-section frame.
 * Desktop keeps the text → shops circuit.
 */
function buildMobileSectionPaths(panel) {
    const width = panel.clientWidth;
    const height = panel.clientHeight;

    if (width < GRID * 3 || height < GRID * 3) {
        return null;
    }

    const inset = 5;
    const x = inset;
    const y = inset;
    const w = width - inset * 2;
    const h = height - inset * 2;
    const r = Math.min(14, w / 2, h / 2);
    const midY = y + h / 2;
    const entryX = x + w;

    const travel = roundedPolyline([
        { x: width - 1, y: midY },
        { x: entryX, y: midY },
    ]);

    const borderUp = [
        `M ${entryX} ${midY}`,
        `L ${entryX} ${y + r}`,
        `Q ${entryX} ${y} ${entryX - r} ${y}`,
        `L ${x + r} ${y}`,
        `Q ${x} ${y} ${x} ${y + r}`,
        `L ${x} ${midY}`,
    ].join(' ');

    const borderDown = [
        `M ${entryX} ${midY}`,
        `L ${entryX} ${y + h - r}`,
        `Q ${entryX} ${y + h} ${entryX - r} ${y + h}`,
        `L ${x + r} ${y + h}`,
        `Q ${x} ${y + h} ${x} ${y + h - r}`,
        `L ${x} ${midY}`,
    ].join(' ');

    return {
        travel,
        borderUp,
        borderDown,
    };
}

/**
 * Travel path around the text, plus two border halves from the right-middle
 * (one upward, one downward) that meet on the left side.
 */
function buildCircuitPaths(panel, trio, copy) {
    if (window.matchMedia('(max-width: 639px)').matches) {
        return buildMobileSectionPaths(panel);
    }

    const panelRect = panel.getBoundingClientRect();
    const trioRect = trio.getBoundingClientRect();
    const copyRect = copy.getBoundingClientRect();
    const width = panel.clientWidth;
    const height = panel.clientHeight;

    if (width < GRID * 4 || height < GRID * 3) {
        return null;
    }

    const copyLeft = copyRect.left - panelRect.left;
    const copyTop = copyRect.top - panelRect.top;
    const copyRight = copyLeft + copyRect.width;
    const copyBottom = copyTop + copyRect.height;
    const copyMidY = copyTop + copyRect.height / 2;

    const trioX = trioRect.left - panelRect.left;
    const trioY = trioRect.top - panelRect.top;
    const boxX = trioX - PAD;
    const boxY = trioY - PAD;
    const boxW = trioRect.width + PAD * 2;
    const boxH = trioRect.height + PAD * 2;
    const radius = 18;

    const maxX = snap(width - GRID);
    const maxY = snap(height - GRID);
    const margin = GRID;

    const aroundLeft = clamp(snap(copyLeft - margin), GRID, maxX);
    const aroundTop = clamp(snap(copyTop - margin), GRID, maxY);
    const aroundBottom = clamp(snap(copyBottom + margin), GRID, maxY);
    const aroundRight = clamp(snap(Math.min(width - GRID, copyRight + margin / 2)), GRID, maxX);
    const startY = clamp(snap(copyMidY), GRID * 2, maxY - GRID);

    const entryX = boxX + boxW;
    const entryY = boxY + boxH / 2;

    const trioIsLeftOfCopy = (boxX + boxW / 2) < (copyLeft + copyRect.width / 2);
    const copyAboveTrio = copyBottom < trioY + GRID;

    let points;

    if (copyAboveTrio) {
        points = [
            { x: width - 1, y: startY },
            { x: aroundRight, y: startY },
            { x: aroundRight, y: aroundTop },
            { x: aroundLeft, y: aroundTop },
            { x: aroundLeft, y: aroundBottom },
            { x: clamp(snap(entryX), GRID, maxX), y: aroundBottom },
            { x: entryX, y: entryY },
        ];
    } else if (trioIsLeftOfCopy) {
        points = [
            { x: width - 1, y: startY },
            { x: aroundRight, y: startY },
            { x: aroundRight, y: aroundTop },
            { x: aroundLeft, y: aroundTop },
            { x: aroundLeft, y: clamp(snap(copyMidY + GRID), GRID, maxY) },
            { x: clamp(snap(aroundLeft - GRID * 2), GRID, maxX), y: clamp(snap(copyMidY + GRID), GRID, maxY) },
            { x: clamp(snap(aroundLeft - GRID * 2), GRID, maxX), y: clamp(snap(entryY), GRID, maxY) },
            { x: entryX, y: entryY },
        ];
    } else {
        points = [
            { x: width - 1, y: clamp(snap(entryY), GRID, maxY) },
            { x: clamp(snap(boxX + boxW + GRID * 2), GRID, maxX), y: clamp(snap(entryY), GRID, maxY) },
            { x: clamp(snap(boxX + boxW + GRID * 2), GRID, maxX), y: aroundTop },
            { x: aroundRight, y: aroundTop },
            { x: aroundRight, y: startY },
            { x: aroundLeft, y: startY },
            { x: aroundLeft, y: aroundBottom },
            { x: clamp(snap(boxX + boxW + GRID), GRID, maxX), y: aroundBottom },
            { x: clamp(snap(boxX + boxW + GRID), GRID, maxX), y: clamp(snap(entryY), GRID, maxY) },
            { x: entryX, y: entryY },
        ];
    }

    const x = boxX;
    const y = boxY;
    const w = boxW;
    const h = boxH;
    const r = Math.min(radius, w / 2, h / 2);
    const midY = y + h / 2;

    // Upward half: right-middle → top → left → left-middle
    const borderUp = [
        `M ${x + w} ${midY}`,
        `L ${x + w} ${y + r}`,
        `Q ${x + w} ${y} ${x + w - r} ${y}`,
        `L ${x + r} ${y}`,
        `Q ${x} ${y} ${x} ${y + r}`,
        `L ${x} ${midY}`,
    ].join(' ');

    // Downward half: right-middle → bottom → left → left-middle
    const borderDown = [
        `M ${x + w} ${midY}`,
        `L ${x + w} ${y + h - r}`,
        `Q ${x + w} ${y + h} ${x + w - r} ${y + h}`,
        `L ${x + r} ${y + h}`,
        `Q ${x} ${y + h} ${x} ${y + h - r}`,
        `L ${x} ${midY}`,
    ].join(' ');

    return {
        travel: roundedPolyline(points),
        borderUp,
        borderDown,
    };
}

let beamFilterSeq = 0;

function ensureBeamFilterId(panel, svg) {
    const filter = svg.querySelector('filter');
    const beams = panel.querySelectorAll('.ps-best-shops__beam-path');

    if (! filter || beams.length === 0) {
        return;
    }

    if (! panel.dataset.beamFilterId) {
        beamFilterSeq += 1;
        panel.dataset.beamFilterId = `ps-best-shops-beam-glow-${beamFilterSeq}`;
    }

    const id = panel.dataset.beamFilterId;

    filter.setAttribute('id', id);
    beams.forEach((beam) => beam.setAttribute('filter', `url(#${id})`));
}

function stopBeamAnimation(beam) {
    beam.getAnimations().forEach((animation) => animation.cancel());
}

function preparePathStroke(beam, length) {
    beam.style.strokeDasharray = String(length);
    beam.style.strokeDashoffset = String(length);
    beam.style.opacity = '0';
}

/**
 * 7s cycle — one continuous motion:
 * charge → travel → at the entry point the same beam splits up/down →
 * hold full circuit (travel + border) → slow fade together
 */
function startCircuitAnimations(travel, borderUp, borderDown, lengths) {
    [travel, borderUp, borderDown].forEach(stopBeamAnimation);

    preparePathStroke(travel, lengths.travel);
    preparePathStroke(borderUp, lengths.borderUp);
    preparePathStroke(borderDown, lengths.borderDown);

    if (prefersReducedMotion() || typeof travel.animate !== 'function') {
        return;
    }

    // Fork point: travel arrives and both borders begin in the same instant.
    const fork = 0.40;
    const borderDone = 0.52;
    const holdEnd = 0.78;

    travel.animate(
        [
            { offset: 0, opacity: 0, strokeDashoffset: lengths.travel, strokeWidth: '5px', easing: 'linear' },
            { offset: 0.07, opacity: 0, strokeDashoffset: lengths.travel, strokeWidth: '5px', easing: 'cubic-bezier(0.4, 0, 0.2, 1)' },
            { offset: 0.09, opacity: 1, strokeDashoffset: lengths.travel, strokeWidth: '5px', easing: 'cubic-bezier(0.33, 0, 0.2, 1)' },
            // Arrive and stay while the beam splits into the border.
            { offset: fork, opacity: 1, strokeDashoffset: 0, strokeWidth: '2px', easing: 'linear' },
            { offset: borderDone, opacity: 1, strokeDashoffset: 0, strokeWidth: '1.75px', easing: 'ease-out' },
            // Tail dissolves slowly after the border is formed.
            { offset: 0.72, opacity: 0, strokeDashoffset: 0, strokeWidth: '1.5px' },
            { offset: 1, opacity: 0, strokeDashoffset: 0, strokeWidth: '1.5px' },
        ],
        { duration: CYCLE_MS, iterations: Infinity, fill: 'forwards' },
    );

    const borderKeyframes = (length) => [
        // Invisible and undrawn until the travel beam reaches the fork.
        { offset: 0, opacity: 0, strokeDashoffset: length, strokeWidth: '2px', easing: 'linear' },
        { offset: fork, opacity: 1, strokeDashoffset: length, strokeWidth: '2.25px', easing: 'cubic-bezier(0.33, 0, 0.2, 1)' },
        { offset: borderDone, opacity: 1, strokeDashoffset: 0, strokeWidth: '1.75px', easing: 'linear' },
        { offset: holdEnd, opacity: 1, strokeDashoffset: 0, strokeWidth: '1.75px', easing: 'ease-out' },
        { offset: 1, opacity: 0, strokeDashoffset: 0, strokeWidth: '1.5px' },
    ];

    borderUp.animate(borderKeyframes(lengths.borderUp), {
        duration: CYCLE_MS,
        iterations: Infinity,
        fill: 'forwards',
    });

    borderDown.animate(borderKeyframes(lengths.borderDown), {
        duration: CYCLE_MS,
        iterations: Infinity,
        fill: 'forwards',
    });
}

function prepareBeamPath(panel) {
    const trio = panel.querySelector('[data-best-shops-trio]');
    const copy = panel.querySelector('[data-best-shops-copy]');
    const travel = panel.querySelector('[data-best-shops-beam]');
    const borderUp = panel.querySelector('[data-best-shops-beam-up]');
    const borderDown = panel.querySelector('[data-best-shops-beam-down]');
    const svg = panel.querySelector('[data-best-shops-circuit]');

    if (! trio || ! copy || ! travel || ! borderUp || ! borderDown || ! svg) {
        return;
    }

    const width = panel.clientWidth;
    const height = panel.clientHeight;

    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.setAttribute('width', String(width));
    svg.setAttribute('height', String(height));
    ensureBeamFilterId(panel, svg);

    const paths = buildCircuitPaths(panel, trio, copy);

    if (! paths) {
        return;
    }

    travel.setAttribute('d', paths.travel);
    borderUp.setAttribute('d', paths.borderUp);
    borderDown.setAttribute('d', paths.borderDown);

    const lengths = {
        travel: travel.getTotalLength(),
        borderUp: borderUp.getTotalLength(),
        borderDown: borderDown.getTotalLength(),
    };

    panel.dataset.beamTravelLength = String(lengths.travel);
    panel.dataset.beamUpLength = String(lengths.borderUp);
    panel.dataset.beamDownLength = String(lengths.borderDown);

    if (panel.classList.contains('is-visible')) {
        startCircuitAnimations(travel, borderUp, borderDown, lengths);
    } else {
        preparePathStroke(travel, lengths.travel);
        preparePathStroke(borderUp, lengths.borderUp);
        preparePathStroke(borderDown, lengths.borderDown);
    }
}

function revealBestShopsBanner(banner) {
    const panel = banner.querySelector('.ps-best-shops');
    const travel = panel?.querySelector('[data-best-shops-beam]');
    const borderUp = panel?.querySelector('[data-best-shops-beam-up]');
    const borderDown = panel?.querySelector('[data-best-shops-beam-down]');

    if (! panel || ! travel || ! borderUp || ! borderDown) {
        return;
    }

    prepareBeamPath(panel);
    panel.classList.add('is-visible');

    startCircuitAnimations(travel, borderUp, borderDown, {
        travel: Number(panel.dataset.beamTravelLength) || travel.getTotalLength(),
        borderUp: Number(panel.dataset.beamUpLength) || borderUp.getTotalLength(),
        borderDown: Number(panel.dataset.beamDownLength) || borderDown.getTotalLength(),
    });
}

function initBestShopsBanners() {
    const banners = document.querySelectorAll('[data-best-shops-banner]');

    if (banners.length === 0) {
        return;
    }

    const refresh = () => {
        banners.forEach((banner) => {
            const panel = banner.querySelector('.ps-best-shops');

            if (panel) {
                prepareBeamPath(panel);
            }
        });
    };

    if (! ('IntersectionObserver' in window) || prefersReducedMotion()) {
        banners.forEach(revealBestShopsBanner);

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            revealBestShopsBanner(entry.target);
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.28,
        rootMargin: '0px 0px -6% 0px',
    });

    banners.forEach((banner) => {
        const panel = banner.querySelector('.ps-best-shops');

        if (panel) {
            prepareBeamPath(panel);
        }

        observer.observe(banner);
    });

    window.addEventListener('resize', refresh, { passive: true });

    if (typeof window.matchMedia === 'function') {
        const mobileMedia = window.matchMedia('(max-width: 639px)');
        const onLayoutChange = () => refresh();

        if (typeof mobileMedia.addEventListener === 'function') {
            mobileMedia.addEventListener('change', onLayoutChange);
        } else if (typeof mobileMedia.addListener === 'function') {
            mobileMedia.addListener(onLayoutChange);
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBestShopsBanners);
} else {
    initBestShopsBanners();
}
