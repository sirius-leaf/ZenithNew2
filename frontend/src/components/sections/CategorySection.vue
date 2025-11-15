<!-- src/components/sections/CategorySection.vue -->
<script setup>
import { RouterLink } from 'vue-router'

const categories = [
  { title: 'LAPTOP', file: 'mac.png', category: 'Computing' },
  { title: 'HANDPHONE', file: 'ip.png', category: 'Gadget' },
  { title: 'EARBUDS', file: 'ear.png', category: 'Audio' },
  { title: 'GPU', file: 'gpu.png', category: 'Hardware' },
  { title: 'SMART TV', file: 'tv.png', category: 'TV & Home' },
  { title: 'CHARGER', file: 'charger.png', category: 'Accessories' },
]

const toSlug = (text) =>
  text
    .toLowerCase()
    .replace(/\//g, '-')
    .replace(/\s+/g, '-')
    .replace(/[^\w-]/g, '')

const getImage = (file) =>
  new URL(`../../assets/${file}`, import.meta.url).href
</script>

<template>
  <section class="py-16 px-4 sm:px-6 md:px-8 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-12">
        <span class="inline-block px-4 py-1 mb-4 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
          Explore Categories
        </span>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
          Products Categories
        </h2>
        <p class="mt-3 text-gray-600 max-w-2xl mx-auto">
          Temukan produk teknologi terbaik sesuai kebutuhanmu — dari komponen PC hingga gadget sehari-hari.
        </p>
      </div>

      <!-- Category Grid -->
      <div
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6"
        data-aos="fade-up"
        data-aos-duration="1000"
      >
        <RouterLink
          v-for="category in categories"
          :key="category.title"
          :to="`/categories/${toSlug(category.category)}`"
          class="group flex flex-col items-center justify-center p-5 rounded-2xl bg-white border border-gray-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-pink-300 hover:bg-pink-50"
        >
          <!-- Icon -->
          <div
            class="w-16 h-16 mb-4 flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl transition-colors duration-300 group-hover:from-blue-100 group-hover:to-indigo-100"
          >
            <img
              :src="getImage(category.file)"
              :alt="category.title"
              class="w-10 h-10 object-contain grayscale group-hover:grayscale-0 transition-all duration-300"
            />
          </div>

          <!-- Text -->
          <span class="text-xs font-medium text-gray-500 mb-1 line-clamp-1">
            {{ category.category }}
          </span>
          <h3 class="text-sm font-semibold text-gray-800 text-center line-clamp-2 group-hover:text-pink-700 transition-colors">
            {{ category.title }}
          </h3>
        </RouterLink>
      </div>

      <!-- Optional: View All Button -->
      <div class="text-center mt-10">
        <RouterLink
          to="/categories"
          class="inline-flex items-center px-6 py-3 bg-pink-600 text-white font-medium rounded-xl hover:bg-pink-400 transition-colors shadow-sm hover:shadow-md"
        >
          <span>Lihat Semua Kategori</span>
          <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
</style>