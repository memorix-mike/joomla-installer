import { defineConfig } from 'vite'
import path from 'path';
import eslint from 'vite-plugin-eslint';

export default defineConfig({
    build: {
        manifest: true,
        rollupOptions: {
            input: [
                path.resolve(__dirname, 'resources/sass/app.scss'),
                path.resolve(__dirname, 'resources/js/main.js')
            ],
        },
        outDir: 'dist'
    },
    plugins: [
        eslint(),
    ],
});
