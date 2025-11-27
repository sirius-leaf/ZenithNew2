<!-- src/views/pages/Dashboard.vue -->
<script setup>
/* =============================
   IMPORT
============================= */
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "vue-toastification";

/* =============================
   STATE & INITIAL
============================= */
const router = useRouter();
const toast = useToast();
const user = ref(null);
let role = "";

/* =============================
   DUMMY DATA FOR TAG POPULER & REKOMENDASI
============================= */
// Data Tag Populer
const popularTags = ref([]);

// Data Rekomendasi Produk
const recommendedProducts = ref([]);
const nextUrl = ref(null); // URL for next page
const isLoadingMore = ref(false); // Loading state for "Load More" button

const loadMoreProducts = async () => {
  if (!nextUrl.value || isLoadingMore.value) return;

  isLoadingMore.value = true;
  try {
    const res = await axios.get(nextUrl.value);
    if (res.data && res.data.data) {
      const newProducts = processProducts(res.data.data);
      recommendedProducts.value = [...recommendedProducts.value, ...newProducts];
      nextUrl.value = res.data.next_page_url;
    }
  } catch (error) {
    console.error("Error loading more products:", error);
  } finally {
    isLoadingMore.value = false;
  }
};

const processProducts = (productsData) => {
  return productsData.map((product) => {
    const variant =
      product.variant && product.variant.length > 0
        ? product.variant[0]
        : null;
    let image =
      "https://via.placeholder.com/200/FFFFFF/000000?text=No+Image";
    let price = "Rp 0";

    if (variant) {
      // Handle image path
      if (variant.gambar_varian) {
        // Check if it starts with http or https
        if (variant.gambar_varian.startsWith("http")) {
          image = variant.gambar_varian;
        } else {
          image = `http://127.0.0.1:8000/storage/${variant.gambar_varian}`;
        }
      }
      price = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(variant.harga);
    }

    return {
      id: product.id_produk,
      name: product.nama_produk,
      price: price,
      rating: 0, // Default rating as it's not in the main list API
      brand: product.merek,
      image: image,
    };
  });
};

/* =============================
   LOAD USER SAAT HALAMAN DIBUKA
============================= */
onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  try {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    const res = await axios.get("http://127.0.0.1:8000/api/user");

    user.value = res.data;

    if (res.data.role) {
      localStorage.setItem("userRole", res.data.role);
      role = res.data.role;
    }

    // Fetch Categories
    try {
      const catRes = await axios.get("http://127.0.0.1:8000/api/categories");
      if (catRes.data && catRes.data.data) {
        popularTags.value = catRes.data.data.map(cat => ({
          id: cat.id_kategori,
          name: cat.nama_kategori,
          // Use a default icon or map based on name if needed
          icon: getCategoryIcon(cat.nama_kategori)
        }));
      }
    } catch (e) {
      console.error("Failed to fetch categories", e);
    }

    // Fetch Products (Initial load 25 items)
    const productRes = await axios.get("http://127.0.0.1:8000/api/products?per_page=25");
    if (productRes.data && productRes.data.data) {
      recommendedProducts.value = processProducts(productRes.data.data);
      nextUrl.value = productRes.data.next_page_url;
    }
  } catch (error) {
    console.error("Gagal mengambil data:", error);
    // Don't force logout on product fetch fail, only on user fetch fail if 401
    if (error.response && error.response.status === 401) {
      localStorage.removeItem("authToken");
      localStorage.removeItem("userRole");
      router.push("/login");
    }
  }
});

const getCategoryIcon = (name) => {
  // Simple mapping or return a generic icon
  // For now returning a simple generic SVG
  return `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 group-hover:text-pink-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>`;
};

/* =============================
   SCROLL FUNCTIONS FOR TAG SLIDER
============================= */
const scrollLeft = (sliderId) => {
  const slider = document.getElementById(sliderId);
  if (slider) {
    slider.scrollBy({ left: -200, behavior: "smooth" });
  }
};

