<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

// Import komponen
import Navbar from "@/components/Navbar.vue";
import Footer from "@/components/Footer.vue";

// State
const products = ref([]);
const isLoading = ref(true);
const error = ref(null);

// Mock Data (ganti dengan fetch API nanti)
const mockProducts = [
  {
    id_produk: 1,
    nama_produk: "Headphone Rapa",
    merek: "RAPA AIR",
    deskripsi: "Wireless earphone dengan noise cancellation.",
    harga: 4599000,
    rating: 4.8,
    image: "https://via.placeholder.com/300x300?text=Headphone+Rapa"
  },
  {
    id_produk: 2,
    nama_produk: "Samsung Galaxy A55",
    merek: "Samsung Indonesia",
    deskripsi: "Smartphone terbaru dengan kamera 64MP.",
    harga: 4599000,
    rating: 4.8,
    image: "https://via.placeholder.com/300x300?text=Galaxy+A55"
  },
  {
    id_produk: 3,
    nama_produk: "Pineapple Macbook Pro 2022 M1 / 512 GB",
    merek: "Apple",
    deskripsi: "Laptop premium dengan chip M1.",
    harga: 9599000,
    rating: 4.9,
    image: "https://via.placeholder.com/300x300?text=MacBook+Pro"
  },
  {
    id_produk: 4,
    nama_produk: "Monitor Gaming 24\"",
    merek: "Asus",
    deskripsi: "Refresh rate 144Hz, response time 1ms.",
    harga: 3299000,
    rating: 4.7,
    image: "https://via.placeholder.com/300x300?text=Gaming+Monitor"
  }
];

// Simulasi fetch data (nanti ganti dengan API Laravel)
const fetchProducts = async () => {
  try {
    // Ganti ini dengan axios/fetch ke API Laravel Anda
    // contoh: const res = await fetch('/api/products');
    // const data = await res.json();
    // products.value = data;

    // Untuk sekarang, gunakan mock data
    await new Promise(resolve => setTimeout(resolve, 800)); // simulasi delay
    products.value = mockProducts;
  } catch (err) {
    error.value = "Gagal memuat produk. Silakan coba lagi.";
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchProducts();
});

// Fungsi format harga
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount);
};

// Fungsi untuk scroll ke elemen
const scrollToSection = (id) => {
  const el = document.getElementById(id);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<template>
  <div class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <Navbar />

    <!-- Banner Utama -->
    <section class="relative overflow-hidden bg-pink-50 py-10 px-6 md:px-10">
      <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-6">
        <div class="md:w-1/2">
          <img src="https://via.placeholder.com/600x300?text=RAIH+PUNCAK+TEKNOLOGI+BERSAMA+KAMI" alt="Banner" class="rounded-lg w-full shadow-md" />
        </div>
        <div class="md:w-1/2 space-y-4">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">RAIH PUNCAK TEKNOLOGI BERSAMA KAMI</h1>
          <p class="text-gray-600">Temukan produk teknologi terbaik dengan diskon hingga 50%.</p>
          <button @click="scrollToSection('flash-sale')" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition">
            Lihat Promo
          </button>
        </div>
      </div>
    </section>

    <!-- Tag Populer -->
    <section class="py-8 px-6 md:px-10">
      <h2 class="text-xl font-semibold mb-6 text-gray-800">Tag Populer</h2>
      <div class="flex flex-wrap gap-4">
        <div v-for="(tag, index) in ['Handphones', 'Laptops', 'CPU', 'Monitors']" :key="index" class="flex flex-col items-center p-4 bg-white rounded-lg shadow cursor-pointer hover:shadow-md transition">
          <img :src="`https://via.placeholder.com/100x100?text=${tag}`" :alt="tag" class="w-16 h-16 mb-2" />
          <span class="text-sm font-medium">{{ tag }}</span>
        </div>
      </div>
    </section>

    <!-- Flash Sale -->
    <section id="flash-sale" class="py-8 px-6 md:px-10 bg-white">
      <h2 class="text-xl font-semibold mb-6 text-gray-800">Flash Sale</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-if="isLoading" class="col-span-full flex justify-center py-10">
          <svg class="animate-spin h-8 w-8 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.644z"></path>
          </svg>
        </div>
        <div v-else-if="error" class="col-span-full text-red-500 text-center py-10">
          {{ error }}
        </div>
        <div v-else-if="products.length === 0" class="col-span-full text-center py-10 text-gray-500">
          Maaf produk sedang kosong
        </div>
        <div v-else v-for="product in products.slice(0, 4)" :key="product.id_produk" class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
          <img :src="product.image" :alt="product.nama_produk" class="w-full h-48 object-cover" />
          <div class="p-4">
            <h3 class="font-medium text-gray-800 line-clamp-2">{{ product.nama_produk }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ product.merek }}</p>
            <div class="flex items-center mt-2">
              <span class="text-yellow-500 text-sm">★ {{ product.rating }}</span>
            </div>
            <p class="mt-2 font-bold text-pink-600">{{ formatCurrency(product.harga) }}</p>
            <button class="mt-3 w-full bg-pink-600 text-white py-1 rounded text-sm hover:bg-pink-700 transition">
              Beli Sekarang
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Rekomendasi -->
    <section class="py-8 px-6 md:px-10 bg-gray-50">
      <div class="max-w-6xl mx-auto">
        <h2 class="text-xl font-semibold mb-6 text-center text-gray-800">REKOMENDASI</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-if="isLoading" class="col-span-full flex justify-center py-10">
            <svg class="animate-spin h-8 w-8 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.644z"></path>
            </svg>
          </div>
          <div v-else-if="error" class="col-span-full text-red-500 text-center py-10">
            {{ error }}
          </div>
          <div v-else-if="products.length === 0" class="col-span-full text-center py-10 text-gray-500">
            Maaf produk sedang kosong
          </div>
          <div v-else v-for="product in products.slice(0, 4)" :key="product.id_produk" class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
            <img :src="product.image" :alt="product.nama_produk" class="w-full h-48 object-cover" />
            <div class="p-4">
              <h3 class="font-medium text-gray-800 line-clamp-2">{{ product.nama_produk }}</h3>
              <p class="text-sm text-gray-500 mt-1">{{ product.merek }}</p>
              <div class="flex items-center mt-2">
                <span class="text-yellow-500 text-sm">★ {{ product.rating }}</span>
              </div>
              <p class="mt-2 font-bold text-pink-600">{{ formatCurrency(product.harga) }}</p>
              <button class="mt-3 w-full bg-pink-600 text-white py-1 rounded text-sm hover:bg-pink-700 transition">
                Beli Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<style scoped>
/* Tambahkan jika perlu styling tambahan */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>