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
                @click="handleDetail(product.id)"
                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors border border-blue-200 text-sm font-medium"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
                Detail
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

    <!-- Detail Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-md bg-white/30 px-4"
      @click.self="closeDetailModal"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto animate-fade-in"
      >
        <div class="p-6">
          <div class="flex justify-between items-start mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Detail Produk</h2>
            <button
              @click="closeDetailModal"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div v-if="loadingDetail" class="text-center py-12">
            <div
              class="inline-block w-8 h-8 border-4 border-pink-300 border-t-pink-600 rounded-full animate-spin"
            ></div>
            <p class="mt-3 text-gray-600">Memuat detail...</p>
          </div>

          <div v-else-if="selectedProduct" class="space-y-6">
            <!-- Header Info -->
            <div class="flex flex-col md:flex-row gap-6">
              <div class="w-full md:w-1/3">
                <div
                  class="aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50"
                >
                  <img
                    :src="selectedImage"
                    class="w-full h-full object-contain"
                    alt="Product Image"
                  />
                </div>
              </div>
              <div class="w-full md:w-2/3 space-y-4">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">
                    {{ selectedProduct.nama_produk }}
                  </h3>
                  <p class="text-sm text-gray-500">
                    {{ selectedCategoryName || "Uncategorized" }}
                  </p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <p class="text-gray-500">Toko</p>
                    <p class="font-medium text-gray-900">
                      {{ selectedProduct.toko?.toko_name || "-" }}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500">Merek</p>
                    <p class="font-medium text-gray-900">
                      {{ selectedProduct.merek || "-" }}
                    </p>
                  </div>
                </div>

                <div>
                  <p class="text-gray-500 text-sm mb-1">Deskripsi</p>
                  <div
                    class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700 max-h-32 overflow-y-auto whitespace-pre-line"
                  >
                    {{ selectedProduct.deskripsi }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Variants -->
            <div v-if="selectedProduct.variant && selectedProduct.variant.length > 0">
              <h4 class="font-bold text-gray-900 mb-3">Varian Produk</h4>
              <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th
                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                      >
                        Gambar
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                      >
                        Nama Varian
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                      >
                        Harga
                      </th>
                      <th
                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                      >
                        Stok
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="variant in selectedProduct.variant" :key="variant.id">
                      <td class="px-4 py-2">
                        <img
                          :src="getVariantImage(variant)"
                          class="w-10 h-10 object-cover rounded border cursor-pointer hover:ring-2 hover:ring-blue-500"
                          alt="Variant"
                          @click="selectedImage = getVariantImage(variant)"
                        />
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-900">
                        {{ variant.nama_varian }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-900">
                        Rp {{ Number(variant.harga).toLocaleString("id-ID") }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-900">
                        {{ variant.stok }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex justify-between rounded-b-xl">
          <button
            @click="confirmDelete"
            class="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition font-medium"
          >
            Hapus Produk
          </button>
          <button
            @click="closeDetailModal"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 z-[60] flex items-center justify-center backdrop-blur-sm bg-black/20 px-4"
    >
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 animate-fade-in">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
        <p class="text-gray-600 mb-6">
          Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat
          dibatalkan.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteConfirm = false"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
          >
            Batal
          </button>
          <button
            @click="deleteProduct"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "vue-toastification";

const router = useRouter();
const toast = useToast();
const products = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const selectedCategory = ref("");
const currentPage = ref(1);
const itemsPerPage = 5;

// Modal State
const showModal = ref(false);
const selectedProduct = ref(null);
const loadingDetail = ref(false);
const selectedCategoryName = ref("");
const selectedImage = ref("");
const showDeleteConfirm = ref(false);

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

const handleDetail = async (id) => {
  console.log("Opening detail for id:", id);
  showModal.value = true;
  loadingDetail.value = true;
  selectedProduct.value = null;
  selectedCategoryName.value = "";
  selectedImage.value = "";

  // Get category from existing list
  const listProduct = products.value.find((p) => p.id === id);
  if (listProduct) {
    selectedCategoryName.value = listProduct.category;
  }

  try {
    const response = await axios.get(`http://127.0.0.1:8000/api/products/${id}`);
    if (response.data && response.data.data) {
      selectedProduct.value = response.data.data;
      selectedImage.value = getMainImage(selectedProduct.value);
    }
  } catch (error) {
    console.error("Gagal memuat detail produk:", error);
    toast.error("Gagal memuat detail produk.");
    showModal.value = false;
  } finally {
    loadingDetail.value = false;
  }
};

const closeDetailModal = () => {
  showModal.value = false;
  selectedProduct.value = null;
  showDeleteConfirm.value = false;
};

const confirmDelete = () => {
  showDeleteConfirm.value = true;
};

const deleteProduct = async () => {
  if (!selectedProduct.value) return;

  try {
    await axios.delete(`/manage/product/${selectedProduct.value.id_produk}`);
    toast.success("Produk berhasil dihapus.");
    closeDetailModal();
    fetchProducts(); // Refresh list
  } catch (error) {
    console.error("Gagal menghapus produk:", error);
    toast.error("Gagal menghapus produk. Silakan coba lagi.");
  }
};

const getMainImage = (product) => {
  if (product.variant && product.variant.length > 0 && product.variant[0].gambar_varian) {
    const path = product.variant[0].gambar_varian;
    if (path.startsWith("http")) return path;
    return `http://127.0.0.1:8000/storage/${path}`;
  }
  return "https://via.placeholder.com/300?text=No+Image";
};

const getVariantImage = (variant) => {
  if (variant.gambar_varian) {
    const path = variant.gambar_varian;
    if (path.startsWith("http")) return path;
    return `http://127.0.0.1:8000/storage/${path}`;
  }
  return "https://via.placeholder.com/100?text=No+Image";
};

const getImageUrl = (path) => {
  if (!path) return "";
  if (path.startsWith("http")) return path;
  return `http://127.0.0.1:8000/storage/${path}`;
};

onMounted(() => {
  console.log("KelolaProduk mounted");
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  if (localStorage.getItem("userRole") !== "admin") {
    toast.error("Akses ditolak, Anda bukan admin");
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
