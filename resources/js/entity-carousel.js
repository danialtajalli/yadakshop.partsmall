import EmblaCarousel from 'embla-carousel';
import EmblaCarouselAutoplay from 'embla-carousel-autoplay';

const AUTO_PLAY_DELAY = 3000;

export function initEntityCarousels(root = document) {
    root.querySelectorAll('[data-entity-carousel]').forEach((carouselRoot) => {
        if (carouselRoot.dataset.entityCarouselReady === 'true') {
            return;
        }

        const viewport = carouselRoot.querySelector('[data-entity-carousel-viewport]');

        if (!viewport) {
            return;
        }

        const prevButton = carouselRoot.querySelector('[data-entity-carousel-prev]');
        const nextButton = carouselRoot.querySelector('[data-entity-carousel-next]');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const plugins = [];
        let autoplay = null;

        if (!prefersReducedMotion) {
            autoplay = EmblaCarouselAutoplay({
                delay: AUTO_PLAY_DELAY,
                stopOnInteraction: true,
            });
            plugins.push(autoplay);
        }

        const embla = EmblaCarousel(viewport, {
            loop: true,
            align: 'start',
            direction: 'ltr',
            dragFree: false,
            containScroll: 'trimSnaps',
        }, plugins);

        carouselRoot.dataset.entityCarouselReady = 'true';

        prevButton?.addEventListener('click', () => embla.scrollPrev());
        nextButton?.addEventListener('click', () => embla.scrollNext());

        if (autoplay) {
            carouselRoot.addEventListener('mouseenter', autoplay.stop);
            carouselRoot.addEventListener('mouseleave', autoplay.play);
        }

        embla.on('pointerDown', () => viewport.classList.add('is-dragging'));
        embla.on('pointerUp', () => viewport.classList.remove('is-dragging'));
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initEntityCarousels());
} else {
    initEntityCarousels();
}
