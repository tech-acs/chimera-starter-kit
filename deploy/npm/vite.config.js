import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(() => {
    const inputs = [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/map.css',
        'resources/js/map.js',
    ];

    return {
        plugins: [
            laravel({
                input: inputs,
                refresh: true,
            }),
        ],
        define: {
            global: {}
        }
    };
});
