const HALF_MS = 140;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function isTouchLikePointer() {
    return window.matchMedia('(hover: none), (pointer: coarse)').matches;
}

function sleep(ms) {
    return new Promise((resolve) => window.setTimeout(resolve, ms));
}

function cancelShellAnimations(shell) {
    shell.getAnimations().forEach((animation) => animation.cancel());
}

async function turnShell(shell, to, easing) {
    cancelShellAnimations(shell);

    const computed = getComputedStyle(shell).transform;
    const from = computed === 'none' ? 'rotateY(0deg)' : computed;

    const animation = shell.animate(
        [
            { transform: from },
            { transform: to },
        ],
        {
            duration: HALF_MS,
            easing,
            fill: 'forwards',
        },
    );

    await Promise.race([
        animation.finished.catch(() => {}),
        sleep(HALF_MS + 40),
    ]);
}

function applyFlipContent(card, showParts) {
    const partsPanel = card.querySelector('.ps-part-cat__panel--parts');
    const categoryPanel = card.querySelector('.ps-part-cat__panel--category');

    card.classList.toggle('is-flipped', showParts);
    card.setAttribute('aria-pressed', showParts ? 'true' : 'false');
    partsPanel?.setAttribute('aria-hidden', showParts ? 'false' : 'true');
    categoryPanel?.setAttribute('aria-hidden', showParts ? 'true' : 'false');
}

/**
 * Swift edge-turn: rotate out → swap content → rotate in.
 * Queues the latest intent so rapid hover/tap stays clean.
 */
async function runFlipLoop(card) {
    if (card._flipRunning) {
        return;
    }

    const shell = card.querySelector('.ps-part-cat__shell');

    if (! shell) {
        return;
    }

    card._flipRunning = true;

    try {
        while (Boolean(card._flipDesired) !== card.classList.contains('is-flipped')) {
            if (prefersReducedMotion()) {
                applyFlipContent(card, Boolean(card._flipDesired));
                break;
            }

            card.classList.add('is-turning');

            try {
                await turnShell(shell, 'rotateY(90deg)', 'cubic-bezier(0.4, 0, 1, 1)');

                applyFlipContent(card, Boolean(card._flipDesired));

                cancelShellAnimations(shell);
                shell.style.transform = 'rotateY(-90deg)';
                void shell.offsetWidth;

                await turnShell(shell, 'rotateY(0deg)', 'cubic-bezier(0, 0, 0.2, 1)');
            } finally {
                card.classList.remove('is-turning');
            }
        }
    } finally {
        card._flipRunning = false;
        card.classList.remove('is-turning');
        cancelShellAnimations(shell);
        shell.style.transform = 'rotateY(0deg)';
    }
}

function requestFlip(card, showParts) {
    card._flipDesired = showParts;
    runFlipLoop(card);
}

function bindDesktopHover(cards) {
    cards.forEach((card) => {
        card.addEventListener('pointerenter', () => requestFlip(card, true));
        card.addEventListener('pointerleave', () => requestFlip(card, false));
        card.addEventListener('focusin', () => requestFlip(card, true));
        card.addEventListener('focusout', (event) => {
            if (! card.contains(event.relatedTarget)) {
                requestFlip(card, false);
            }
        });
    });
}

function bindMobileTouch(cards) {
    cards.forEach((card) => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.setAttribute('aria-pressed', 'false');

        const toggle = (event) => {
            if (event.target.closest('a')) {
                return;
            }

            event.preventDefault();
            const showingParts = card._flipDesired ?? card.classList.contains('is-flipped');

            requestFlip(card, ! showingParts);
        };

        card.addEventListener('click', toggle);
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                toggle(event);
            }
        });
    });
}

function initPartCategoryCards() {
    const root = document.querySelector('[data-part-category-cards]');

    if (! root) {
        return;
    }

    const cards = [...root.querySelectorAll('[data-part-category-card]')];

    if (cards.length === 0 || prefersReducedMotion()) {
        return;
    }

    if (isTouchLikePointer()) {
        bindMobileTouch(cards);
    } else {
        bindDesktopHover(cards);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPartCategoryCards);
} else {
    initPartCategoryCards();
}
