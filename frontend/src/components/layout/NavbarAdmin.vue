<!-- src/components/layout/Navbar.vue -->
<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import axios from 'axios'

// Import gambar
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
    <header class="bg-white shadow-sm border-b border-gray-200 z-50">
    <div class="flex items-center justify-between px-4 md:px-8 py-3">
      <!-- Logo -->
      <RouterLink to="/dashboard" class="flex items-center gap-2">
        <img src="/src/assets/logo.png" alt="Zenith Logo" class="h-14 w-auto" />
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
  </header>
</template>