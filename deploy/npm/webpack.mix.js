const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
    ])
    .js('resources/js/map.js', 'public/js')
    .css('resources/css/map.css', 'public/css')
    .css('resources/css/fonts.css', 'public/css');

mix.copy('./node_modules/plotly.js-basic-dist/plotly-basic.js', 'public/js');

mix.copyDirectory('resources/fonts', 'public/fonts');

if (mix.inProduction()) {
    mix.version();
}
