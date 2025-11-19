<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const user = ref(null);
const loadingSeller = ref(false); // State loading untuk tombol request

// Fungsi untuk mengambil data user
const fetchUser = async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  try {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    // Pastikan backend mengembalikan relasi 'toko' jika ada
    // User::with('toko')->find(id) di backend
    const res = await axios.get("http://127.0.0.1:8000/api/user");
    user.value = res.data;

    // Update role di localStorage agar sinkron
    if (res.data.role) {
      localStorage.setItem("userRole", res.data.role);
    }
  } catch (error) {
    localStorage.removeItem("authToken");
    localStorage.removeItem("userRole");
    router.push("/login");
  }
};

// Fungsi Request Jadi Penjual
const requestSeller = async () => {
  if (!confirm("Apakah Anda yakin ingin mendaftar menjadi penjual?")) return;

  loadingSeller.value = true;
  try {
    const token = localStorage.getItem("authToken");
    const response = await axios.post(
      "http://127.0.0.1:8000/api/manage/become-seller",
      {},
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    alert(response.data.message || "Permintaan berhasil dikirim!");

    // Update data user lokal secara manual agar UI berubah langsung
    if (user.value) {
      user.value.role = "penjual_pending";
      localStorage.setItem("userRole", "penjual_pending");
    }
  } catch (error) {
    console.error(error);
    alert(error.response?.data?.message || "Gagal mengirim permintaan.");
  } finally {
    loadingSeller.value = false;
  }
};

// Fungsi Logout
const logout = async () => {
  if (!confirm("Yakin ingin logout?")) return;

  try {
    // Opsional: Panggil API logout backend jika ada
    // await axios.post('http://127.0.0.1:8000/api/logout');
  } catch (e) {
    // ignore error
  } finally {
    localStorage.removeItem("authToken");
    localStorage.removeItem("userRole");
    router.push("/login");
  }
};

onMounted(() => {
  fetchUser();
});
</script>

<template>
  <div v-if="user" class="min-h-screen bg-gray-50 font-sans">
    <div class="flex min-h-screen">
      <!-- =========================== -->
      <!--         SIDEBAR             -->
      <!-- =========================== -->
      <aside
        class="w-64 flex-none bg-white border-r border-gray-200 flex flex-col"
      >
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            Dashboard
          </h2>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
          <router-link
            to="/dashboard"
            class="flex items-center px-4 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors group"
          >
            <span class="font-medium">Home</span>
          </router-link>
          <router-link
            to="/dashboard/manage/users"
            class="flex items-center px-4 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
          >
            Manage Users
          </router-link>
          <router-link
            to="/dashboard/manage/pcBuild"
            class="flex items-center px-4 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
          >
            Manage PC Build
          </router-link>
          <router-link
            to="#"
            class="flex items-center px-4 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
          >
            Manage Item
          </router-link>

          <!-- Menu Khusus Penjual -->
          <div v-if="user.role === 'penjual'" class="mt-4">
            <p
              class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
            >
              Penjual
            </p>
            <router-link
              to="/dashboard/manage/create-toko"
              class="flex items-center px-4 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
            >
              Kelola Toko
            </router-link>
          </div>

          <!-- Menu Khusus Admin -->
          <div v-if="user.role === 'admin'" class="mt-4">
            <p
              class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
            >
              Admin
            </p>

            <router-link
              to="/dashboard/manage/seller-requests"
              class="flex items-center px-4 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors"
            >
              Request Penjual
            </router-link>
          </div>
        </nav>

        <div class="p-4 border-t border-gray-200">
          <button
            @click="logout"
            class="w-full flex items-center px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors"
          >
            Logout
          </button>
        </div>
      </aside>

      <!-- =========================== -->
      <!--       KONTEN UTAMA          -->
      <!-- =========================== -->
      <main class="flex-1 p-8 overflow-y-auto h-screen">
        <!-- 1. Header Welcome -->
        <header class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">
              Halo, {{ user.name }} 👋
            </h1>
            <p class="text-gray-500 mt-1">
              Selamat datang di panel kontrol Anda.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <span
              class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
              :class="{
                'bg-green-100 text-green-800': user.role === 'penjual',
                'bg-purple-100 text-purple-800': user.role === 'admin',
                'bg-gray-100 text-gray-800': user.role === 'user',
                'bg-yellow-100 text-yellow-800':
                  user.role === 'penjual_pending',
              }"
            >
              {{ user.role.replace("_", " ") }}
            </span>
          </div>
        </header>

        <!-- 2. STATUS & ACTIONS BOX (Pengganti logika @if Blade) -->
        <section class="mb-8">
          <!-- A. Jika User Biasa -->
          <div
            v-if="user.role === 'user'"
            class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg"
          >
            <h3 class="text-lg font-bold mb-2">Ingin mulai berjualan?</h3>
            <p class="mb-4 opacity-90">
              Daftarkan akun Anda menjadi penjual untuk mulai membuka toko dan
              menjual produk rakitan PC.
            </p>
            <button
              @click="requestSeller"
              :disabled="loadingSeller"
              class="bg-white text-blue-600 px-5 py-2 rounded-lg font-semibold hover:bg-gray-100 transition disabled:opacity-70"
            >
              {{ loadingSeller ? "Memproses..." : "Daftar Menjadi Penjual" }}
            </button>
          </div>

          <!-- B. Jika Pending -->
          <div
            v-else-if="user.role === 'penjual_pending'"
            class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 flex items-center gap-4"
          >
            <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">⏳</div>
            <div>
              <h3 class="text-lg font-bold text-yellow-800">
                Menunggu Konfirmasi
              </h3>
              <p class="text-yellow-700">
                Permintaan Anda untuk menjadi penjual sedang ditinjau oleh
                Admin.
              </p>
            </div>
          </div>

          <!-- C. Jika Penjual -->
          <div
            v-else-if="user.role === 'penjual'"
            class="bg-green-50 border border-green-200 rounded-xl p-6"
          >
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
              <router-link
                to="/dashboard/manage/create-toko"
                class="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700 transition shadow"
              >
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
            <div
              class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-100 rounded-lg"
            >
              <p class="text-gray-400 text-sm">Belum ada data tag.</p>
            </div>
          </div>

          <!-- Rekomendasi -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="font-bold text-gray-800">Rekomendasi</h3>
              <span class="text-xs bg-gray-100 px-2 py-1 rounded">Demo</span>
            </div>
            <div
              class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-100 rounded-lg"
            >
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
        <div
          v-if="$route.path !== '/dashboard'"
          class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
        >
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
/* Styling scrollbar sidebar agar lebih rapi */
aside nav::-webkit-scrollbar {
  width: 4px;
}
aside nav::-webkit-scrollbar-track {
  background: transparent;
}
aside nav::-webkit-scrollbar-thumb {
  background-color: #e5e7eb;
  border-radius: 20px;
}
</style>
