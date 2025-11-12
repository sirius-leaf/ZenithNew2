<template>
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Daftar Produk Kami</h1>

    <div v-if="loading" class="text-center text-gray-600">
      Memuat produk...
    </div>

    <div v-else-if="error" class="text-center text-red-500">
      Terjadi kesalahan saat memuat produk: {{ error.message }}
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="product in products" :key="product.id_produk"
           class="bg-white rounded-lg shadow-md overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-lg">
        <div class="p-6">
          <h2 class="text-xl font-semibold mb-2 text-gray-800">{{ product.nama_produk }}</h2>
          <p class="text-gray-600 mb-4">{{ product.deskripsi }}</p>
          
          <div v-if="product.variant && product.variant.length > 0">
            <p class="text-sm font-medium text-gray-700 mb-2">Varian:</p>
            <ul class="list-disc list-inside text-gray-500 text-sm">
              <li v-for="variant in product.variant" :key="variant.id_variant">
                {{ variant.nama_variant }} - Rp {{ variant.harga.toLocaleString('id-ID') }}
              </li>
            </ul>
          </div>
          <div v-else>
            <p class="text-sm text-gray-500">Belum ada varian produk.</p>
          </div>

          <router-link :to="{ name: 'product-detail', params: { id: product.id_produk } }"
                       class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-200">
            Lihat Detail
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      products: [],
      loading: true,
      error: null,
    };
  },
  created() {
    this.fetchProducts();
  },
  methods: {
    async fetchProducts() {
      try {
        // **INI BAGIAN YANG DIUBAH:**
        // Menggunakan URL lengkap ke API Laravel Anda
        const response = await axios.get('http://127.0.0.1:8000/api/products');
        
        // API Anda menggunakan paginate(), jadi data ada di 'response.data.data'
        this.products = response.data.data; 
      } catch (error) {
        this.error = error;
        console.error("Error fetching products:", error);
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