import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                'resources/css/map.css',
                'resources/js/map.js',

                'resources/js/ChartEditor/index.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    define: {
        global: {}
    },
    build: {
        target: "ES2022"
    },
});
