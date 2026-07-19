import './entity-carousel';
import './map/imports';
import './location-selects';
import './touch-feedback';
import './navigation-progress';
import './stats-strip';

const isFilamentPanel = () => {
    const path = window.location.pathname;

    return path === '/admin' || path.startsWith('/admin/');
};

if (! isFilamentPanel()) {
    import('./alpine-public.js');
}
