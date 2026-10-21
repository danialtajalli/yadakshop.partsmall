(() => {
    'use strict';

    const projectId = document.currentScript?.dataset.clarityProjectId;
    const banner = document.getElementById('ps-clarity-consent');
    const settings = document.getElementById('ps-clarity-settings');
    const accept = document.getElementById('ps-clarity-accept');
    const reject = document.getElementById('ps-clarity-reject');

    if (!/^[a-z0-9]+$/i.test(projectId ?? '') || !banner || !settings || !accept || !reject) {
        return;
    }

    // Scope consent to this project. Do not reuse a decision for another tracker.
    const storageKey = `partsmall.clarity.consent.v1.${projectId}`;
    const lifetime = 180 * 24 * 60 * 60 * 1000;
    let started = false;

    function readDecision() {
        try {
            const stored = JSON.parse(window.localStorage.getItem(storageKey));
            if (stored && ['granted', 'denied'].includes(stored.value)
                && Number.isFinite(stored.expiresAt) && stored.expiresAt > Date.now()
                && stored.expiresAt <= Date.now() + lifetime) {
                return stored.value;
            }
        } catch {
            // Storage can be blocked. Ask again on the next page in that case.
        }

        return null;
    }

    function sendConsent(value) {
        window.clarity('consentv2', {
            analytics_Storage: value,
            ad_Storage: 'denied',
        });
    }

    function start() {
        if (started) return;
        started = true;

        window.clarity = window.clarity || function () {
            (window.clarity.q = window.clarity.q || []).push(arguments);
        };
        // Queue consent before fetching the tag. No Microsoft request occurs before opt-in.
        sendConsent('granted');
        const script = document.createElement('script');
        script.async = true;
        script.src = `https://www.clarity.ms/tag/${projectId}`;
        document.head.appendChild(script);
    }

    function showBanner(visible) {
        banner.hidden = !visible;
        settings.hidden = visible;
        settings.setAttribute('aria-expanded', String(visible));
    }

    function choose(value) {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify({
                value,
                expiresAt: Date.now() + lifetime,
            }));
        } catch {
            // This page still honors the visitor's choice without persistent storage.
            try {
                window.localStorage.removeItem(storageKey);
            } catch {
                // Storage access itself may be unavailable.
            }
        }

        if (value === 'granted') start();
        if (value === 'denied' && started) {
            sendConsent('denied');
            // Unload the tag after withdrawal; subsequent pages will not load it.
            window.setTimeout(() => window.location.reload(), 250);
        }

        showBanner(false);
        settings.focus({ preventScroll: true });
    }

    settings.addEventListener('click', () => {
        showBanner(true);
        reject.focus();
    });
    accept.addEventListener('click', () => choose('granted'));
    reject.addEventListener('click', () => choose('denied'));

    // Apply a withdrawal made in another tab to this page as well.
    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey && event.key !== null) return;
        const decision = readDecision();
        if (started && decision !== 'granted') {
            sendConsent('denied');
            window.location.reload();
            return;
        }
        if (decision === 'granted') start();
        showBanner(decision === null);
    });

    const decision = readDecision();
    showBanner(decision === null);
    if (decision === 'granted') start();
})();
