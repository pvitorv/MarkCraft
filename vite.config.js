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
    build: {
        // Chunks separados = cache melhor + Studio não baixa PDF/PSD até exportar
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return undefined;
                    }
                    if (id.includes('fabric')) {
                        return 'vendor-fabric';
                    }
                    if (id.includes('alpinejs') || id.includes('@alpinejs')) {
                        return 'vendor-alpine';
                    }
                    if (id.includes('ag-psd')) {
                        return 'vendor-psd';
                    }
                    if (id.includes('jspdf')) {
                        return 'vendor-jspdf';
                    }
                    if (id.includes('pptxgenjs')) {
                        return 'vendor-pptx';
                    }
                    if (id.includes('jszip')) {
                        return 'vendor-jszip';
                    }
                    if (id.includes('pdfjs-dist')) {
                        return 'vendor-pdfjs';
                    }
                    if (id.includes('@imgly/background-removal') || id.includes('onnxruntime')) {
                        return 'vendor-rembg';
                    }
                    if (id.includes('nsfwjs') || id.includes('@tensorflow')) {
                        return 'vendor-nsfw';
                    }
                    return undefined;
                },
            },
        },
        chunkSizeWarningLimit: 900,
    },
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
