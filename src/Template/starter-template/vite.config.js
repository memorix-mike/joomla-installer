import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  root: '',
  build: {
    outDir: 'dist',
    cssMinify: true,
  },

  plugins: [
    laravel({
      input: [
        'resources/sass/app.scss',
        'resources/js/main.js',
      ],
      refresh: true,
    }),
  ],
});
