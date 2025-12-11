<!-- src/components/layout/Navbar.vue -->
<script setup>
import { ref, onMounted, watch } from "vue";
import { RouterLink, useRouter, useRoute } from "vue-router";
import axios from "axios";
import { useCartStore } from "@/stores/cartStore"; // Import store

// Import gambar
import desktopIcon from "@/assets/DesktopIcon.png";
import cartIcon from "@/assets/CartIcon.png";
import accountIcon from "@/assets/AccountIcon.png";

const { totalItems } = useCartStore(); // Gunakan store

const isOpen = ref(false);
const isLoggedIn = ref(false);
const user = ref(null); // <-- data user
const router = useRouter();
const route = useRoute();

// Dropdown & Modal State
const isProfileOpen = ref(false);
const showLogoutModal = ref(false);
const profileDropdownRef = ref(null);

// Close dropdown when clicking outside
onMounted(() => {
  document.addEventListener("click", (e) => {
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
      isProfileOpen.value = false;
    }
  });
});

// Ambil data user dari API (seperti di Dashboard)
const fetchUser = async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    isLoggedIn.value = false;
    user.value = null;
    return;
  }

  try {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    const res = await axios.get("http://127.0.0.1:8000/api/user");
    user.value = res.data;
    isLoggedIn.value = true;
  } catch (error) {
    console.error("Navbar: Gagal ambil data user", error);
    localStorage.removeItem("authToken");
    isLoggedIn.value = false;
    user.value = null;
  }
};

// Panggil saat komponen dimuat
onMounted(() => {
  fetchUser();
  window.addEventListener("user-profile-updated", fetchUser);
});

// Opsional: refresh saat rute berubah (misal setelah login/register)
watch(
  () => route.path,
  () => {
    fetchUser();
  }
);

// Confirm Logout
const confirmLogout = () => {
  isProfileOpen.value = false;
  showLogoutModal.value = true;
};

// [PERBAIKAN] Fungsi Logout diperbarui
const logout = async (e) => {
  if (e) e.stopPropagation();

  try {
    // 1. Kabari Backend dulu (Supaya Log tercatat & Token dihapus di DB)
    // Gunakan URL lengkap sesuai pattern Anda
    await axios.post("http://127.0.0.1:8000/api/logout");
  } catch (error) {
    // Jika token sudah expired atau server error, kita tetap lanjut logout di frontend
    console.error("Gagal logout di server atau token sudah kadaluarsa:", error);
  } finally {
    // 2. Hapus data di Client (Browser)
    localStorage.removeItem("authToken");
    localStorage.removeItem("userRole"); // [Tambahan] Hapus role juga biar bersih
    delete axios.defaults.headers.common["Authorization"];

    // Reset state
    isLoggedIn.value = false;
    user.value = null;
    isOpen.value = false;
    showLogoutModal.value = false;

    // Redirect ke home
    router.push("/");
  }
};

// Pencarian
const searchQuery = ref("");
const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: "/searching", query: { q: searchQuery.value } });
  }
};
</script>

