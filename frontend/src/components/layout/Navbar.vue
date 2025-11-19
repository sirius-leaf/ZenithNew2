<!-- src/components/layout/Navbar.vue -->
<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import axios from 'axios'

// Import gambar
import desktopIcon from '@/assets/DesktopIcon.png'
import cartIcon from '@/assets/CartIcon.png'
import accountIcon from '@/assets/AccountIcon.png'

const isOpen = ref(false)
const isLoggedIn = ref(false)
const user = ref(null) // <-- data user
const router = useRouter()
const route = useRoute()

// Ambil data user dari API (seperti di Dashboard)
const fetchUser = async () => {
  const token = localStorage.getItem('authToken')
  if (!token) {
    isLoggedIn.value = false
    user.value = null
    return
  }

  try {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    const res = await axios.get('http://127.0.0.1:8000/api/user')
    user.value = res.data
    isLoggedIn.value = true
  } catch (error) {
    console.error('Navbar: Gagal ambil data user', error)
    localStorage.removeItem('authToken')
    isLoggedIn.value = false
    user.value = null
  }
}

// Panggil saat komponen dimuat
onMounted(() => {
  fetchUser()
})

// Opsional: refresh saat rute berubah (misal setelah login/register)
watch(() => route.path, () => {
  fetchUser()
})

// Logout
const logout = (e) => {
  e.stopPropagation()
  localStorage.removeItem('authToken')
  delete axios.defaults.headers.common['Authorization']
  isLoggedIn.value = false
  user.value = null
  isOpen.value = false
  router.push('/')
}

// Pencarian
const searchQuery = ref('')
const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/product', query: { q: searchQuery.value } })
  }
}
</script>

<template>
  <header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-4 md:px-8 py-3">
      <!-- Logo -->
      <RouterLink to="/dashboard" class="flex items-center gap-2">
        <img src="/src/assets/logo.png" alt="Zenith Logo" class="h-14 w-auto" />
      </RouterLink>

      <!-- Pencarian -->
      <div class="flex-1 mx-4 max-w-xl">
        <div class="relative">
          <input
            v-model="searchQuery"
            @keypress.enter.prevent="handleSearch"
            type="text"
            placeholder="Cari produk..."
            class="w-full px-4 py-2 text-md text-pink-700 bg-pink-100 border-none rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 absolute right-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- Keranjang & Akun -->
      <div class="flex items-center gap-4">
        <RouterLink to="/cart" class="relative">
          <img :src="cartIcon" alt="Cart" class="h-6 w-6" />
          <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
        </RouterLink>

        <!-- Akun: tampilkan nama dari API -->
        <RouterLink
          v-if="isLoggedIn"
          to="/profile"
          class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-200 transition-colors cursor-pointer"
        >
          <img :src="accountIcon" alt="Profile" class="h-8 w-8 rounded-full" />
          <span class="text-sm font-medium text-gray-700">
            {{ user?.name || 'User' }}
          </span>
          <button
            @click.stop="logout"
            class="ml-3 px-3 py-1 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors duration-200"
          >
            Logout
          </button>
        </RouterLink>

        <div v-else class="flex gap-2">
          <RouterLink to="/login" class="text-sm text-pink-600 hover:text-pink-800">Login</RouterLink>
          <RouterLink to="/register" class="text-sm bg-pink-600 text-white px-3 py-1 rounded-md hover:bg-pink-700">Register</RouterLink>
        </div>
      </div>
    </div>

    <div class="px-4 md:px-8 py-2 flex justify-end">
      <RouterLink
        to="/dashboard/manage/pcBuild"
        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold text-sm rounded-md hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow-sm"
      >
        <img :src="desktopIcon" alt="Desktop Lab" class="w-5 h-5 mr-2" />
        DESKTOP LAB
      </RouterLink>
    </div>
  </header>
</template>