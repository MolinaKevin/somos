import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/sass/app.scss'], // Agrega cualquier archivo adicional aquí
            refresh: true,
        }),
        vue(), // Asegúrate de incluir el plugin de Vue
    ],
});

