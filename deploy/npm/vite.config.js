import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig(({mode}) => {
    // Inputs that are ALWAYS needed (dashboard, public pages, etc.)
    const inputs = [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/map.css',
        'resources/js/map.js',
    ];
    // Only include the React-based editor if we are NOT in production
    if (mode !== 'production') {
        inputs.push('resources/js/ChartEditor/index.jsx');
    }

    return {
        plugins: [
            laravel({
                input: inputs,
                refresh: true,
            }),
            react(),
        ],
        define: {
            global: {}
        },
        build: {
            target: "ES2022",
            // This ensures that if the file isn't in 'input',
            // it and React won't be in the final manifest/vendor folder.
            rollupOptions: {
                output: {
                    manualChunks: (id) => {
                        if (id.includes('node_modules/react')) {
                            return 'react-vendor';
                        }
                    }
                }
            }
        },
    };
});
