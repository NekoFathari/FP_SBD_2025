import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import Sweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import laravelVuePagination from 'laravel-vue-pagination';
// If you need to use TailwindPagination, ensure you use it in your code; otherwise, remove this import.

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';


createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Sweetalert2)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// kita ambil data tabel anggota dari url
// dan kita set data tersebut ke dalam props anggota
// ini akan digunakan untuk menampilkan data anggota di halaman yang sesuai
// kita juga akan menggunakan data ini untuk menampilkan data anggota di halaman lain
// kita akan menggunakan data ini untuk menampilkan data anggota di halaman lain

// This will set light / dark mode on page load...
initializeTheme();
