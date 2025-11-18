<!-- src/components/layout/SidebarAdmin.vue -->
<template>
  <aside
    class="fixed left-0 top-16 w-72 h-[calc(100vh-4rem)] bg-white border-r border-gray-200 z-40 flex flex-col"
    :class="{ 'shadow-lg': isActiveRoute }"
  >
    <!-- Main Nav -->
    <nav class="flex-1 px-4 py-12 overflow-y-auto">
      <ul class="space-y-4">
        <li v-for="item in menuItems" :key="item.path">
          <RouterLink
            :to="item.path"
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-colors w-full"
            :class="[
              isActive(item.path)
                ? 'bg-pink-200 text-pink-700 font-medium border-l-8'
                : 'text-gray-600 hover:bg-blue-100',
            ]"
            active-class=""
          >
            <svg
              class="w-5 h-5 flex-shrink-0"
              :class="isActive(item.path) ? 'text-blue-900' : 'text-gray-500'"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="item.icon"
              />
            </svg>
            <span>{{ item.label }}</span>
          </RouterLink>
        </li>
      </ul>
    </nav>

    <!-- Sidebar Footer (optional logout) -->
    <!-- Uncomment if needed -->
    <!--
    <div class="p-4 border-t border-gray-100">
      <button
        class="flex items-center gap-3 w-full px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
        @click="logout"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span>Keluar</span>
      </button>
    </div>
    -->
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// ✅ Menu items
const menuItems = [
  {
    label: 'Kelola Akun',
    path: '/admin',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7m14 0a7 7 0 00-7-7',
  },
  {
    label: 'Kelola Produk',
    path: '/admin/kelolaproduk',
    icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
  },
  {
    label: 'Konfirmasi',
    path: '/admin/konfirmasi',
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  },
]

// ✅ Helper: active route matching (supports /admin dan /admin/)
const isActive = (path) => {
  if (path === '/admin') {
    return route.path === '/admin' || route.path === '/admin/'
  }
  return route.path.startsWith(path) // mendukung nested route seperti /admin/kelolaproduk/edit/1
}

// ✅ Computed untuk tambahan styling (misal: shadow saat aktif)
const isActiveRoute = computed(() => {
  return menuItems.some(item => isActive(item.path))
})

// ❗ Jika kamu ingin tambahkan logout:
// const logout = () => {
//   localStorage.removeItem('authToken')
//   router.push('/')
// }
</script>

<style scoped>
/* Opsional: smooth scroll behavior */
aside {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f8fafc;
}
aside::-webkit-scrollbar {
  width: 6px;
}
aside::-webkit-scrollbar-track {
  background: #f8fafc;
}
aside::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 3px;
}
</style>