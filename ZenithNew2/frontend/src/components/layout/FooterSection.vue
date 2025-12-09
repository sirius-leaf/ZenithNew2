<script setup>
const img = "/src/assets/logo.png";
</script>

<template>
  <footer class="bg-white border-t border-gray-200 mt-12">
    <div class="max-w-7xl mx-auto w-full px-4 md:px-6 lg:px-8 py-8">
      <div class="flex flex-col md:flex-row justify-between items-center gap-6">
        <!-- Brand & Copyright -->
        <div class="text-center md:text-left">
          <img :src="img" alt="Zenith" class="h-8 mb-2 mx-auto md:mx-0" />
          <p class="text-xs text-gray-500">
            Jl. Slamet Riyadi, Surakarta, Jawa Tengah, Indonesia
          </p>
          <p class="text-xs text-gray-400 mt-1">
            Copyright ©2025 ZENITH, All Rights Reserved
          </p>
        </div>

        <!-- Links (Horizontal) -->
        <div
          class="flex flex-wrap justify-center gap-6 text-sm font-medium text-gray-600"
        >
          <a href="#" class="hover:text-pink-600 transition">Home</a>
          <a href="#" class="hover:text-pink-600 transition">Category</a>
          <a href="#" class="hover:text-pink-600 transition">About Us</a>
          <a href="#" class="hover:text-pink-600 transition">Testimoni</a>
          <span class="text-gray-300">|</span>
          <a href="#" class="hover:text-pink-600 transition">Instagram</a>
          <a href="#" class="hover:text-pink-600 transition">Twitter</a>
        </div>
      </div>
    </div>
  </footer>
</template>
