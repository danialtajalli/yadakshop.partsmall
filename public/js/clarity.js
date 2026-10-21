(() => {
    'use strict';

    const projectId = document.currentScript?.dataset.clarityProjectId;
    if (!/^[a-z0-9]+$/i.test(projectId ?? '')) return;

    const source = `https://www.clarity.ms/tag/${projectId}`;
    if (document.querySelector(`script[src="${source}"]`)) return;

    window.clarity = window.clarity || function () {
        (window.clarity.q = window.clarity.q || []).push(arguments);
    };

    const script = document.createElement('script');
    script.async = true;
    script.src = source;
    document.head.appendChild(script);
})();
