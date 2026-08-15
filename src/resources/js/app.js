import './bootstrap';
// import './enable-push'
import { createApp, h } from 'vue';
import {createInertiaApp, Link} from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import ElementPlus from "element-plus";
import { createPinia } from "pinia";
import { Tooltip } from "bootstrap";
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import Master from "@/Layouts/Master.vue";
import vueDropzone from 'vue2-dropzone-vue3'

//imports for app initialization
import { initApexCharts } from "@/core/plugins/apexcharts";
import { initInlineSvg } from "@/core/plugins/inline-svg";
import { initVeeValidate } from "@/core/plugins/vee-validate";
import { initKtIcon } from "@/core/plugins/keenthemes";


import '../css/app.css';
import "bootstrap-icons/font/bootstrap-icons.css";
import "apexcharts/dist/apexcharts.css";
import "quill/dist/quill.snow.css";
import "animate.css";
import "sweetalert2/dist/sweetalert2.css";
import "nouislider/distribute/nouislider.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "socicon/css/socicon.css";
import "line-awesome/dist/line-awesome/css/line-awesome.css";
import "dropzone/dist/dropzone.css";
import "@vueform/multiselect/themes/default.css";
import "element-plus/dist/index.css";

import "@/assets/fonticon/fonticon.css";
import "@/assets/keenicons/duotone/style.css";
import "@/assets/keenicons/outline/style.css";
import "@/assets/keenicons/solid/style.css";
import "@/assets/sass/element-ui.dark.scss";
import "@/assets/sass/plugins.scss";
import "@/assets/sass/style.bundle.rtl.css";
import "@vueform/multiselect/themes/default.css";

import Multiselect from "@vueform/multiselect";
import Permissions from "@/Mixins/Permissions";
import Helpers from "@/Mixins/Helpers";
import Pagination from "@/Components/Pagination.vue";
import jQuery from "jquery";
const $ = jQuery;
window.$ = $;


const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        );
        page.then((module) => {
            module.default.layout = module.default.layout || Master;
        });
        return page;
    },

    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.component('Pagination', Pagination);
        app.component('Multiselect', Multiselect);
        app.component('Link', Link);
        app.component('vueDropzone', vueDropzone);
        app.mixin(Permissions);
        app.mixin(Helpers);
        app.use(createPinia())
        app.use(plugin)
        app.use(ZiggyVue, Ziggy)
        app.use(ElementPlus);
        app.use(VueSweetalert2);

        initApexCharts(app);
        initInlineSvg(app);
        initKtIcon(app);
        initVeeValidate();
        app.directive("tooltip", (el) => {
            new Tooltip(el);
        });
        app.mount(el)
    },
    progress: {
        color: '#4B5563',
    },
});
