import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/studio.css',
                'resources/css/studio-mobile.css',
                'resources/js/app.js',
                'resources/js/image-studio/app-studio.js',
            ],
            // Evita full-reload em cascata (views compiladas / storage) que
            // puxava o browser de volta para /studio ao navegar pelo navbar.
            refresh: [
                'resources/views/**',
                'routes/**',
            ],
        }),
    ],
    server: {
        watch: {
            ignored: [
                '**/storage/**',
                '**/public/build/**',
                '**/node_modules/**',
            ],
        },
    },
});
