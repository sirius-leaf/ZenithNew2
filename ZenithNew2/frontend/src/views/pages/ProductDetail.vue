<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";
import { useCartStore } from "@/stores/cartStore";
import ProductReviews from "@/components/product/ProductReviews.vue";
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();
const { updateCartItem } = useCartStore();

const product = ref(null);
const tokoData = ref(null);
const tokoRating = ref(0);
const rating = ref(null);
const loading = ref(true);
const error = ref(null);
const user = ref(null);

// State untuk varian yang dipilih
const selectedVariant = ref(null);
const quantity = ref(1);
const activeTab = ref("detail");
const isDescriptionExpanded = ref(false);

// Computed: URL Gambar Utama
const mainImage = computed(() => {
  if (selectedVariant.value && selectedVariant.value.gambar_varian) {
    const imagePath = selectedVariant.value.gambar_varian;
    if (imagePath.startsWith("http")) return imagePath;
    return `http://127.0.0.1:8000/storage/${imagePath}`;
  }

  if (
    product.value &&
    product.value.variant &&
    product.value.variant.length > 0
  ) {
    const imagePath = product.value.variant[0].gambar_varian;
    if (imagePath) {
      if (imagePath.startsWith("http")) return imagePath;
      return `http://127.0.0.1:8000/storage/${imagePath}`;
    }
  }
  return "https://via.placeholder.com/400x300/CCCCCC?text=No+Image";
});

const storePhoto = computed(() => {
  if (
    tokoData.value &&
    tokoData.value.user &&
    tokoData.value.user.store_photo
  ) {
    const photo = tokoData.value.user.store_photo;
    if (photo.startsWith("http")) return photo;
    return `http://127.0.0.1:8000/storage/${photo}`;
  }
  return "https://via.placeholder.com/150?text=Store";
});

const storeName = computed(() => {
  if (tokoData.value && tokoData.value.user && tokoData.value.user.store_name) {
    return tokoData.value.user.store_name;
  }
  return product.value?.toko?.toko_name || "Nama Toko";
});

// Fungsi untuk memilih varian
const selectVariant = (variant) => {
  selectedVariant.value = variant;
  quantity.value = 1; // Reset kuantitas saat varian berubah
};

// Fungsi untuk mengatur kuantitas
const incrementQuantity = () => {
  if (selectedVariant.value && quantity.value < selectedVariant.value.stok) {
    quantity.value++;
  }
};

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

// Fungsi untuk tombol "Beli Langsung"
const handleBuyNow = () => {
  if (!selectedVariant.value) {
    Swal.fire("Peringatan", "Silakan pilih varian terlebih dahulu.", "warning");
    return;
  }
  if (selectedVariant.value.stok <= 0) {
    Swal.fire("Stok Habis", "Stok habis. Tidak bisa membeli.", "error");
    return;
  }
  if (tokoData.value?.is_frozen) {
    Swal.fire("Toko Dibekukan", "Toko sedang dibekukan. Tidak dapat melakukan pembelian.", "error");
    return;
  }

  // Redirect langsung ke checkout (hapus alert testing)
  const buyNowItem = [{
    id_varian: selectedVariant.value.id_varian,
    kuantitas: quantity.value,
    nama_varian: selectedVariant.value.nama_varian,
    harga: selectedVariant.value.harga,
    stok: selectedVariant.value.stok,
    product_name: product.value.nama_produk,
    product_id: product.value.id_produk,
    toko_id: product.value.toko?.id
  }];
  localStorage.setItem("checkout_selection", JSON.stringify(buyNowItem));
  router.push({ name: "checkout" });
};

const showLoginModal = ref(false);

const openReviewForm = () => {
  const token = localStorage.getItem('authToken');
  if (!token) {
    showLoginModal.value = true;
    return;
  }
  router.push({ name: 'review.create', params: { type: 'product', id: route.params.id } });
};

const handleAddToCart = () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    showLoginModal.value = true;
    return;
  }

  if (!selectedVariant.value) {
    Swal.fire("Peringatan", "Silakan pilih varian terlebih dahulu.", "warning");
    return;
  }
  if (selectedVariant.value.stok <= 0) {
    Swal.fire("Stok Habis", "Stok habis. Tidak bisa menambahkan ke keranjang.", "error");
    return;
  }
  if (tokoData.value?.is_frozen) {
    Swal.fire("Toko Dibekukan", "Toko sedang dibekukan. Tidak dapat menambahkan ke keranjang.", "error");
    return;
  }
  if (
    user.value &&
    user.value.toko &&
    product.value.toko &&
    user.value.toko.id === product.value.toko.id
  ) {
    Swal.fire("Produk Sendiri", "Anda tidak dapat membeli produk dari toko Anda sendiri.", "info");
    return;
  }

  updateCartItem(selectedVariant.value.id_varian, quantity.value, {
    nama_varian: selectedVariant.value.nama_varian,
    harga: selectedVariant.value.harga,
    stok: selectedVariant.value.stok,
    product_name: product.value.nama_produk,
  });

  Swal.fire("Berhasil!", `Berhasil menambahkan ${quantity.value}x ${selectedVariant.value.nama_varian} ke keranjang!`, "success");
};

