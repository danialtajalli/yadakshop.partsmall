const PRESSABLE_SELECTOR = [
    'a[href]',
    'button:not([disabled])',
    'input[type="submit"]:not([disabled])',
    'input[type="button"]:not([disabled])',
    '[role="button"]:not([disabled])',
    '.ps-btn-primary',
    '.ps-btn-secondary',
    '.ps-expandable-toggle',
    '.ps-carousel-nav',
].join(', ');

function findPressable(target) {
    if (!(target instanceof Element)) {
        return null;
    }

    const pressable = target.closest(PRESSABLE_SELECTOR);

    if (!pressable || pressable.matches('[disabled], [aria-disabled="true"]')) {
        return null;
    }

    return pressable;
}

function clearPressed() {
    document.querySelectorAll('.ps-touch-pressed').forEach((element) => {
        element.classList.remove('ps-touch-pressed');
    });
}

function bindTouchFeedback() {
    document.addEventListener(
        'touchstart',
        (event) => {
            const pressable = findPressable(event.target);

            if (!pressable) {
                return;
            }

            clearPressed();
            pressable.classList.add('ps-touch-pressed');
        },
        { passive: true },
    );

    document.addEventListener('touchend', clearPressed, { passive: true });
    document.addEventListener('touchcancel', clearPressed, { passive: true });
}

if (typeof window !== 'undefined') {
    bindTouchFeedback();
}
