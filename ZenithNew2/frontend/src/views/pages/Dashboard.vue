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
      recommendedProducts.value = [
        ...recommendedProducts.value,
        ...newProducts,
      ];
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
      product.variant && product.variant.length > 0 ? product.variant[0] : null;
    let image = "http://127.0.0.1:8000/img_placeholder.jpg";
    let price = "Rp 0";

    if (variant) {
      // Handle image path
      if (variant.gambar_varian) {
        // Check if it starts with http or https
        if (variant.gambar_varian.startsWith("http")) {
          image = variant.gambar_varian;
        } else if (
          variant.gambar_varian.includes("img_placeholder.jpg") ||
          variant.gambar_varian.startsWith("..")
        ) {
          image = "http://127.0.0.1:8000/img_placeholder.jpg";
        } else {
          image = `http://127.0.0.1:8000/storage/${variant.gambar_varian}`;
        }
      }
      price = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(variant.harga);
    }

    // Extract category name (assuming first category if multiple)
    let categoryName = "";
    if (
      product.category_detail &&
      product.category_detail.length > 0 &&
      product.category_detail[0].category
    ) {
      categoryName = product.category_detail[0].category.nama_kategori;
    }

    return {
      id: product.id_produk,
      name: product.nama_produk,
      price: price,
      rating: 0, // Default rating as it's not in the main list API
      brand: product.merek,
      image: image,
      storeName: product.toko ? product.toko.toko_name : "Toko",
      storeId: product.toko ? product.toko.id : null,
      category: categoryName,
    };
  });
};

/* =============================
   LOAD USER SAAT HALAMAN DIBUKA
============================= */
onMounted(async () => {
  const token = localStorage.getItem("authToken");

  // Fetch Categories (Public)
  try {
    const catRes = await axios.get("http://127.0.0.1:8000/api/categories");
    if (catRes.data && catRes.data.data) {
      popularTags.value = catRes.data.data.map((cat) => ({
        id: cat.id_kategori,
        name: cat.nama_kategori,
        icon: getCategoryIcon(cat.nama_kategori),
      }));
    }
  } catch (e) {
    console.error("Failed to fetch categories", e);
  }

  // Fetch Products (Public)
  try {
    const productRes = await axios.get(
      "http://127.0.0.1:8000/api/products?per_page=10"
    );
    if (productRes.data && productRes.data.data) {
      recommendedProducts.value = processProducts(productRes.data.data);
      nextUrl.value = productRes.data.next_page_url;
    }
  } catch (error) {
    console.error("Gagal mengambil data produk:", error);
  }

  // Fetch User (Only if token exists)
  if (token) {
    try {
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
      const res = await axios.get("http://127.0.0.1:8000/api/user");

      user.value = res.data;

      if (res.data.role) {
        localStorage.setItem("userRole", res.data.role);
        role = res.data.role;
      }
    } catch (error) {
      console.error("Gagal mengambil data user:", error);
      if (error.response && error.response.status === 401) {
        localStorage.removeItem("authToken");
        localStorage.removeItem("userRole");
        // Optional: Redirect to login or just stay as guest
        // router.push("/login");
      }
    }
  }
});

const getCategoryIcon = (name) => {
  // Simple mapping or return a generic icon
  // For now returning a simple generic SVG
  return `<svg xmlns="http://www.w3.org/2000/svg" class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

const resendVerification = async () => {
  try {
    await axios.post("/email/verification-notification");
    toast.success("Link verifikasi telah dikirim ke email Anda!");
  } catch (error) {
    if (error.response && error.response.status === 429) {
      toast.error("Terlalu banyak permintaan. Tunggu beberapa saat.");
    } else {
      toast.error("Gagal mengirim link verifikasi.");
    }
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <main class="px-4 py-6 md:px-6 lg:px-8 max-w-7xl mx-auto w-full">
      <!-- Verification Alert -->
      <div
        v-if="user && !user.email_verified_at"
        class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center"
      >
        <div>
          <p class="font-bold">Email Belum Terverifikasi</p>
          <p class="text-sm">
            Silakan cek email Anda untuk memverifikasi akun. Belum terima email?
          </p>
        </div>
        <button
          @click="resendVerification"
          class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm transition-colors"
        >
          Kirim Ulang
        </button>
      </div>

      <!-- Hero Banner -->
      <div
        class="relative bg-gradient-to-r from-pink-600 to-purple-600 rounded-2xl p-8 md:p-10 mb-8 overflow-hidden shadow-lg text-white"
      >
        <div class="relative z-10">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">
            Selamat Datang di Zenith
          </h1>
          <p class="text-pink-100 mb-0 max-w-xl">
            Temukan berbagai produk pilihan dengan kualitas terbaik dan harga
            terjangkau.
          </p>
        </div>
        <div
          class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"
        ></div>
        <div
          class="absolute bottom-0 right-20 w-40 h-40 bg-pink-400 opacity-20 rounded-full blur-2xl"
        ></div>
      </div>

      <!-- Modern Section: Tag Populer & Rekomendasi -->
      <section class="mb-8">
        <!-- Tag Populer -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 mb-8">
          <h3 class="font-bold text-gray-800 mb-4 text-lg">Kategori Pilihan</h3>
          <div class="flex overflow-x-auto pb-2 gap-3 no-scrollbar">
            <div
              v-for="tag in popularTags"
              :key="tag.id"
              class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-pink-500 text-pink-600 transition-all duration-300 cursor-pointer whitespace-nowrap min-w-max"
              @click="
                $router.push({
                  name: 'searching',
                  query: { category: tag.name },
                })
              "
            >
              <span class="text-sm font-bold">{{ tag.name }}</span>
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

          <div
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 mb-8"
          >
            <div
              v-for="product in recommendedProducts"
              :key="product.id_produk"
              class="bg-white rounded-2xl p-3 hover:shadow-xl transition-all duration-300 cursor-pointer group flex flex-col h-full border border-gray-200 hover:border-pink-100 relative"
              @click="
                $router.push({
                  name: 'product-detail',
                  params: { id: product.id },
                })
              "
            >
              <!-- Image Container -->
              <div
                class="relative overflow-hidden rounded-xl mb-3 aspect-square bg-gray-50"
              >
                <img
                  :src="product.image"
                  :alt="product.name"
                  class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
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
                  {{ product.name }}
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
                    @click.stop="
                      product.storeId
                        ? $router.push({
                            name: 'toko.detail',
                            params: { id: product.storeId },
                          })
                        : null
                    "
                  >
                    {{ product.storeName }}
                  </span>
                </div>

                <!-- 3. Brand & Category (Light) -->
                <div class="text-xs text-gray-400 mb-2 truncate">
                  {{ product.brand
                  }}<span v-if="product.brand && product.category">, </span
                  >{{ product.category }}
                </div>

                <!-- Price -->
                <div
                  class="mt-auto pt-2 border-t border-gray-50 flex justify-between items-center"
                >
                  <p class="text-pink-600 font-bold text-base">
                    {{ product.price }}
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

          <!-- Load More Button -->
          <div class="flex justify-center mt-6" v-if="nextUrl">
            <button
              @click="loadMoreProducts"
              :disabled="isLoadingMore"
              class="px-8 py-3 bg-white border border-pink-600 text-pink-600 font-semibold rounded-full hover:bg-pink-50 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <span v-if="isLoadingMore">Memuat...</span>
              <span v-else>Lihat Lebih Banyak</span>
              <svg
                v-if="!isLoadingMore"
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
                  d="M19 9l-7 7-7-7"
                />
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
