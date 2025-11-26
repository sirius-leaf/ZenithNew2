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

// Dropdown & Modal State
const isProfileOpen = ref(false)
const showLogoutModal = ref(false)
const profileDropdownRef = ref(null)

// Close dropdown when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
      isProfileOpen.value = false
    }
  })
})

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

// Confirm Logout
const confirmLogout = () => {
  isProfileOpen.value = false
  showLogoutModal.value = true
}

// Logout
const logout = (e) => {
  if(e) e.stopPropagation()
  localStorage.removeItem('authToken')
  delete axios.defaults.headers.common['Authorization']
  isLoggedIn.value = false
  user.value = null
  isOpen.value = false
  showLogoutModal.value = false
  router.push('/')
}

// Pencarian
const searchQuery = ref('')
const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/searching', query: { q: searchQuery.value } })
  }
}
</script>

<template>
  <header class="bg-white shadow-sm border-b border-gray-200 relative z-50">
    <div class="flex items-center justify-between px-4 md:px-8 py-3 gap-6">
      <!-- Logo -->
      <RouterLink to="/dashboard" class="flex-shrink-0">
        <img src="/src/assets/logo.png" alt="Zenith Logo" class="h-12 w-auto" />
      </RouterLink>

      <!-- Pencarian -->
      <div class="flex-1 max-w-4xl">
        <div class="relative">
          <input
            v-model="searchQuery"
            @keypress.enter.prevent="handleSearch"
            type="text"
            placeholder="Cari produk..."
            class="w-full px-5 py-2.5 text-md text-gray-700 bg-gray-100 border-none rounded-full focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all"
          />
          <button @click="handleSearch" class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1.5 bg-pink-600 rounded-full text-white hover:bg-pink-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Desktop Lab Button -->
      <RouterLink
        to="/dashboard/manage/pcBuild"
        class="hidden md:inline-flex items-center px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold text-sm rounded-lg hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow-sm whitespace-nowrap"
      >
        <img :src="desktopIcon" alt="Desktop Lab" class="w-5 h-5 mr-2" />
        DESKTOP LAB
      </RouterLink>

      <!-- Keranjang & Akun -->
      <div class="flex items-center gap-6">
        <!-- Cart -->
        <RouterLink to="/cart" class="text-gray-500 hover:text-pink-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </RouterLink>

        <!-- Akun Dropdown -->
        <div v-if="isLoggedIn" class="relative" ref="profileDropdownRef">
          <button 
            @click="isProfileOpen = !isProfileOpen"
            class="flex items-center gap-2 focus:outline-none"
          >
            <div class="w-9 h-9 rounded-full bg-pink-50 flex items-center justify-center border border-pink-100 text-pink-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
          </button>

          <!-- Dropdown Menu -->
          <div 
            v-if="isProfileOpen"
            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50 transform origin-top-right transition-all"
          >
            <div class="px-4 py-2 border-b border-gray-100 mb-1">
              <p class="text-sm font-semibold text-gray-900 truncate">{{ user?.name || 'User' }}</p>
              <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
            </div>
            
            <RouterLink 
              to="/profile" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors"
              @click="isProfileOpen = false"
            >
              Profile
            </RouterLink>
            
            <button 
              @click="confirmLogout"
              class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
            >
              Logout
            </button>
          </div>
        </div>

        <div v-else class="flex gap-2">
          <RouterLink to="/login" class="text-sm font-medium text-gray-600 hover:text-pink-600 px-3 py-2">Login</RouterLink>
          <RouterLink to="/register" class="text-sm font-medium bg-pink-600 text-white px-4 py-2 rounded-full hover:bg-pink-700 transition shadow-sm">Register</RouterLink>
        </div>
      </div>
    </div>

    <!-- Logout Modal -->
    <div v-if="showLogoutModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 transform transition-all scale-100">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Logout</h3>
          <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin keluar dari akun Anda?</p>
          <div class="flex gap-3">
            <button 
              @click="showLogoutModal = false"
              class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition"
            >
              Batal
            </button>
            <button 
              @click="logout"
              class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition"
            >
              Keluar
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>