let mix = require('laravel-mix');

mix.js(
    'src/assetbundles/cloudflare/src/js/settings.js',
    'src/assetbundles/cloudflare/dist/js/settings.js'
);
