import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp, Link, router } from '@inertiajs/vue3';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.component('InertiaLink', Link);
        app.config.globalProperties.$router = router;

        app.mount(el);
    },
});
