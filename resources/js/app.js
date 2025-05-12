require('./bootstrap');

// Import modules...
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import PrimeVue from 'primevue/config';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas, faCartShopping } from '@fortawesome/free-solid-svg-icons'; // Import specific icons
import { far, faUser } from '@fortawesome/free-regular-svg-icons'; // Import specific icons
import { fab, faFacebookF, faLinkedinIn } from '@fortawesome/free-brands-svg-icons'; // Import specific icons

library.add(fab, faFacebookF,faLinkedinIn, far, faUser, fas, faCartShopping);

import 'primevue/resources/themes/saga-blue/theme.css'
import 'primevue/resources/primevue.min.css'                 
import 'primeicons/primeicons.css'    

const el = document.getElementById('app');

createInertiaApp({
    resolve: (name) => require(`./Pages/${name}`),
    setup({ el, app, props, plugin }) {
        createApp({ render: () => h(app, props) })
            .mixin({ methods: { route } })
            .component('font-awesome-icon',FontAwesomeIcon)
            .use(plugin)
            .use(PrimeVue)
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
