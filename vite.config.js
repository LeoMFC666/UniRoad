import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/artisanflow.css',
                'resources/js/app.js',
                'resources/js/artisanflow-editor.js',
                'resources/js/artisanflow-utils.js'
            ],
            refresh: true,
        }),
    ],
});
