<!-- src/views/pages/Dashboard.vue -->
<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const user = ref(null)

onMounted(async () => {
  const token = localStorage.getItem('authToken')
  if (!token) {
    router.push('/login')
    return
  }

  try {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    const res = await axios.get('http://127.0.0.1:8000/api/user')
    user.value = res.data
  } catch (error) {
    console.error('Gagal mengambil data user:', error)
    localStorage.removeItem('authToken')
    router.push('/login')
  }
})

// Handler logout (opsional: bisa dipindah ke Navbar jika perlu)
const handleLogout = () => {
  localStorage.removeItem('authToken')
  delete axios.defaults.headers.common['Authorization']
  router.push('/login')
}
</script>

<template>
  <div v-if="user" class="min-h-screen bg-gray-50">
    <!-- 🔥 TANPA SIDEBAR — Full-width main content -->
    <main class="p-4 md:p-6">
      <!-- Welcome Message -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Selamat datang, {{ user.name }}</h2>
        <p class="text-gray-600">Email: {{ user.email }}</p>
      </div>

      <!-- 🔥 Tag Populer — Versi Modern -->
      <section class="mb-10">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-gray-800">Tag Populer</h3>
            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
              Sementara
            </span>
          </div>

          <div class="flex flex-col items-center justify-center py-12 px-6 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2-8V6a2 2 0 00-2-2H7a2 2 0 00-2 2v4m10 0h.01M12 16h.01" />
              </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-700 mb-1">Maaf hasil belum ada</h4>
            <p class="text-sm text-gray-500 max-w-md text-center">
              Fitur ini sedang dalam pengembangan.
            </p>
          </div>
        </div>
      </section>

      <!-- 🔥 Rekomendasi Produk — Versi Modern -->
      <section>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-gray-800">Rekomendasi</h3>
            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">
              Kosong
            </span>
          </div>

          <div class="flex flex-col items-center justify-center py-12 px-6 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mb-4">
              <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-700 mb-1">Maaf hasil belum ada</h4>
            <p class="text-sm text-gray-500 max-w-md text-center">
              Belum ada data produk yang direkomendasikan.
            </p>
          </div>
        </div>
      </section>

      <!-- Dynamic Content -->
      <router-view class="mt-8" />
    </main>
  </div>
</template>

<style scoped>
.router-view-enter-active,
.router-view-leave-active {
  transition: opacity 0.3s ease;
}
.router-view-enter-from,
.router-view-leave-to {
  opacity: 0;
}
</style>