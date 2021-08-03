process.argv.push('--https');
const mix = require('laravel-mix');
const fs = require('fs');

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
 
 mix.options({
    hmrOptions: {
        host:'local.apexinnovations.com',
        port: 8080 // Can't use 443 here because address already in use
    }
 });

 mix.webpackConfig({
    devServer: {
      https: {
        key: fs.readFileSync('./ssl/apexinnovations.key'),
        cert: fs.readFileSync('./ssl/apexinnovations.crt')
      }
    }
  })

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer'),
    ])
    .webpackConfig(require('./webpack.config'));

if (mix.inProduction()) {
    mix.version();
}
