import './entity-carousel';
import './map/imports';
import './location-selects';

import Alpine from 'alpinejs';
import shopCommentForm from './shop-comment-form';

window.Alpine = Alpine;

Alpine.data('shopCommentForm', shopCommentForm);

Alpine.start();
