<template>
  <div class="min-h-screen bg-gray-50 pb-12">
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center h-screen">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-pink-600"
      ></div>
    </div>

    <!-- Content -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Store Header (Simplified) -->
      <div
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8"
      >
        <div class="flex flex-col md:flex-row items-center gap-6">
          <!-- Profile Photo -->
          <div
            class="w-20 h-20 md:w-24 md:h-24 rounded-full border border-gray-100 shadow-sm overflow-hidden bg-gray-50 flex-shrink-0"
          >
            <img
              :src="storePhoto"
              alt="Store Profile"
              class="w-full h-full object-cover"
            />
          </div>
          <!-- Store Info -->
          <div class="flex-1 pt-2 md:pt-0">
            <div
              class="flex flex-col md:flex-row md:items-center justify-between gap-4"
            >
              <div>
                <div class="flex items-center gap-3 mb-1">
                  <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                    {{ storeName }}
                  </h1>
                  <div
                    class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-100"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 text-yellow-400"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z"
                      />
                    </svg>
                    <span class="font-bold text-gray-900 text-sm">{{
                      ratingToko["rata-rata"]
                    }}</span>
                    <span class="text-gray-500 text-xs"
                      >({{ ratingToko["jumlah"] }} Ulasan)</span
                    >
                  </div>
                </div>

                <div class="flex items-center gap-4 text-sm text-gray-600">
                  <div class="flex items-center gap-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 text-gray-400"
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
                    {{ toko.user?.address || "Alamat tidak tersedia" }}
                  </div>
                </div>
              </div>

              <!-- Stats / Actions -->
              <div class="flex gap-3 items-center">
                <div
                  class="text-center px-4 py-2 bg-gray-50 rounded-lg border border-gray-100 h-fit"
                >
                  <span class="block text-lg font-bold text-gray-900">{{
                    products.length
                  }}</span>
                  <span class="text-xs text-gray-500 uppercase tracking-wide"
                    >Produk</span
                  >
                </div>

                <!-- Frozen Indicator -->
                <div
                  v-if="toko.is_frozen"
                  class="bg-red-50 border border-red-100 text-red-800 px-4 py-2 rounded-xl flex items-center gap-2 h-fit"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4 text-red-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                  </svg>
                  <span class="font-bold text-sm text-red-700"
                    >Toko dibekukan oleh admin</span
                  >
                </div>
              </div>
              <div class="flex items-center gap-3 ml-4">
                <button @click="openStoreReviewForm"
                  v-if="!user || user.role !== 'admin'"
                  class="px-4 py-1.5 border border-pink-600 text-pink-600 font-semibold rounded-lg text-sm hover:bg-pink-50 transition"
                >
                  Beri Rating
                </button>
              </div>
            </div>

            <!-- Description -->
            <div class="mt-4 text-gray-600 text-sm max-w-3xl">
              <p>{{ toko.deskripsi || "Tidak ada deskripsi toko." }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div>
        <h2
          class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 text-pink-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
            />
          </svg>
          Semua Produk
        </h2>

        <div
          v-if="products.length === 0"
          class="text-center py-12 bg-white rounded-xl border border-gray-100"
        >
          <div class="text-gray-400 mb-2 text-4xl">📦</div>
          <p class="text-gray-500">Toko ini belum memiliki produk.</p>
        </div>

        <div
          v-else
          class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6"
        >
          <div
            v-for="p in products"
            :key="p.id_produk"
            class="bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group cursor-pointer"
            @click="
              $router.push({
                name: 'product-detail',
                params: { id: p.id_produk },
              })
            "
          >
            <!-- Product Image -->
            <div class="aspect-square bg-gray-100 relative overflow-hidden">
              <img
                :src="getProductImage(p)"
                :alt="p.nama_produk"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              />
            </div>

            <!-- Product Info -->
            <div class="p-4">
              <h3
                class="font-medium text-gray-900 line-clamp-2 mb-1 group-hover:text-pink-600 transition-colors"
              >
                {{ p.nama_produk }}
                <span class="text-xs text-gray-500 ml-1 font-normal"
                  >({{ p.variant ? p.variant.length : 0 }} Varian)</span
                >
              </h3>
              <p class="text-xs text-gray-500 mb-2">{{ p.merek }}</p>
              <div class="flex items-center justify-between">
                <span class="font-bold text-pink-600">{{
                  getProductPrice(p)
                }}</span>
                <div
                  class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-md"
                  v-if="p.rating > 0"
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
                    p.rating
                  }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Login Required Modal -->
  <div
    v-if="showLoginModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4"
  >
    <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full text-center">
      <div class="mb-4">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-12 w-12 text-pink-600 mx-auto"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
          />
        </svg>
      </div>
      <h3 class="text-lg font-bold text-gray-900 mb-2">Login Diperlukan</h3>
      <p class="text-gray-600 mb-6">
        Tertarik dengan toko ini? Silahkan login terlebih dahulu.
      </p>
      <div class="flex gap-3">
        <button
          @click="showLoginModal = false"
          class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
        >
          Batal
        </button>
        <button
          @click="$router.push('/login')"
          class="flex-1 px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition"
        >
          Login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();
const tokoId = route.params.id;

const toko = ref({});
const products = ref([]);
const loading = ref(true);
const ratingToko = ref({ "rata-rata": 0, jumlah: 0 });
const showLoginModal = ref(false);
const user = ref(null);

const fetchUser = async () => {
  const token = localStorage.getItem("authToken");
  if (!token) return;
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${token}` },
    });
    user.value = res.data;
  } catch (e) {
    console.error("Gagal ambil data user", e);
  }
};

const openStoreReviewForm = () => {
  const token = localStorage.getItem('authToken');
  if (!token) {
    showLoginModal.value = true;
    return;
  }
  router.push({ name: 'review.create', params: { type: 'toko', id: tokoId } });
};

const storeName = computed(() => {
  if (toko.value && toko.value.user && toko.value.user.store_name) {
    return toko.value.user.store_name;
  }
  return toko.value.toko_name || "Nama Toko";
});

const storePhoto = computed(() => {
  if (toko.value && toko.value.user && toko.value.user.store_photo) {
    return `http://127.0.0.1:8000/storage/${toko.value.user.store_photo}`;
  }
  return "https://via.placeholder.com/150?text=Store";
});

const getProductImage = (product) => {
  if (
    product.variant &&
    product.variant.length > 0 &&
    product.variant[0].gambar_varian
  ) {
    return `http://127.0.0.1:8000/storage/${product.variant[0].gambar_varian}`;
  }
  return "https://via.placeholder.com/300?text=No+Image";
};

const getProductPrice = (product) => {
  if (product.variant && product.variant.length > 0) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(Number(product.variant[0].harga));
  }
  return "Rp 0";
};

onMounted(async () => {
  fetchUser();
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/toko/${tokoId}`);

    if (res.data.data && res.data.data.length > 0) {
      toko.value = res.data.data[0];
    }
    products.value = res.data.products;
    ratingToko.value = res.data.ratingToko;

    if (products.value.length > 0) {
      if (!products.value[0].variant) {
        // ...
      }
    }
  } catch (error) {
    console.log(error);
  } finally {
    loading.value = false;
  }
});
</script>