<template>
  <header class="bg-white shadow-sm border-b border-gray-200 relative z-50">
    <div class="max-w-7xl mx-auto w-full px-4 md:px-6 lg:px-8 flex items-center justify-between py-3 gap-6 relative">
      <!-- Logo -->
      <RouterLink to="/dashboard" class="flex-shrink-0">
        <img src="/src/assets/logo.png" alt="Zenith Logo" class="h-12 w-auto" />
      </RouterLink>

      <!-- Pencarian -->
      <div class="flex-1 max-w-xl md:ml-12 mr-auto">
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            v-model="searchQuery"
            @keypress.enter.prevent="handleSearch"
            type="text"
            placeholder="Cari produk impianmu..."
            class="w-full pl-11 pr-4 py-2.5 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-full focus:outline-none focus:bg-white focus:ring-2 focus:ring-pink-100 focus:border-pink-500 transition-all duration-300 shadow-sm hover:shadow-md hover:bg-white" />
          <!-- Optional: Search Button inside right (hidden for cleaner look or keep it?) -->
          <!-- Let's keep it clean with just the icon on the left as per modern standards, or add a subtle arrow on right -->
        </div>
      </div>

      <!-- Desktop Lab Button -->
      <RouterLink
        v-if="!user || user.role !== 'admin'"
        to="/dashboard/manage/desktopLab"
        class="hidden md:inline-flex items-center px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-bold text-sm rounded-lg hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow-sm whitespace-nowrap ml-auto">
        <img :src="desktopIcon" alt="Desktop Lab" class="w-5 h-5 mr-2" />
        DESKTOP LAB
      </RouterLink>

      <!-- Keranjang & Akun -->
      <div class="flex items-center gap-6">
        <!-- Customer Service (Admin Only) -->
        <RouterLink
          v-if="user && user.role === 'admin'"
          to="/admin"
          class="text-gray-500 hover:text-pink-600 transition-colors relative group"
          title="Dashboard Admin">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
          </svg>
        </RouterLink>
        <RouterLink
          v-if="user && user.role === 'admin'"
          to="/admin"
          class="text-gray-500 hover:text-pink-600 transition-colors relative group hidden">
          <!-- Backup hidden link if needed -->
        </RouterLink>

        <!-- Store Icon (Seller Only) -->
        <!-- Ganti seluruh <RouterLink> dengan div dropdown berikut -->
        <div v-if="user && user.role === 'penjual'" class="relative group">
          <!-- Ikon Toko (bisa diklik atau hover) -->
          <button
            class="text-gray-500 hover:text-pink-600 transition-colors focus:outline-none"
            title="Kelola Toko"
            aria-haspopup="true"
            aria-expanded="false">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-7 w-7"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <div
            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out origin-top-right">
            <RouterLink
              to="/toko"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">
              Kelola Produk
            </RouterLink>
            <RouterLink
              to="/pesanan"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors">
              Kelola Pesanan
            </RouterLink>
          </div>
        </div>

        <!-- Cart (Hidden for Admin) -->
        <RouterLink
          v-if="!user || user.role !== 'admin'"
          to="/cart"
          class="text-gray-500 hover:text-pink-600 transition-colors relative">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <!-- Badge Jumlah Item -->
          <span
            v-if="totalItems > 0"
            class="absolute -top-1 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center border-2 border-white">
            {{ totalItems }}
          </span>
        </RouterLink>

        <!-- Akun Dropdown -->
        <div v-if="isLoggedIn" class="relative" ref="profileDropdownRef">
          <button @click="isProfileOpen = !isProfileOpen" class="flex items-center gap-2 focus:outline-none">
            <div
              class="w-9 h-9 rounded-full bg-pink-50 flex items-center justify-center border border-pink-100 text-pink-600 overflow-hidden">
              <img
                v-if="user && user.profile_photo"
                :src="`http://127.0.0.1:8000/storage/${user.profile_photo}`"
                alt="Profile"
                class="w-full h-full object-cover" />
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
          </button>

          <!-- Dropdown Menu -->
          <div
            v-if="isProfileOpen"
            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50 transform origin-top-right transition-all">
            <div class="px-4 py-2 border-b border-gray-100 mb-1">
              <p class="text-sm font-semibold text-gray-900 truncate">
                {{ user?.name || "User" }}
              </p>
              <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
            </div>

            <RouterLink
              v-if="user && user.role !== 'admin'"
              to="/profile"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors"
              @click="isProfileOpen = false">
              Profile
            </RouterLink>

            <RouterLink
              v-if="user && user.role !== 'admin'"
              to="/riwayat"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors"
              @click="isProfileOpen = false">
              Riwayat
            </RouterLink>

            <button
              @click="confirmLogout"
              class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
              Logout
            </button>
          </div>
        </div>

        <div v-else class="flex gap-2">
          <RouterLink to="/login" class="text-sm font-medium text-gray-600 hover:text-pink-600 px-3 py-2">
            Login
          </RouterLink>
          <RouterLink
            to="/register"
            class="text-sm font-medium bg-pink-600 text-white px-4 py-2 rounded-full hover:bg-pink-700 transition shadow-sm">
            Register
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Logout Modal -->
    <div
      v-if="showLogoutModal"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 transform transition-all scale-100">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Logout</h3>
          <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin keluar dari akun Anda?</p>
          <div class="flex gap-3">
            <button
              @click="showLogoutModal = false"
              class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
              Batal
            </button>
            <button
              @click="logout"
              class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
              Keluar
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
