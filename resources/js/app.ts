import { createInertiaApp } from '@inertiajs/vue3';
import AOS from 'aos';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import 'aos/dist/aos.css';
import '../css/app.css';
import { initializeTheme } from '@/composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Tradition Me';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);

        AOS.init({
            duration: 600,
            once: true,
            offset: 24,
            easing: 'ease-out-cubic',
        });

        document.addEventListener('inertia:navigated', () => {
            AOS.refreshHard();
        });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
