// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'node:path'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: '127.0.0.1',
    port: 5173,
    // 🔑 KONFIGURASI PROXY WAJIB UNTUK LARAVEL + VUE DI PORT BERBEDA
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
        // ✅ Ini mencegah /api/users → /api/api/users
        rewrite: (path) => path.replace(/^\/api/, ''),
        // Optional: log request untuk debug
        // configure: (proxy, options) => {
        //   console.log('Proxying API request:', options.target + path)
        // },
      }
    }
  }
})