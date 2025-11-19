<template>
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Daftar Produk Kami</h1>

    <div v-if="loading" class="text-center text-gray-600 py-10">
      Memuat produk...
    </div>

    <div v-else-if="error" class="text-center text-red-500 py-10">Terjadi kesalahan saat memuat produk: {{ error.message }}</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="product in products" :key="product.id_produk" class="bg-white rounded-lg shadow-md overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-lg">
        <div class="h-48 w-full overflow-hidden bg-gray-100 flex items-center justify-center">
          <img :src="product.main_image_url" :alt="product.nama_produk" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" />
        </div>

        <div class="p-6">
          <h2 class="text-xl font-semibold mb-2 text-gray-800">{{ product.nama_produk }}</h2>
          <p class="text-gray-600 mb-4 truncate">{{ product.deskripsi }}</p>

          <div v-if="product.variant && product.variant.length > 0">
            <p class="text-sm font-medium text-gray-700 mb-2">Varian:</p>
            <ul class="list-disc list-inside text-gray-500 text-sm">
              <li v-for="variant in product.variant" :key="variant.id_variant">
                {{ variant.nama_variant }} -
                <span class="font-bold text-green-600"> Rp {{ variant.harga.toLocaleString("id-ID") }} </span>
              </li>
            </ul>
          </div>
          <div v-else>
            <p class="text-sm text-gray-500">Belum ada varian produk.</p>
          </div>

          <router-link :to="{ name: 'product-detail', params: { id: product.id_produk } }" class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-200">
            Lihat Detail
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

// Fungsi helper untuk mendapatkan URL gambar dari varian pertama
const getImageUrl = (product) => {
  if (product.variant && product.variant.length > 0) {
    const imagePath = product.variant[0].gambar_varian;
    if (imagePath) {
      // Menggunakan URL lengkap + /storage/
      return `http://127.0.0.1:8000/storage/${imagePath}`;
    }
  }
  // Gambar placeholder jika tidak ada
  return "https://via.placeholder.com/400x300/CCCCCC?text=No+Image";
};

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
        const response = await axios.get("http://127.0.0.1:8000/api/products"); // 1. Ambil data produk
        let fetchedProducts = response.data.data;

        // 2. Tambahkan URL Gambar Utama ke setiap objek produk
        fetchedProducts = fetchedProducts.map((product) => {
          product.main_image_url = getImageUrl(product);
          return product;
        });

        this.products = fetchedProducts;
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
