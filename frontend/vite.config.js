// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'node:path'  // ✅ Import path (ESM-compatible)

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      // ✅ Konfigurasi alias @ = src/
      '@': path.resolve(__dirname, './src'),
    },
  },
})