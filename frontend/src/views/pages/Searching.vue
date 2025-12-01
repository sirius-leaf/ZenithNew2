<template>
  <main class="px-4 py-6 md:px-6 lg:px-8 max-w-7xl mx-auto w-full">
    <!-- Tabs -->
    <div class="flex justify-center mb-8">
      <div class="bg-gray-100 p-1 rounded-xl inline-flex">
        <button
          @click="activeTab = 'produk'"
          :class="[
            'px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200',
            activeTab === 'produk'
              ? 'bg-white text-pink-600 shadow-sm'
              : 'text-gray-500 hover:text-gray-700',
          ]"
        >
          Produk
        </button>
        <button
          @click="activeTab = 'toko'"
          :class="[
            'px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200',
            activeTab === 'toko'
              ? 'bg-white text-pink-600 shadow-sm'
              : 'text-gray-500 hover:text-gray-700',
          ]"
        >
          Toko
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-pink-500"></div>
      <p class="mt-2 text-gray-600">Memuat data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center text-red-500 py-10">
      Terjadi kesalahan: {{ error.message }}
    </div>

    <!-- Main Content -->
    <div v-else class="flex flex-col md:flex-row gap-8">
      <!-- Sidebar Filter (Only for Products) -->
      <aside v-if="activeTab === 'produk'" class="w-full md:w-64 flex-shrink-0">
        <ProductSidebar
          :initial-category="selectedCategory"
          @category-selected="applyCategoryFilter"
        />
      </aside>

      <!-- Content Area -->
      <div class="flex-grow">
        <!-- PRODUK VIEW -->
        <div v-if="activeTab === 'produk'">
          <!-- Header Info & Sort -->
          <div
            class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"
          >
            <p class="text-gray-600 text-sm">
              Menampilkan
              <span class="font-bold text-gray-900"
                >{{ filteredProducts.length > 0 ? 1 : 0 }} -
                {{ filteredProducts.length }}</span
              >
              barang dari total untuk "<span class="font-bold text-gray-900">{{
                searchQuery || selectedCategory || "Semua Produk"
              }}</span
              >"
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
          <div
            v-if="filteredProducts.length > 0"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
          >
            <div
              v-for="product in filteredProducts"
              :key="product.id_produk"
              class="bg-white rounded-2xl p-3 hover:shadow-xl transition-all duration-300 cursor-pointer group flex flex-col h-full border border-gray-200 hover:border-pink-100 relative"
              @click="
                $router.push({
                  name: 'product-detail',
                  params: { id: product.id_produk },
                })
              "
            >
              <!-- Image Container -->
              <div
                class="relative overflow-hidden rounded-xl mb-3 aspect-square bg-gray-50"
              >
                <img
                  :src="product.main_image_url"
                  :alt="product.nama_produk"
                  class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                  @error="
                    (e) =>
                      (e.target.src =
                        'https://via.placeholder.com/200/FFFFFF/000000?text=No+Image')
                  "
                />
                <!-- Overlay Gradient -->
                <div
                  class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
              </div>

              <!-- Content -->
              <div class="flex flex-col flex-grow px-1">
                <!-- 1. Product Name (Bold) -->
                <h4
                  class="font-bold text-gray-900 text-sm mb-1 line-clamp-2 leading-snug group-hover:text-pink-600 transition-colors"
                >
                  {{ product.nama_produk }}
                </h4>

                <!-- 2. Store Name -->
                <div class="flex items-center gap-1 mb-1">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    />
                  </svg>
                    <span
                        class="text-xs text-gray-600 truncate hover:text-pink-600 hover:underline cursor-pointer"
                        @click.stop="product.toko?.id ? $router.push({ name: 'toko.detail', params: { id: product.toko.id } }) : null"
                    >
                        {{ product.toko?.toko_name || 'Toko' }}
                    </span>
                </div>

                <!-- 3. Brand & Category (Light) -->
                <div class="text-xs text-gray-400 mb-2 truncate">
                  {{ product.merek
                  }}<span
                    v-if="
                      product.merek &&
                      product.category_detail?.[0]?.category?.nama_kategori
                    "
                    >, </span
                  >{{ product.category_detail?.[0]?.category?.nama_kategori }}
                </div>

                <!-- Price -->
                <div
                  class="mt-auto pt-2 border-t border-gray-50 flex justify-between items-center"
                >
                  <p class="text-pink-600 font-bold text-base">
                    {{
                      Number(product.variant?.[0]?.harga ?? 0).toLocaleString(
                        "id-ID",
                        {
                          style: "currency",
                          currency: "IDR",
                          minimumFractionDigits: 0,
                          maximumFractionDigits: 0,
                        }
                      )
                    }}
                  </p>
                  <div
                    class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-md"
                    v-if="product.rating > 0"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-3 w-3 text-yellow-400"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 00-1.028-.684H9.049a1 1 0 00-1.028.684L7.95 6.316a1 1 0 00.919.592l3.181.45a1 1 0 001.028-.684l1.07-3.292a1 1 0 00-1.028-.684H9.049z"
                      />
                    </svg>
                    <span class="text-xs font-bold text-gray-700">{{
                      product.rating
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pesan Tidak Ditemukan -->
          <div
            v-else
            class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300"
          >
            <p class="text-xl font-medium text-gray-600">
              Maaf, produk tidak ditemukan.
            </p>
            <button
              @click="selectedCategory = null"
              class="mt-4 text-pink-600 hover:underline font-medium"
            >
              Tampilkan Semua Produk
            </button>
          </div>
        </div>

        <!-- TOKO VIEW -->
        <div v-else-if="activeTab === 'toko'">
          <div
            v-if="filteredShops.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <div
              v-for="shop in filteredShops"
              :key="shop.id"
              class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition flex flex-col h-full"
            >
              <!-- Header: Profil & Info -->
              <div class="flex items-center gap-4 mb-4">
                <img
                  :src="shop.image"
                  :alt="shop.name"
                  class="w-14 h-14 rounded-full object-cover border border-gray-200"
                />
                <div>
                  <h4 class="font-bold text-gray-900 text-base line-clamp-1">
                    {{ shop.name }}
                  </h4>
                  <div class="flex items-center text-gray-500 text-xs mt-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-3 w-3 mr-1"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                    </svg>
                    {{ shop.location }}
                  </div>
                </div>
              </div>

              <!-- Body: 3 Foto Produk -->
              <div class="grid grid-cols-3 gap-2 mb-4">
                <div
                  v-for="(img, index) in shop.productImages"
                  :key="index"
                  class="aspect-square rounded-md overflow-hidden bg-gray-100"
                >
                  <img
                    :src="img"
                    class="w-full h-full object-cover"
                    alt="Product Preview"
                  />
                </div>
              </div>

              <!-- Footer: Tombol Lihat Toko -->
              <div class="mt-auto">
                <button
                  v-if="shop.tokoId"
                  @click="
                    $router.push({
                      name: 'toko.detail',
                      params: { id: shop.tokoId },
                    })
                  "
                  class="w-full py-2 rounded-lg border border-pink-600 text-pink-600 font-medium text-sm hover:bg-pink-50 transition-colors"
                >
                  Lihat Toko
                </button>
                <button
                  v-else
                  disabled
                  class="w-full py-2 rounded-lg border border-gray-300 text-gray-400 font-medium text-sm cursor-not-allowed"
                >
                  Toko Belum Aktif
                </button>
              </div>
            </div>
          </div>

          <div
            v-else
            class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300"
          >
            <p class="text-xl font-medium text-gray-600">
              Maaf, toko tidak ditemukan.
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import ProductSidebar from "@/components/layout/ProductSidebar.vue";

const route = useRoute();
const router = useRouter();

// State
const activeTab = ref("produk"); // 'produk' or 'toko'
const products = ref([]);
const shops = ref([]); // Store shops data
const loading = ref(true);
const error = ref(null);
const selectedCategory = ref(null);
const sortByPrice = ref("");
const searchQuery = ref(route.query.q || "");

// Watch query param changes
watch(
  () => route.query.q,
  (newQuery) => {
    searchQuery.value = newQuery || "";
    fetchProducts();
    fetchShops();
  }
);

watch(
  () => route.query.category,
  (newCategory) => {
    selectedCategory.value = newCategory || null;
    fetchProducts();
  }
);

// Fungsi helper untuk mendapatkan URL gambar dari varian pertama
const getImageUrl = (product) => {
  if (product.variant && product.variant.length > 0) {
    const imagePath = product.variant[0].gambar_varian;
    if (imagePath) {
      if (imagePath.startsWith("http")) return imagePath;

      // Handle seeder placeholder or relative paths that are likely broken
      if (imagePath.includes("img_placeholder.jpg") || imagePath.startsWith("..")) {
         return "https://via.placeholder.com/300?text=Product+Image";
      }

      // Remove any leading ./
      const cleanPath = imagePath.replace(/^\.\//, '');

      // Check if path already contains 'storage/'
      if (cleanPath.startsWith("storage/")) {
        return `http://127.0.0.1:8000/${cleanPath}`;
      }
      return `http://127.0.0.1:8000/storage/${cleanPath}`;
    }
  }
  return "https://via.placeholder.com/200/FFFFFF/000000?text=No+Image";
};

// Fetch Data
const fetchProducts = async () => {
  loading.value = true;
  error.value = null;
  try {
    // Build query params
    const params = {};
    if (searchQuery.value) params.q = searchQuery.value;
    if (selectedCategory.value) params.category = selectedCategory.value;

    // Fetch Products
    const productRes = await axios.get("http://127.0.0.1:8000/api/products", {
      params,
    });
    let fetchedProducts = productRes.data.data;

    // Tambahkan URL gambar ke setiap produk
    fetchedProducts = fetchedProducts.map((product) => {
      product.main_image_url = getImageUrl(product);
      return product;
    });
    products.value = fetchedProducts;
  } catch (err) {
    error.value = err;
    console.error("Error fetching data:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  // Initialize state from query params
  if (route.query.q) searchQuery.value = route.query.q;
  if (route.query.category) selectedCategory.value = route.query.category;

  await fetchProducts();

  await fetchProducts();
  await fetchShops();
});

// Fetch Shops
const fetchShops = async () => {
  try {
    const params = {};
    if (searchQuery.value) params.q = searchQuery.value;

    const res = await axios.get("http://127.0.0.1:8000/api/shops", { params });
    const fetchedUsers = res.data.data; // Pagination data is in res.data.data

    shops.value = fetchedUsers.map((user) => {
      // Map products images if available
      const productImages =
        user.toko?.products?.map((p) => {
          if (p.variant && p.variant.length > 0) {
            const imagePath = p.variant[0].gambar_varian;
            if (imagePath) {
              if (imagePath.startsWith("http")) return imagePath;
              
              // Remove any leading ./ or ../
              const cleanPath = imagePath.replace(/^(\.\.\/)+/, "").replace(/^\.\//, "");
              
              // Check if path already contains 'storage/'
              if (cleanPath.startsWith("storage/")) {
                return `http://127.0.0.1:8000/${cleanPath}`;
              }
              return `http://127.0.0.1:8000/storage/${cleanPath}`;
            }
          }
          return "https://via.placeholder.com/100?text=No+Image";
        }) || [];

      return {
        id: user.id, // Or user.toko.id if we want to link to toko detail
        // Note: The route 'toko.detail' likely expects id_toko.
        // If user doesn't have a toko record yet, this might be an issue.
        // But for now let's use user.toko?.id_toko || user.id and handle it.
        // Actually, let's check if user.toko exists.
        tokoId: user.toko?.id,
        name: user.store_name,
        location: user.address,
        image: user.store_photo
          ? `http://127.0.0.1:8000/storage/${user.store_photo}`
          : `https://ui-avatars.com/api/?name=${encodeURIComponent(
              user.store_name || "Store"
            )}&background=random`,
        productImages: productImages.slice(0, 3),
      };
    });
  } catch (err) {
    console.error("Error fetching shops:", err);
  }
};

// Computed: Produk yang sudah difilter dan diurutkan
const filteredProducts = computed(() => {
  let result = [...products.value];

  // Filter berdasarkan kategori (Client-side removed, handled by API)
  // if (selectedCategory.value) {
  //   result = result.filter(p => p.nama_produk.toLowerCase().includes(selectedCategory.value.toLowerCase()));
  // }

  // Filter berdasarkan Search Query (Client-side removed, handled by API)
  // if (searchQuery.value) {
  //   const query = searchQuery.value.toLowerCase();
  //   result = result.filter(p => p.nama_produk.toLowerCase().includes(query));
  // }

  // Urutkan berdasarkan harga
  if (sortByPrice.value === "asc") {
    result.sort(
      (a, b) => (a.variant?.[0]?.harga ?? 0) - (b.variant?.[0]?.harga ?? 0)
    );
  } else if (sortByPrice.value === "desc") {
    result.sort(
      (a, b) => (b.variant?.[0]?.harga ?? 0) - (a.variant?.[0]?.harga ?? 0)
    );
  }

  return result;
});

// Computed: Filtered Shops
const filteredShops = computed(() => {
  let result = [...shops.value];

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((s) => s.name.toLowerCase().includes(query));
  }

  return result;
});

// Handler untuk filter kategori dari sidebar
const applyCategoryFilter = (categoryName) => {
  selectedCategory.value = categoryName;
  // Update URL query param without reloading page
  router.push({ query: { ...route.query, category: categoryName } });
  fetchProducts();
};
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
::-webkit-scrollbar {
  display: none;
}
html {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}

.bg-gray-50:hover {
  background-color: #f9fafb;
}
</style>
