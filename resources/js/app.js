import './entity-carousel';
import './map/imports';
import './location-selects';

const isFilamentPanel = () => {
    const path = window.location.pathname;

    return path === '/admin' || path.startsWith('/admin/');
};

if (! isFilamentPanel()) {
    import('./alpine-public.js');
}
