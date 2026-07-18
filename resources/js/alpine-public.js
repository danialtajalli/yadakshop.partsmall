import Alpine from 'alpinejs';
import shopCommentForm from './shop-comment-form';
import catalogClientSearch from './catalog-client-search';
import catalogRemoteSearch from './catalog-remote-search';

window.Alpine = Alpine;

Alpine.data('shopCommentForm', shopCommentForm);
Alpine.data('catalogClientSearch', catalogClientSearch);
Alpine.data('catalogRemoteSearch', catalogRemoteSearch);

Alpine.start();
