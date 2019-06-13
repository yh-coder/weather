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

const entryPoints = [
    'common',
    'earth'
];

for (let ep of entryPoints) {
    mix.js(`resources/js/${ep}.js`, 'public/js');
}

mix.browserSync({
    host:'weather.docker-dev.jp',
    proxy: {
        target: "172.31.7.80",
        ws: true
    },
   open: false
})
.js('resources/js/app.js', 'public/js')
.sass('resources/sass/app.scss', 'public/css')
.options({processCssUrls: false})
.sourceMaps(false)
.version();
