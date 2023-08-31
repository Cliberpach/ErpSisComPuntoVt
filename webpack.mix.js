const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

mix.js('resources/js/appNotify.js', 'public/js')
    .sass('resources/sass/appNotify.scss', 'public/css');
    
mix.webpackConfig({
    resolve: {
        alias: {
            '@sass': path.resolve(__dirname, 'resources/sass'),
        }
    }
}).sourceMaps()
mix.disableNotifications();