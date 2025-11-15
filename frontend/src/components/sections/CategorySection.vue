<script setup>
import { RouterLink } from "vue-router"; // ✅ pastikan RouterLink diimpor

const categories = [
  {
    title: "LAPTOP",
    file: "mac.png",
    category: "Computing/Technology",
  },
  {
    title: "HANDPHONE",
    file: "ip.png",
    category: "Handphone/Gadget",
  },
  {
    title: "EARBUDS",
    file: "ear.png",
    category: "Accessories",
  },
  {
    title: "GPU",
    file: "gpu.png",
    category: "Hardware",
  },
  {
    title: "SMART TV",
    file: "tv.png",
    category: "Home Electronics",
  },
  {
    title: "CHARGER",
    file: "charger.png",
    category: "Charger dan Adaptor Laptop",
  },
];

// 👉 Fungsi untuk ubah string jadi slug aman untuk URL
const toSlug = (text) => {
  return text
    .toLowerCase()
    .replace(/\//g, "-") // ganti "/" jadi "-"
    .replace(/\s+/g, "-") // ganti spasi jadi "-"
    .replace(/[^\w-]+/g, ""); // hapus karakter aneh
};

// Helper untuk ambil gambar
const getImage = (file) => new URL(`../assets/${file}`, import.meta.url).href;
</script>

<template>
  <section class="py-16 px-6">
    <h3 class="text-3xl font-bold text-center mb-10">Products Categories</h3>

    <div
      data-aos-duration="1300"
      data-aos="fade-up"
      class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-8 reveal"
    >
      <!-- ✅ Gunakan RouterLink di tiap kategori -->
      <RouterLink
        v-for="category in categories"
        :key="category.title"
        :to="`/categories/${toSlug(category.category)}`"
        class="bg-blue-900 text-white rounded-2xl p-4 flex flex-col items-center text-center h-full w-full max-w-[380px] justify-between transition-transform duration-300 hover:scale-105 hover:bg-blue-800"
      >
        <span class="text-sm tracking-wide text-pink-300 mb-2">
          {{ category.category }}
        </span>
        <img
          :src="getImage(category.file)"
          :alt="category.title"
          class="object-contain mb-2"
        />
        <p class="text-lg font-semibold">{{ category.title }}</p>
      </RouterLink>
    </div>
  </section>
</template>

<style scoped>
img {
  height: 120px;
}
</style>
