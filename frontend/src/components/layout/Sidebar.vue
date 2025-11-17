<!-- src/components/Sidebar.vue -->
<template>
  <aside
    class="w-64 flex-none border-r border-gray-200 p-4 flex flex-col"
  >
    <!-- Menu Section -->
    <nav class="mt-2 flex-1">
      <div class="space-y-1">
        <router-link
          v-for="(item, index) in menuItems"
          :key="index"
          :to="item.path"
          class="block px-2 py-2 text-gray-700 font-medium transition-all duration-200 hover:text-gray-900 focus:outline-none"
          :class="{
            'text-pink-600 font-semibold': isCurrentRoute(item.path),
            'border-l-2 border-pink-600 pl-2 -ml-2': isCurrentRoute(item.path)
          }"
        >
          {{ item.label }}
        </router-link>
      </div>
    </nav>

    <!-- Logout Button -->
    <div class="pt-4 mt-auto border-t border-gray-200">
      <button
        @click="onLogout"
        class="block w-full text-left px-2 py-2 text-gray-700 font-medium transition-colors duration-200 hover:text-red-600 focus:outline-none"
      >
        Keluar
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const emit = defineEmits(['logout'])

const route = useRoute()

const menuItems = [
  { label: 'Beranda', path: '/dashboard' },
  { label: 'Manage PC Build', path: '/dashboard/manage/pcBuild' }
]

// Fungsi untuk cek apakah route saat ini sama dengan path menu
const isCurrentRoute = (path) => {
  return route.path === path
}

const onLogout = () => {
  emit('logout')
}
</script>

<style scoped>
/* Optional: tambahkan sedikit underline saat hover */
.router-link-exact-active:hover {
  text-decoration: underline;
  text-decoration-color: #fbbf24;
  text-underline-offset: 4px;
}
</style>