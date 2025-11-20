<!-- src/views/pages/Dashboard.vue -->
<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const user = ref(null);

var role = "";

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  try {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    const res = await axios.get("http://127.0.0.1:8000/api/user");
    user.value = res.data;

    // Opsional: simpan role di localStorage untuk akses cepat di route guard
    if (res.data.role) {
      localStorage.setItem("userRole", res.data.role);
      role = res.data.role;
    }
  } catch (error) {
    console.error("Gagal mengambil data user:", error);
    localStorage.removeItem("authToken");
    localStorage.removeItem("userRole");
    router.push("/login");
  }
});

// Handler logout (opsional)
const handleLogout = () => {
  localStorage.removeItem("authToken");
  localStorage.removeItem("userRole");
  delete axios.defaults.headers.common["Authorization"];
  router.push("/login");
};
</script>

<template>
  <div v-if="user" class="min-h-screen bg-gray-50">
    <!-- 🔥 TANPA SIDEBAR — untuk SEMUA ROLE di halaman ini -->
    <main class="p-4 md:p-6">
      <!-- Welcome Message -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Selamat datang, {{ user.name }}</h2>
        <p class="text-gray-600">Email: {{ user.email }}</p>
      </div>

      <!-- 🔥 Tag Populer — Versi Modern (sama seperti Versi 1) -->
      <section class="mb-10">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-gray-800">Tag Populer</h3>
            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
              Sementara
            </span>
          </div>
        </div>

        <!-- 2. STATUS & ACTIONS BOX (Pengganti logika @if Blade) -->
        <section class="mb-8">
          <!-- A. Jika User Biasa -->
          <div v-if="user.role === 'user'" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
            <h3 class="text-lg font-bold mb-2">Ingin mulai berjualan?</h3>
            <p class="mb-4 opacity-90">
              Daftarkan akun Anda menjadi penjual untuk mulai membuka toko dan menjual produk rakitan PC.
            </p>
            <button @click="requestSeller" :disabled="loadingSeller" class="bg-white text-blue-600 px-5 py-2 rounded-lg font-semibold hover:bg-gray-100 transition disabled:opacity-70">
              {{ loadingSeller ? "Memproses..." : "Daftar Menjadi Penjual" }}
            </button>
          </div>

          <!-- B. Jika Pending -->
          <div v-else-if="user.role === 'penjual_pending'" class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 flex items-center gap-4">
            <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">⏳</div>
            <div>
              <h3 class="text-lg font-bold text-yellow-800">
                Menunggu Konfirmasi
              </h3>
              <p class="text-yellow-700">
                Permintaan Anda untuk menjadi penjual sedang ditinjau oleh Admin.
              </p>
            </div>
          </div>

          <!-- C. Jika Penjual -->
          <div v-else-if="user.role === 'penjual'" class="bg-green-50 border border-green-200 rounded-xl p-6">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-bold text-green-800">
                  Panel Penjual Aktif
                </h3>
                <p class="text-green-700">
                  Anda memiliki akses penuh untuk mengelola toko.
                </p>
              </div>
              <!-- Link ini aman karena CreateToko.vue sudah menghandle pengecekan 'hasShop' -->
              <router-link to="/dashboard/manage/create-toko" class="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700 transition shadow">
                Kelola / Buka Toko
              </router-link>
            </div>
          </div>
        </section>

        <!-- 3. Placeholder Features (Tag Populer dll) -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Tag Populer -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="font-bold text-gray-800">Tag Populer</h3>
              <span class="text-xs bg-gray-100 px-2 py-1 rounded">Demo</span>
            </div>
            <div class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-100 rounded-lg">
              <p class="text-gray-400 text-sm">Belum ada data tag.</p>
            </div>
          </div>

          <!-- Rekomendasi -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="font-bold text-gray-800">Rekomendasi</h3>
              <span class="text-xs bg-gray-100 px-2 py-1 rounded">Demo</span>
            </div>
            <div class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-100 rounded-lg">
              <p class="text-gray-400 text-sm">Belum ada rekomendasi.</p>
            </div>
            <h4 class="text-lg font-medium text-gray-700 mb-1">
              Maaf hasil belum ada
            </h4>
            <p class="text-sm text-gray-500 max-w-md text-center">
              Belum ada data produk yang direkomendasikan.
            </p>
          </div>
        </section>

        <!-- 4. Router View (Untuk Konten Anak: Manage User, Toko, dll) -->
        <!-- Ini tempat ManageUser.vue, CreateToko.vue, dll akan muncul -->
        <div v-if="$route.path !== '/dashboard'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <router-view />
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
            <h4 class="text-lg font-medium text-gray-700 mb-1">
              Maaf hasil belum ada
            </h4>
            <p class="text-sm text-gray-500 max-w-md text-center">
              Belum ada data produk yang direkomendasikan.
            </p>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
/* Animasi tetap bisa dipertahankan jika suatu saat butuh router-view */
/* Tapi untuk sekarang, tidak digunakan */
</style>
