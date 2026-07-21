import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/landing.css',
                'resources/js/app.js',
                'resources/js/landing.js',
                'resources/js/widget/main.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
            fonts: [
                bunny('Outfit', {
                    weights: [400, 500, 700],
                }),
                bunny('DM Serif Display', {
                    weights: [400],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