const goToStore = () => {
  if (product.value && product.value.toko) {
    router.push({
      name: "toko.detail",
      params: { id: product.value.toko.id },
    });
  }
};

const reviews = ref([]);

// Fetch Product Detail
const fetchProductDetail = async () => {
  try {
    const productId = route.params.id;
    const response = await axios.get(
      `http://127.0.0.1:8000/api/products/${productId}`
    );
    product.value = response.data.data;
    rating.value = response.data.rating;

    // Fetch Reviews
    const reviewsResponse = await axios.get(
      `http://127.0.0.1:8000/api/reviews/${productId}`
    );
    reviews.value = reviewsResponse.data.data;

    // Fetch Toko Data
    if (product.value.toko && product.value.toko.id) {
      const responseToko = await axios.get(
        `http://127.0.0.1:8000/api/toko/${product.value.toko.id}`
      );
      // API returns { data: [toko], ratingToko: {...} }
      if (responseToko.data.data && responseToko.data.data.length > 0) {
        tokoData.value = responseToko.data.data[0];
      }
      tokoRating.value = responseToko.data.ratingToko["rata-rata"];
    }

    // Set varian pertama sebagai default
    if (product.value.variant && product.value.variant.length > 0) {
      selectedVariant.value = product.value.variant[0];
    }
  } catch (err) {
    error.value = err;
    console.error("Error fetching product detail:", err);
  } finally {
    loading.value = false;
  }
};

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

onMounted(() => {
  fetchProductDetail();
  fetchUser();
});
</script>

