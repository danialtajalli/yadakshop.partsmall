<script>
(function () {
    try {
        var navigationEntry = performance.getEntriesByType('navigation')[0];

        if (navigationEntry && navigationEntry.type === 'back_forward') {
            sessionStorage.removeItem('ps-nav-progress');
            sessionStorage.removeItem('ps-nav-progress-value');

            return;
        }

        if (sessionStorage.getItem('ps-nav-progress') !== 'active') {
            return;
        }

        var value = sessionStorage.getItem('ps-nav-progress-value') || '28';
        var style = document.createElement('style');
        style.id = 'ps-nav-progress-boot';
        style.textContent = '#ps-navigation-progress{width:' + value + '%;opacity:1}';
        document.head.appendChild(style);
    } catch (e) {}
})();
</script>
