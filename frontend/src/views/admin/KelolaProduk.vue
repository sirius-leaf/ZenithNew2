<!-- src/components/admin/KelolaProduk.vue -->
<template>
  <div
    class="bg-white rounded-xl shadow-md p-6 animate-fade-in max-w-5xl mx-auto mt-8"
  >
    <h1 class="text-xl font-bold text-blue-900 mb-4">Kelola Produk Seller</h1>

    <!-- Search Bar + Kategori Dropdown -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
      <div class="flex-1 relative">
        <svg
          class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-blue-900"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari Produk, Toko..."
          class="w-full pl-10 pr-4 py-2 bg-pink-100 border-none rounded-lg text-blue-900 outline-none"
        />
      </div>
      <div class="md:w-48">
        <select
          v-model="selectedCategory"
          class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-pink-500"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat" :value="cat">
            {{ cat }}
          </option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-8 text-center">
      <div
        class="inline-block w-8 h-8 border-4 border-pink-300 border-t-pink-600 rounded-full animate-spin"
      ></div>
      <p class="mt-3 text-gray-600">Memuat produk...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-pink-500 text-white">
            <th class="py-2 px-4 text-left">Nama Toko</th>
            <th class="py-2 px-4 text-left">Produk</th>
            <th class="py-2 px-4 text-left">Kategori</th>
            <th class="py-2 px-4 text-center">Opsi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="product in filteredProducts"
            :key="product.id"
            class="border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200"
          >
            <td class="py-2 px-4">{{ product.storeName }}</td>
            <td class="py-2 px-4">
              <div class="flex items-center gap-3">
                <img
                  v-if="product.image"
                  :src="getImageUrl(product.image)"
                  class="w-10 h-10 object-cover rounded"
                  alt="Product"
                />
                <div
                  v-else
                  class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-500"
                >
                  No Img
                </div>
                <span class="font-medium">{{ product.productName }}</span>
              </div>
            </td>
            <td class="py-2 px-4">{{ product.category }}</td>
            <td class="py-2 px-4 text-center">
              <button
                @click="deleteProduct(product.id)"
                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors border border-red-200 text-sm font-medium"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A1 1 0 0117.133 21H6.867A1 1 0 016 19.133L4.867 7H19zm-1 8.133L18 19H6l-1.133-4.133A1 1 0 015 14v-2a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1z"
                  />
                </svg>
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- No data -->
      <div
        v-if="!loading && filteredProducts.length === 0"
        class="py-8 text-center text-gray-500"
      >
        Tidak ada produk ditemukan.
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center mt-6 space-x-2">
      <button
        v-for="page in totalPages"
        :key="page"
        @click="currentPage = page"
        :class="[
          'px-3 py-1 rounded',
          currentPage === page
            ? 'bg-blue-900 text-white'
            : 'bg-blue-900/20 text-blue-900',
        ]"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const products = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const selectedCategory = ref("");
const currentPage = ref(1);
const itemsPerPage = 5;

// Extract unique categories from products
const categories = computed(() => {
  const cats = new Set(products.value.map((p) => p.category).filter(Boolean));
  return Array.from(cats);
});

// Base filtered list (shared logic)
const allFilteredProducts = computed(() => {
  let result = products.value.filter(
    (p) =>
      p.storeName.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.productName.toLowerCase().includes(searchQuery.value.toLowerCase())
  );

  if (selectedCategory.value) {
    result = result.filter((p) => p.category.includes(selectedCategory.value));
  }

  return result;
});

// Paginated list
const filteredProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return allFilteredProducts.value.slice(start, start + itemsPerPage);
});

// Total pages based on filtered list
const totalPages = computed(() => {
  return Math.ceil(allFilteredProducts.value.length / itemsPerPage);
});

// Reset page when filters change
watch([searchQuery, selectedCategory], () => {
  currentPage.value = 1;
});

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/manage/all-products");
    if (response.data.success) {
      products.value = response.data.data;
    }
  } catch (error) {
    console.error("Gagal memuat produk:", error);
    // alert("Gagal memuat data produk.");
  } finally {
    loading.value = false;
  }
};

const deleteProduct = async (id) => {
  if (
    !confirm(
      "Apakah Anda yakin ingin menghapus produk ini? Produk akan hilang dari pencarian dan toko seller."
    )
  )
    return;

  try {
    await axios.delete(`/manage/product/${id}`);
    alert("Produk berhasil dihapus.");
    fetchProducts(); // Refresh list
  } catch (error) {
    console.error("Gagal menghapus produk:", error);
    alert("Gagal menghapus produk. Silakan coba lagi.");
  }
};

const getImageUrl = (path) => {
  if (!path) return "";
  if (path.startsWith("http")) return path;
  return `http://127.0.0.1:8000/storage/${path}`;
};

onMounted(() => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  if (localStorage.getItem("userRole") !== "admin") {
    alert("Akses ditolak, Anda bukan admin");
    router.push("/");
    return;
  }

  fetchProducts();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