<template>
  <main class="px-4 py-6 md:px-6 lg:px-8 max-w-7xl mx-auto w-full">
    <!-- Back Link -->
    <!-- Back Link -->
    <button
      @click="$router.back()"
      class="inline-block mb-4 text-gray-500 hover:text-pink-600 transition duration-200 text-sm font-medium"
    >
      &larr; Kembali
    </button>

    <div v-if="loading" class="text-center text-gray-600 py-12">
      Memuat detail produk...
    </div>

    <div v-else-if="error" class="text-center text-red-500 py-12">
      Terjadi kesalahan: {{ error.message }}
    </div>

    <div v-else-if="product" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- KOLOM KIRI: GALERI GAMBAR (3 cols) -->
      <div class="lg:col-span-3">
        <div class="sticky top-24">
          <!-- Main Image -->
          <div
            class="rounded-xl overflow-hidden border border-gray-200 mb-4 bg-gray-50 aspect-square flex items-center justify-center"
          >
            <img
              :src="mainImage"
              :alt="product.nama_produk"
              class="w-full h-full object-contain"
            />
          </div>
          <!-- Thumbnails -->
          <div
            class="grid grid-cols-4 gap-2"
            v-if="product.variant && product.variant.length > 0"
          >
            <div
              v-for="variant in product.variant"
              :key="variant.id_varian"
              class="aspect-square rounded-md border overflow-hidden cursor-pointer transition bg-gray-50"
              :class="
                selectedVariant?.id_varian === variant.id_varian
                  ? 'border-pink-500 ring-1 ring-pink-500'
                  : 'border-gray-200 hover:border-pink-300'
              "
              @click="selectVariant(variant)"
            >
              <img
                :src="
                  variant.gambar_varian
                    ? variant.gambar_varian.startsWith('http')
                      ? variant.gambar_varian
                      : `http://127.0.0.1:8000/storage/${variant.gambar_varian}`
                    : 'https://via.placeholder.com/100?text=No+Image'
                "
                class="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- KOLOM TENGAH: INFO PRODUK (6 cols) -->
      <div class="lg:col-span-6">
        <!-- Nama Produk -->
        <h1 class="text-2xl font-bold text-gray-900 mb-1">
          {{ product.nama_produk }}
        </h1>

        <!-- Nama Varian (Dynamic) -->
        <p
          v-if="selectedVariant"
          class="text-lg text-gray-600 font-medium mb-2"
        >
          {{ selectedVariant.nama_varian }}
        </p>

        <!-- Rating & Sold -->
        <div class="flex items-center text-sm mb-4">
          <span class="text-gray-600 mr-2"
            >Terjual
            <span class="text-gray-900 font-medium">{{
              rating.terjual
            }}</span></span
          >
          <span class="text-gray-300 mx-2">•</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4 text-yellow-400"
            fill="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              d="M12 2.5L14.8 9.2L21.5 12L14.8 14.8L12 21.5L9.2 14.8L2.5 12L9.2 9.2L12 2.5Z"
            />
          </svg>
          <span class="ml-1 font-medium text-gray-900">{{
            rating["rata-rata"]
          }}</span>
          <span class="text-gray-500 ml-1">({{ rating.jumlah }} rating)</span>
        </div>

        <!-- Harga -->
        <div class="mb-6">
          <h2 class="text-3xl font-bold text-pink-600">
            Rp {{ Number(selectedVariant?.harga ?? 0).toLocaleString("id-ID") }}
          </h2>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="flex space-x-8">
            <button
              @click="activeTab = 'detail'"
              :class="[
                'pb-3 text-sm font-bold border-b-2 transition-colors',
                activeTab === 'detail'
                  ? 'border-pink-600 text-pink-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700',
              ]"
            >
              Deskripsi Produk
            </button>
            <button
              @click="activeTab = 'info'"
              :class="[
                'pb-3 text-sm font-bold border-b-2 transition-colors',
                activeTab === 'info'
                  ? 'border-pink-600 text-pink-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700',
              ]"
            ></button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="mb-8 min-h-[200px]">
          <!-- Detail Tab -->
          <div v-if="activeTab === 'detail'">
            <div class="space-y-3 text-sm text-gray-700">
              <div
                class="mt-4 prose prose-sm max-w-none text-gray-700 relative whitespace-pre-line"
                :class="{
                  'max-h-24 overflow-hidden':
                    !isDescriptionExpanded &&
                    (product.deskripsi?.length || 0) > 300,
                }"
              >
                {{ product.deskripsi }}
                <div
                  v-if="
                    !isDescriptionExpanded &&
                    (product.deskripsi?.length || 0) > 300
                  "
                  class="absolute bottom-0 left-0 w-full h-12 bg-gradient-to-t from-white to-transparent"
                ></div>
              </div>
              <button
                v-if="(product.deskripsi?.length || 0) > 300"
                @click="isDescriptionExpanded = !isDescriptionExpanded"
                class="text-pink-600 font-semibold text-sm mt-2 hover:underline focus:outline-none"
              >
                {{
                  isDescriptionExpanded
                    ? "Lihat Lebih Sedikit"
                    : "Lihat Selengkapnya"
                }}
              </button>
            </div>
          </div>

          <!-- Spesifikasi Tab -->
          <div v-else-if="activeTab === 'spesifikasi'">
            <p class="text-gray-500 text-sm">
              Spesifikasi lengkap produk akan ditampilkan di sini.
            </p>
          </div>

          <!-- Info Penting Tab -->
          <div v-else-if="activeTab === 'info'">
            <p class="text-gray-500 text-sm">
              Informasi penting mengenai pengiriman dan garansi.
            </p>
          </div>
        </div>

        <!-- Shop Info -->
        <div class="flex items-center gap-4 pt-6 border-t border-gray-200">
          <div
            class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center overflow-hidden"
          >
            <img
              :src="storePhoto"
              alt="Store Logo"
              class="w-full h-full object-cover"
            />
          </div>
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-gray-900">
                {{ storeName }}
              </h3>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
              <div class="flex items-center">
                <svg
                  class="w-3 h-3 text-yellow-400 mr-1"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M12 2.5L14.8 9.2L21.5 12L14.8 14.8L12 21.5L9.2 14.8L2.5 12L9.2 9.2L12 2.5Z"
                  />
                </svg>
                {{ tokoRating }}
              </div>
            </div>
          </div>
          <button
            @click="goToStore"
            class="px-6 py-1.5 border border-pink-600 text-pink-600 font-semibold rounded-lg text-sm hover:bg-pink-50 transition"
          >
            Lihat Toko
          </button>
        </div>

        <!-- Product Reviews -->
        <div class="flex items-center justify-between mb-3">
        </div>
        <ProductReviews
          :reviews="reviews"
          :averageRating="rating?.['rata-rata'] || 0"
          :totalReviews="rating?.jumlah || 0"
        />
      </div>

      <!-- KOLOM KANAN: PURCHASE CARD (3 cols) -->
      <div class="lg:col-span-3">
        <div
          class="sticky top-24 border-2 border-pink-300 rounded-xl p-4 bg-white"
        >
          <h3 class="font-bold text-gray-900 mb-4">Pilih Jumlah</h3>

          <!-- Variant Selection -->
          <div
            v-if="product.variant && product.variant.length > 0"
            class="mb-4"
          >
            <p class="text-sm font-medium text-gray-700 mb-2">Pilih Varian:</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="variant in product.variant"
                :key="variant.id_varian"
                @click="selectVariant(variant)"
                :class="[
                  'px-3 py-1.5 rounded-md text-xs font-medium transition border',
                  selectedVariant?.id_varian === variant.id_varian
                    ? 'bg-pink-50 border-pink-500 text-pink-600'
                    : 'bg-white border-gray-300 text-gray-600 hover:border-pink-500',
                ]"
              >
                {{ variant.nama_varian }}
              </button>
            </div>
          </div>

          <!-- Selected Variant Details -->
          <div
            v-if="selectedVariant"
            class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-100"
          >
            <p class="text-sm font-bold text-gray-800 mb-1">
              {{ selectedVariant.nama_varian }}
            </p>
            <div class="flex justify-between items-center">
              <span class="text-pink-600 font-bold"
                >Rp
                {{
                  Number(selectedVariant.harga).toLocaleString("id-ID")
                }}</span
              >
            </div>
          </div>

          <!-- Quantity -->
          <!-- Quantity -->
          <div class="mb-6">
            <div class="flex items-center gap-3">
              <div class="flex items-center border border-gray-300 rounded-md">
                <button
                  @click="decrementQuantity"
                  class="px-3 py-1 text-gray-500 hover:bg-gray-100 disabled:opacity-50"
                  :disabled="quantity <= 1"
                >
                  –
                </button>
                <input
                  type="text"
                  v-model="quantity"
                  class="w-10 text-center text-sm border-none focus:ring-0 p-1"
                  readonly
                />
                <button
                  @click="incrementQuantity"
                  class="px-3 py-1 text-pink-600 hover:bg-gray-100 disabled:opacity-50"
                  :disabled="quantity >= (selectedVariant?.stok || 1)"
                >
                  +
                </button>
              </div>
              <span class="text-sm text-gray-500"
                >Stok Total:
                <span class="font-bold text-gray-900">{{
                  selectedVariant?.stok || 0
                }}</span></span
              >
            </div>
            <p
              v-if="selectedVariant?.stok === 0"
              class="text-red-500 text-sm font-medium mt-2"
            >
              Stok varian ini habis
            </p>
          </div>

          <!-- Subtotal -->
          <div class="flex justify-between items-center mb-6">
            <span class="text-gray-500 text-sm">Subtotal</span>
            <span class="font-bold text-lg text-gray-900"
              >Rp
              {{
                (Number(selectedVariant?.harga || 0) * quantity).toLocaleString(
                  "id-ID"
                )
              }}</span
            >
          </div>

          <!-- Frozen Warning -->
          <div
            v-if="tokoData?.is_frozen"
            class="mb-6 bg-red-50 border border-red-100 text-red-800 px-4 py-3 rounded-xl flex flex-col items-center text-center"
          >
            <div class="flex items-center gap-2 mb-1">
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
              <span class="font-bold text-sm">Toko Dibekukan</span>
            </div>
            <span class="font-light text-xs text-red-600 italic"
              >Pembelian tidak dapat dilakukan.</span
            >
          </div>

          <!-- Buttons -->
          <div class="space-y-3">
            <button
              v-if="!user || user.role !== 'admin'"
              @click="handleAddToCart"
              :disabled="tokoData?.is_frozen"
              :class="[
                'w-full font-bold py-2.5 rounded-lg transition flex items-center justify-center gap-2',
                tokoData?.is_frozen
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  : 'bg-pink-600 text-white hover:bg-pink-700',
              ]"
            >
              <span>+</span> Keranjang
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-gray-600 py-12">
      Produk tidak ditemukan.
    </div>
  </main>

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
        Tertarik dengan produk ini? Silahkan login terlebih dahulu.
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

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
::-webkit-scrollbar {
  display: none;
}
html {
  -ms-overflow-style: none; /* IE and Edge */
  scrollbar-width: none; /* Firefox */
}

/* Hover effect untuk tombol varian */
.bg-gray-100:hover {
  background-color: #f3f4f6;
}
</style>
