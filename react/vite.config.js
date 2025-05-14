import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// https://vite.dev/config/
export default defineConfig({
    plugins: [react()],
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        minify: false,
        sourcemap: true,
    },
    server: {
        port: 3000,
        open: true,
    },
});
