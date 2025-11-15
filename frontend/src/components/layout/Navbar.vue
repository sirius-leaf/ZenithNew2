<script setup>
import { ref, watch } from "vue";
import { RouterLink, useRouter, useRoute } from "vue-router";

const isOpen = ref(false); // untuk toggle menu di mobile (opsional)
const isLoggedIn = ref(false);
const router = useRouter();
const route = useRoute();

// Cek status login saat komponen dimuat
const checkAuthStatus = () => {
  const token = localStorage.getItem('authToken');
  isLoggedIn.value = !!token;
};
checkAuthStatus();

// Pantau perubahan rute
watch(() => route.path, () => {
  checkAuthStatus();
});

// Fungsi logout
const logout = () => {
  localStorage.removeItem('authToken');
  isLoggedIn.value = false;
  isOpen.value = false;
  router.push('/');
};

// State untuk input pencarian
const searchQuery = ref('');

// Fungsi untuk handle submit pencarian
const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/product', query: { q: searchQuery.value } });
  }
};
</script>

<template>
  <header class="bg-white shadow">
    <!-- Baris Atas: Logo, Search, Cart & Profile -->
    <div class="flex justify-between items-center px-6 py-3 md:px-10 md:py-4 bg-gray-50 border-b">
      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2">
        <img src="/src/assets/logo.png" alt="Zenith Logo" class="h-8 w-auto" />
      </RouterLink>

      <!-- Pencarian (Simple) -->
      <div class="flex-1 mx-6 max-w-xl">
        <div class="relative">
          <input
            v-model="searchQuery"
            @keypress.enter.prevent="handleSearch"
            type="text"
            placeholder="Cari produk..."
            class="w-full px-4 py-2.5 pl-10 pr-4 text-sm text-gray-700 bg-transparent border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
          />
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>
      </div>

      <!-- Keranjang & Profil -->
      <div class="flex items-center gap-4">
        <RouterLink to="/cart" class="relative">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10v8a2 2 0 002 2H5a2 2 0 002-2v-8zm5-1a2 2 0 110-4 2 2 0 010 4z" />
          </svg>
          <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
        </RouterLink>

        <div v-if="isLoggedIn" class="flex items-center gap-2">
          <img src="https://ui-avatars.com/api/?name=Raditya+Ba&background=0D8ABC&color=fff" alt="Profile" class="h-8 w-8 rounded-full" />
          <span class="text-sm font-medium text-gray-700">raditya.ba</span>
          <button @click="logout" class="text-xs text-red-500 hover:text-red-700">Logout</button>
        </div>

        <div v-else class="flex gap-2">
          <RouterLink to="/login" class="text-sm text-pink-600 hover:text-pink-800">Login</RouterLink>
          <RouterLink to="/register" class="text-sm bg-pink-600 text-white px-3 py-1 rounded-md hover:bg-pink-700">Register</RouterLink>
        </div>
      </div>
    </div>

    <!-- Baris Bawah: Menu Navigasi Utama -->
    <div class="hidden md:flex justify-center bg-white px-6 py-2">
      <nav class="flex gap-8 text-gray-700">
        <RouterLink to="/product" class="hover:text-blue-500">Product</RouterLink>
        <RouterLink to="/category" class="hover:text-blue-500">Category</RouterLink>
        <RouterLink to="/about" class="hover:text-blue-500">About Us</RouterLink>
        <RouterLink to="/testimonial" class="hover:text-blue-500">Testimoni</RouterLink>
      </nav>
    </div>

    <!-- Tombol Mobile Menu (Opsional) -->
    <button
      @click="isOpen = !isOpen"
      class="md:hidden p-4 text-gray-700 focus:outline-none"
    >
      <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Mobile Menu (Opsional) -->
    <transition name="slide-fade">
      <div
        v-if="isOpen"
        class="md:hidden flex flex-col items-start gap-4 px-6 py-4 bg-white border-t"
      >
        <RouterLink to="/product" class="hover:text-blue-500" @click="isOpen = false">Product</RouterLink>
        <RouterLink to="/category" class="hover:text-blue-500" @click="isOpen = false">Category</RouterLink>
        <RouterLink to="/about" class="hover:text-blue-500" @click="isOpen = false">About Us</RouterLink>
        <RouterLink to="/testimonial" class="hover:text-blue-500" @click="isOpen = false">Testimoni</RouterLink>

        <div v-if="!isLoggedIn" class="flex gap-4 mt-4">
          <RouterLink to="/login" class="text-pink-600" @click="isOpen = false">Login</RouterLink>
          <RouterLink to="/register" class="bg-pink-600 text-white px-3 py-1 rounded-md" @click="isOpen = false">Register</RouterLink>
        </div>

        <div v-if="isLoggedIn" class="w-full mt-4">
          <button
            @click="logout"
            class="w-full bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600"
          >
            Logout
          </button>
        </div>
      </div>
    </transition>
  </header>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>