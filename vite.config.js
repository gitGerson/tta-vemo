import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/sass/sneat.scss',
                'resources/js/app.js',
                'resources/js/modules/sneat-theme.js',
                'resources/js/modules/dashboard.js',
                'resources/js/modules/date-range-picker.js',
                'resources/js/modules/filepond.js',
                'resources/js/modules/rich-editor.js',
                'resources/js/modules/select2.js',
            ],
            refresh: true,
        }),
    ],
});
