<script setup>
import { ref } from "vue";
import { RouterLink } from "vue-router";

const isOpen = ref(false); // untuk toggle menu di mobile
</script>

<template>
  <header class="bg-white shadow px-6 py-4 md:px-10 md:py-6">
    <div class="flex justify-between items-center">
      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2">
        <img src="/src/assets/logo.png" alt="logo" class="h-10 w-auto" />
      </RouterLink>

      <!-- Menu desktop -->
      <nav class="hidden md:flex gap-6 text-gray-700">
        <RouterLink to="/product" class="hover:text-blue-500"
          >Product</RouterLink
        >
        <RouterLink to="/category" class="hover:text-blue-500"
          >Category</RouterLink
        >
        <RouterLink to="/about" class="hover:text-blue-500"
          >About Us</RouterLink
        >
        <RouterLink to="/testimonial" class="hover:text-blue-500"
          >Testimoni</RouterLink
        >
      </nav>

      <!-- Login/Register desktop -->
      <div class="hidden md:flex gap-4">
        <RouterLink
          to="/login"
          class="text-pink-600 px-4 py-2 rounded-lg hover:bg-pink-100"
          >Login</RouterLink
        >
        <RouterLink
          to="/register"
          class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700"
        >
          Register
        </RouterLink>
      </div>

      <!-- Tombol Hamburger (mobile) -->
      <button
        @click="isOpen = !isOpen"
        class="md:hidden text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 rounded-lg"
      >
        <svg
          v-if="!isOpen"
          xmlns="http://www.w3.org/2000/svg"
          class="h-7 w-7"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          class="h-7 w-7"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>

    <!-- Menu mobile -->
    <transition name="slide-fade">
      <div
        v-if="isOpen"
        class="flex flex-col items-center gap-4 mt-4 text-gray-700 md:hidden"
      >
        <RouterLink
          to="/product"
          class="hover:text-blue-500"
          @click="isOpen = false"
          >Product</RouterLink
        >
        <RouterLink
          to="/category"
          class="hover:text-blue-500"
          @click="isOpen = false"
          >Category</RouterLink
        >
        <RouterLink
          to="/about"
          class="hover:text-blue-500"
          @click="isOpen = false"
          >About Us</RouterLink
        >
        <RouterLink
          to="/testimonial"
          class="hover:text-blue-500"
          @click="isOpen = false"
          >Testimoni</RouterLink
        >

        <div class="flex gap-4 mt-2">
          <RouterLink
            to="/login"
            class="px-4 py-2 text-pink-600 hover:bg-pink-100"
            >Login</RouterLink
          >
          <RouterLink
            to="/register"
            class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700"
          >
            Register
          </RouterLink>
        </div>
      </div>
    </transition>
  </header>
</template>

<style scoped>
/* animasi menu mobile */
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
