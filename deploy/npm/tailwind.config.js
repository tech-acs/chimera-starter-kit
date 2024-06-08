import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './vendor/uneca/dashboard-starter-kit/**/*.blade.php',
        './vendor/uneca/dashboard-starter-kit/src/Livewire/*.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/MapIndicators/**/*.php',
        './resources/js/ChartEditor/*.jsx'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
    darkMode: 'false',
};
