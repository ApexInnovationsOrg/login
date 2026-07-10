import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                // Existing templates reference /assets/... (files that live in
                // public/assets, served statically). @vitejs/plugin-vue
                // resolves absolute template asset URLs as build imports by
                // default, which breaks since those files aren't part of the
                // Vite pipeline. Keep them as literal URLs, matching Mix.
                transformAssetUrls: false,
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
        // Breeze-era imports omit the .vue extension ('@/Components/Button')
        extensions: ['.mjs', '.js', '.json', '.vue'],
    },
});
