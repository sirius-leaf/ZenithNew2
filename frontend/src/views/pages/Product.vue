<template>
  <main class="px-4 py-6 md:px-6 lg:px-8 max-w-7xl mx-auto w-full">
    <h1 class="text-3xl font-bold mb-6 text-center">Daftar Produk Kami</h1>

    <!-- Loading State -->
    <div v-if="loading" class="text-center text-gray-600 py-10">
      Memuat produk...
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center text-red-500 py-10">
      Terjadi kesalahan saat memuat produk: {{ error.message }}
    </div>

    <!-- Main Content -->
    <div v-else class="flex flex-col md:flex-row gap-8">
      
      <!-- Sidebar Filter -->
      <aside class="w-full md:w-64 flex-shrink-0">
        <ProductSidebar 
          :initial-category="selectedCategory"
          @category-selected="applyCategoryFilter"
        />
      </aside>

      <!-- Product List Area -->
      <div class="flex-grow">
        
        <!-- Header Info & Sort -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
          <p class="text-gray-600 text-sm">
            Menampilkan <span class="font-bold text-gray-900">{{ filteredProducts.length > 0 ? 1 : 0 }} - {{ filteredProducts.length }}</span> barang dari total untuk "<span class="font-bold text-gray-900">{{ selectedCategory || 'Semua Produk' }}</span>"
          </p>

          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Urutkan:</label>
            <select
              v-model="sortByPrice"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 bg-white"
            >
              <option value="">Default</option>
              <option value="asc">Harga Terendah</option>
              <option value="desc">Harga Tertinggi</option>
            </select>
          </div>
        </div>

        <!-- Product Grid -->
        <div v-if="filteredProducts.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div
            v-for="product in filteredProducts"
            :key="product.id_produk"
            class="bg-gray-50 rounded-lg p-3 shadow-sm hover:shadow-md transition cursor-pointer group"
            @click="$router.push({ name: 'product-detail', params: { id: product.id_produk } })"
          >
            <!-- Gambar Produk -->
            <div class="relative overflow-hidden rounded-md mb-3">
              <img
                :src="product.main_image_url"
                :alt="product.nama_produk"
                class="w-full h-32 object-cover transform group-hover:scale-110 transition-transform duration-500"
                @error="e => e.target.src = 'https://via.placeholder.com/200/FFFFFF/000000?text=No+Image'"
              />
            </div>
            
            <!-- Nama Produk -->
            <h4 class="font-semibold text-gray-800 text-sm mb-1 line-clamp-1 group-hover:text-pink-600 transition-colors">{{ product.nama_produk }}</h4>
            
            <!-- Harga -->
            <p class="text-pink-600 font-bold mb-1">
              Rp {{ (product.variant?.[0]?.harga ?? 0).toLocaleString('id-ID') }}
            </p>
            
            <!-- Rating -->
            <div class="flex items-center mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z" />
              </svg>
              <span class="text-xs text-gray-600 ml-1">4.8</span>
            </div>
            
            <!-- Brand -->
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2z" clip-rule="evenodd" />
              </svg>
              <span class="text-xs text-gray-600 ml-1">{{ product.merek || 'Zenith Store' }}</span>
            </div>
          </div>
        </div>

        <!-- Pesan Tidak Ditemukan -->
        <div v-else class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300">
          <p class="text-xl font-medium text-gray-600">Maaf, produk tidak ditemukan.</p>
          <button @click="selectedCategory = null" class="mt-4 text-pink-600 hover:underline font-medium">
            Tampilkan Semua Produk
          </button>
        </div>

      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ProductSidebar from '@/components/layout/ProductSidebar.vue';

// State
const products = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedCategory = ref(null);
const sortByPrice = ref('');

// Fungsi helper untuk mendapatkan URL gambar dari varian pertama
const getImageUrl = (product) => {
  if (product.variant && product.variant.length > 0) {
    const imagePath = product.variant[0].gambar_varian;
    if (imagePath) {
      if (imagePath.startsWith('http')) return imagePath;
      const cleanPath = imagePath.replace(/^(\.\.\/)+/, '');
      return `http://127.0.0.1:8000/${cleanPath}`;
    }
  }
  return "https://via.placeholder.com/200/FFFFFF/000000?text=No+Image";
};

// Fetch Products
onMounted(async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/products");
    let fetchedProducts = response.data.data;

    // Tambahkan URL gambar ke setiap produk
    fetchedProducts = fetchedProducts.map((product) => {
      product.main_image_url = getImageUrl(product);
      return product;
    });

    products.value = fetchedProducts;
  } catch (err) {
    error.value = err;
    console.error("Error fetching products:", err);
  } finally {
    loading.value = false;
  }
});

// Computed: Produk yang sudah difilter dan diurutkan
const filteredProducts = computed(() => {
  let result = [...products.value];

  // Filter berdasarkan kategori
  if (selectedCategory.value) {
    result = result.filter(p => p.nama_produk.toLowerCase().includes(selectedCategory.value.toLowerCase()));
  }

  // Urutkan berdasarkan harga
  if (sortByPrice.value === 'asc') {
    result.sort((a, b) => (a.variant?.[0]?.harga ?? 0) - (b.variant?.[0]?.harga ?? 0));
  } else if (sortByPrice.value === 'desc') {
    result.sort((a, b) => (b.variant?.[0]?.harga ?? 0) - (a.variant?.[0]?.harga ?? 0));
  }

  return result;
});

// Handler untuk filter kategori dari sidebar
const applyCategoryFilter = (categoryName) => {
  selectedCategory.value = categoryName;
};
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
::-webkit-scrollbar {
  display: none;
}
html {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;     /* Firefox */
}

.bg-gray-50:hover {
  background-color: #f9fafb;
}
</style>