const scrollRight = (sliderId) => {
  const slider = document.getElementById(sliderId);
  if (slider) {
    slider.scrollBy({ left: 200, behavior: "smooth" });
  }
};

/* =============================
   LOGOUT HANDLER
============================= */
const handleLogout = () => {
  localStorage.removeItem("authToken");
  localStorage.removeItem("userRole");
  delete axios.defaults.headers.common["Authorization"];
  router.push("/login");
};
</script>

<template>
  <div v-if="user" class="min-h-screen bg-gray-50">
    <main class="px-4 py-6 md:px-6 lg:px-8 max-w-7xl mx-auto w-full">
      <!-- Modern Section: Tag Populer & Rekomendasi -->
      <section class="mb-8">
        <!-- Tag Populer -->
        <!-- Tag Populer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
          <h3 class="font-bold text-gray-800 mb-6 text-lg uppercase tracking-wide border-b pb-2">KATEGORI</h3>
          <div class="flex flex-wrap justify-center gap-8">
            <div
              v-for="tag in popularTags"
              :key="tag.id"
              class="flex flex-col items-center group cursor-pointer w-24"
              @click="$router.push({ name: 'searching', query: { category: tag.name } })"
            >
              <div
                class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-white group-hover:shadow-md transition-all duration-300 border border-transparent group-hover:border-gray-100"
                v-html="tag.icon"
              ></div>
              <span
                class="text-sm font-medium text-gray-600 group-hover:text-pink-600 transition-colors text-center leading-tight"
                >{{ tag.name }}</span
              >
            </div>
          </div>
        </div>

        <!-- Rekomendasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h3
            class="font-bold text-pink-600 mb-4 text-lg text-center uppercase tracking-wider border-b-2 border-pink-300 pb-2"
          >
            Rekomendasi
          </h3>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
            <div
              v-for="product in recommendedProducts"
              :key="product.id_produk"
              class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition cursor-pointer group flex flex-col h-full"
              @click="
                $router.push({
                  name: 'product-detail',
                  params: { id: product.id },
                })
              "
            >
              <div class="relative overflow-hidden rounded-md mb-3 aspect-square">
                <img
                  :src="product.image"
                  :alt="product.name"
                  class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500"
                />
              </div>
              <h4
                class="font-semibold text-gray-800 text-sm mb-1 line-clamp-2 group-hover:text-pink-600 transition-colors flex-grow"
              >
                {{ product.name }}
              </h4>
              <p class="text-pink-600 font-bold mb-1 text-sm">{{ product.price }}</p>
              <div class="flex items-center mb-2">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-3 w-3 text-yellow-400"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 00-1.028-.684H9.049a1 1 0 00-1.028.684L7.95 6.316a1 1 0 00.919.592l3.181.45a1 1 0 001.028-.684l1.07-3.292a1 1 0 00-1.028-.684H9.049z"
                  />
                </svg>
                <span class="text-xs text-gray-600 ml-1">{{
                  product.rating
                }}</span>
              </div>
              <div class="flex items-center mt-auto">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-3 w-3 text-green-500"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2z"
                    clip-rule="evenodd"
                  />
                </svg>
                <span class="text-xs text-gray-600 ml-1 truncate">{{
                  product.brand
                }}</span>
              </div>
            </div>
          </div>

          <!-- Load More Button -->
          <div class="flex justify-center mt-6" v-if="nextUrl">
            <button 
              @click="loadMoreProducts" 
              :disabled="isLoadingMore"
              class="px-8 py-3 bg-white border border-pink-600 text-pink-600 font-semibold rounded-full hover:bg-pink-50 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <span v-if="isLoadingMore">Memuat...</span>
              <span v-else>Lihat Lebih Banyak</span>
              <svg v-if="!isLoadingMore" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>
        </div>
      </section>

      <!-- Router View -->
      <div
        v-if="$route.path !== '/dashboard'"
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <router-view />
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
html {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}

/* Optional: Add a subtle animation on hover for products */
.bg-gray-50:hover {
  background-color: #f9fafb;
}
</style>
