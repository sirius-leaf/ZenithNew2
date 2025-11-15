<template>
  <div class="container mx-auto p-4">
    <router-link to="/" class="inline-block mb-4 text-indigo-600 hover:text-indigo-800 transition duration-200">
      &larr; Kembali ke Daftar Produk
    </router-link>

    <div v-if="loading" class="text-center text-gray-600">
      Memuat detail produk...
    </div>

    <div v-else-if="error" class="text-center text-red-500">
      Terjadi kesalahan saat memuat detail produk: {{ error.message }}
    </div>

    <div v-else-if="product" class="bg-white rounded-lg shadow-lg p-8">
      <h1 class="text-4xl font-bold mb-4 text-gray-900">{{ product.nama_produk }}</h1>
      <p class="text-lg text-gray-700 mb-6">{{ product.deskripsi }}</p>

      <hr class="my-6 border-gray-300">

      <h2 class="text-2xl font-semibold mb-4 text-gray-800">Varian Produk:</h2>
      <div v-if="product.variant && product.variant.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="variant in product.variant" :key="variant.id_variant"
             class="bg-gray-50 p-4 rounded-md shadow-sm border border-gray-200">
          <h3 class="text-xl font-medium mb-1 text-gray-800">{{ variant.nama_variant }}</h3>
          <p class="text-gray-600 mb-2">
            Harga: <span class="font-bold text-green-700">Rp {{ variant.harga.toLocaleString('id-ID') }}</span>
          </p>
          <p class="text-sm text-gray-500">Stok: {{ variant.stok }}</p>
        </div>
      </div>
      <div v-else>
        <p class="text-gray-600">Belum ada varian untuk produk ini.</p>
      </div>
    </div>
    <div v-else class="text-center text-gray-600">
      Produk tidak ditemukan.
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      product: null,
      loading: true,
      error: null,
    };
  },
  created() {
    this.fetchProductDetail();
  },
  methods: {
    async fetchProductDetail() {
      try {
        const productId = this.$route.params.id; // Mengambil ID dari URL
        
        // **INI BAGIAN YANG DIUBAH:**
        // Menggunakan URL lengkap ke API Laravel Anda
        const response = await axios.get(`http://127.0.0.1:8000/api/products/${productId}`);
        
        // API 'show' mengembalikan satu objek, jadi data ada di 'response.data'
        this.product = response.data;
      } catch (error) {
        this.error = error;
        console.error("Error fetching product detail:", error);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Styling kustom jika diperlukan */
</style>