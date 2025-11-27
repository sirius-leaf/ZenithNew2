<template>
  <div class="w-full bg-gray-50 p-4 sm:p-6 font-ubuntu">
    <!-- Header -->
    <div class="mb-8 flex items-center gap-2">
      <button @click="$router.back()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
        ← Kembali ke Dashboard
      </button>
    </div>

    <!-- Loading -->
    <div v-if="!user" class="max-w-2xl mx-auto text-center py-10">
      <p class="text-gray-600">Memuat data akun...</p>
    </div>

    <!-- Profile Card -->
    <div v-else class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-6">
      <h2 class="text-lg font-medium text-neutral-950 mb-6">Informasi Akun</h2>

      <div class="space-y-6">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-pink-50 rounded-lg flex justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14c3.866 0 7 1.791 7 4v1H5v-1c0-2.209 3.134-4 7-4zm0-2a4 4 0 100-8 4 4 0 000 8z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Username</p>
            <p class="text-base text-neutral-950">{{ user.name || '—' }}</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-50 rounded-lg flex justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Email</p>
            <p class="text-base text-neutral-950">{{ user.email }}</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-50 rounded-lg flex justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l7 4v6c0 5.25-3.5 10-7 10S5 17.25 5 12V6l7-4zm-2 10l2 2 4-4" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Role</p>
            <p class="text-base text-neutral-950">{{ user.role || 'User' }}</p>
          </div>
        </div>

        <!-- Edit Profil Button – solid blue, tanpa gradient -->
        <div class="pt-4 border-t border-gray-100">
          <button
            @click="$router.push('/profile/edit')"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg flex items-center justify-center gap-2 transition-all duration-200 hover:shadow-md active:scale-[0.98] focus:outline-none"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Profil
          </button>
        </div>
      </div>

      <!-- Status Toko -->
      <div class="mt-8 pt-6 border-t">
        <h3 class="text-lg font-medium text-neutral-950 mb-4">Status Toko</h3>

        <!-- User biasa -->
        <div v-if="user.role === 'user'" class="bg-pink-50 rounded-xl p-6 shadow-sm border border-pink-100">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-white rounded-full border-2 border-pink-500 flex justify-center items-center shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l1-4h16l1 4M4 9h16v10H4V9zm4 5h8" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-medium text-neutral-950 mb-2">Buka Toko Anda Sendiri</h3>
              <p class="text-sm text-gray-600 mb-4">
                Jadilah penjual dan raih penghasilan dengan membuka toko di platform kami.
                Pastikan data profil (nomor telepon & alamat) sudah lengkap!
              </p>
              <button
                @click="requestSeller"
                :disabled="loadingSeller"
                class="w-full py-2 px-4 bg-pink-500 hover:bg-pink-600 text-white font-medium rounded-lg flex items-center justify-center gap-2 transition-all duration-200 hover:shadow-md active:scale-[0.98] cursor-pointer disabled:opacity-60"
              >
                <svg v-if="!loadingSeller" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8m12 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                </svg>
                {{ loadingSeller ? 'Memproses...' : 'Daftar Toko Sekarang' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Pending -->
        <div v-else-if="user.role === 'penjual_pending'" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
          <div class="mt-0.5 w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 text-lg">
            ⏳
          </div>
          <div>
            <h4 class="font-medium text-yellow-800">Menunggu Konfirmasi</h4>
            <p class="text-sm text-yellow-700">Permintaan Anda sedang ditinjau oleh Admin.</p>
          </div>
        </div>

        <!-- Penjual aktif -->
        <div v-else-if="user.role === 'penjual'" class="bg-green-50 border border-green-200 rounded-xl p-4">
          <div class="flex flex-col sm:flex-row justify-between gap-3 items-start sm:items-center">
            <div>
              <h4 class="font-medium text-green-800">Panel Penjual Aktif</h4>
              <p class="text-sm text-green-700">Anda memiliki akses penuh untuk mengelola toko.</p>
            </div>
            <router-link
              to="/dashboard/manage/create-toko"
              class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 whitespace-nowrap"
            >
              Kelola / Buka Toko
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "vue-toastification";

const router = useRouter();
const toast = useToast();
const user = ref(null);
const loadingSeller = ref(false);

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) return router.push("/login");

  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  const res = await axios.get("http://127.0.0.1:8000/api/profile");
  user.value = res.data;
});

const requestSeller = async () => {
  if (!user.value.no_telpon || !user.value.alamat) {
    toast.error("Isi nomor telepon & alamat terlebih dahulu di Profil.", {
      timeout: 3000,
      closeOnClick: true,
    });
    router.push("/profile/edit");
    return;
  }

  loadingSeller.value = true;

  try {
    const token = localStorage.getItem("authToken");
    if (token) {
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }

    const res = await axios.post("http://127.0.0.1:8000/api/manage/become-seller");

    user.value.role = "penjual_pending";
    localStorage.setItem("userRole", "penjual_pending");

    toast.success(res.data?.message ?? "Permintaan berhasil dikirim!", {
      timeout: 2500,
    });
  } catch (error) {
    console.error("Request seller failed:", error);
    const msg = error.response?.data?.message || "Terjadi kesalahan.";
    toast.error(msg, { timeout: 3000 });
  } finally {
    loadingSeller.value = false;
  }
};
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap');

.font-ubuntu {
  font-family: 'Ubuntu', sans-serif;
}
</style>