import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/page/maintenances.css',
                'resources/js/app.js',
                'resources/js/page/maintenances.js'
            ],
            refresh: true,
        }),
    ],
});
