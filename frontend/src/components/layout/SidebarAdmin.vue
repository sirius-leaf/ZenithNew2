<!-- src/components/layout/SidebarAdmin.vue -->
<template>
  <aside
    class="fixed left-0 top-[64px] w-[288px] h-[calc(100vh-64px)] bg-white shadow-md z-40 flex flex-col"
  >
    <!-- Menu Items -->
    <div class="flex-1 overflow-y-auto p-6 space-y-4">
      <!-- Kelola Akun -->
      <button
        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl border transition-all"
        :class="isActive('/admin') ? 'border-blue-900 bg-white text-blue-900' : 'border-gray-300 text-gray-700 hover:bg-pink-50'"
        @click="navigateTo('/admin')"
      >
        <svg class="w-5 h-5" :class="isActive('/admin') ? 'text-blue-900' : 'text-pink-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14a4 4 0 014 4m0-4h.01M12 18h.01" />
        </svg>
        <span>Kelola Akun</span>
      </button>

      <!-- Kelola Produk -->
      <button
        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl border transition-all"
        :class="isActive('/admin/kelolaproduk') ? 'border-blue-900 bg-white text-blue-900' : 'border-gray-300 text-gray-700 hover:bg-pink-50'"
        @click="navigateTo('/admin/kelolaproduk')"
      >
        <svg class="w-5 h-5" :class="isActive('/admin/kelolaproduk') ? 'text-blue-900' : 'text-pink-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <span>Kelola Produk</span>
      </button>

      <!-- Konfirmasi -->
      <button
        class="flex items-center gap-3 w-full px-4 py-3 rounded-xl border transition-all"
        :class="isActive('/admin/konfirmasi') ? 'border-blue-900 bg-white text-blue-900' : 'border-gray-300 text-gray-700 hover:bg-pink-50'"
        @click="navigateTo('/admin/konfirmasi')"
      >
        <svg class="w-5 h-5" :class="isActive('/admin/konfirmasi') ? 'text-blue-900' : 'text-pink-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Konfirmasi</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const navigateTo = (path) => {
  router.push(path)
}

// Helper: cek apakah rute aktif
const isActive = (path) => {
  if (path === '/admin') {
    return route.path === '/admin' || route.path === '/admin/'
  }
  return route.path === path
}

// Sync saat mount
onMounted(() => {
  // Tidak perlu set activeRoute manual jika pakai helper
})
</script>