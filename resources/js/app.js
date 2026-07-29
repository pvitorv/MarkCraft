import './bootstrap';

import Alpine from 'alpinejs';
import markCraftHub from './markCraftHub';

document.addEventListener('alpine:init', () => {
    Alpine.data('markCraftHub', markCraftHub);
});

window.Alpine = Alpine;

Alpine.start();
