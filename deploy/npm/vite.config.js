import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                'resources/css/map.css',
                'resources/js/map.js',

                'resources/js/chart.js'
            ],
            refresh: true,
        }),
    ],
});
