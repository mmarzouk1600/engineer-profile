import './bootstrap';
import '../css/front/app.css';

import "@vueform/multiselect/themes/default.css";
import Multiselect from "@vueform/multiselect";
import 'sweetalert2/dist/sweetalert2.min.css';
import "sweetalert2/dist/sweetalert2.css";
import { createApp, h } from 'vue';
import {createInertiaApp, Link} from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import Helpers from "@/Mixins/Helpers";
import VueSweetalert2 from "vue-sweetalert2";


import Pagination from "@/Components/Pagination.vue";
import Master from "@/Front/Layouts/Master.vue";
import vueDropzone from "vue2-dropzone-vue3";
const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Front/Pages/${name}.vue`,
            import.meta.glob("./Front/Pages/**/*.vue")
        );
        page.then((module) => {
            module.default.layout = module.default.layout || Master;
        });
        return page;
    },

    // resolve: (name) => resolvePageComponent(`./Front/Pages/${name}.vue`, import.meta.glob('./Front/Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.component('Multiselect', Multiselect);
        app.component('Link', Link)
        app.component('vueDropzone', vueDropzone);
        app.mixin(Helpers);
        app.component('Pagination', Pagination);
        app.use(VueSweetalert2);
        app.use(plugin)
        app.use(ZiggyVue, Ziggy)
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
