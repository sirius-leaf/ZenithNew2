<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";
import { useCartStore } from "@/stores/cartStore";
import ProductReviews from "@/components/product/ProductReviews.vue";

const route = useRoute();
const router = useRouter();
const { updateCartItem } = useCartStore();

const product = ref(null);
const toko = ref(null);
const rating = ref(null);
const loading = ref(true);
const error = ref(null);

// State untuk varian yang dipilih
const selectedVariant = ref(null);
const quantity = ref(1);
const activeTab = ref("detail");

// Computed: URL Gambar Utama
const mainImage = computed(() => {
  if (
    product.value &&
    product.value.variant &&
    product.value.variant.length > 0
  ) {
    const imagePath = product.value.variant[0].gambar_varian;
    if (imagePath) {
      return `http://127.0.0.1:8000/storage/${imagePath}`;
    }
  }
  return "https://via.placeholder.com/400x300/CCCCCC?text=No+Image";
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
    alert("Silakan pilih varian terlebih dahulu.");
    return;
  }
  if (selectedVariant.value.stok <= 0) {
    alert("Stok habis. Tidak bisa membeli.");
    return;
  }
  // Di sini bisa redirect ke halaman checkout dengan varian dan kuantitas
  alert(
    `Beli langsung ${quantity.value}x ${selectedVariant.value.nama_varian}`
  );
};

// Fungsi untuk tombol "Keranjang"
const handleAddToCart = () => {
  if (!selectedVariant.value) {
    alert("Silakan pilih varian terlebih dahulu.");
    return;
  }
  if (selectedVariant.value.stok <= 0) {
    alert("Stok habis. Tidak bisa menambahkan ke keranjang.");
    return;
  }

  updateCartItem(selectedVariant.value.id_varian, quantity.value, {
    nama_varian: selectedVariant.value.nama_varian,
    harga: selectedVariant.value.harga,
    stok: selectedVariant.value.stok,
    product_name: product.value.nama_produk,
  });

  alert(
    `Berhasil menambahkan ${quantity.value}x ${selectedVariant.value.nama_varian} ke keranjang!`
  );
};

// Fetch Product Detail
const fetchProductDetail = async () => {
  try {
    const productId = route.params.id;
    const response = await axios.get(
      `http://127.0.0.1:8000/api/products/${productId}`
    );
    product.value = response.data.data;
    rating.value = response.data.rating;

    const responseToko = await axios.get(
      `http://127.0.0.1:8000/api/toko/${product.value.toko.id}`
    );
    toko.value = responseToko.data.ratingToko["rata-rata"];

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

onMounted(fetchProductDetail);
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
          <div class="rounded-xl overflow-hidden border border-gray-200 mb-4">
            <img
              :src="mainImage"
              :alt="product.nama_produk"
              class="w-full h-auto object-contain bg-white"
            />
          </div>
          <!-- Thumbnails (Mocked for now) -->
          <div class="grid grid-cols-4 gap-2">
            <div
              v-for="i in 4"
              :key="i"
              class="aspect-square rounded-md border border-gray-200 overflow-hidden cursor-pointer hover:border-pink-500 transition"
              :class="{ 'border-pink-500 ring-1 ring-pink-500': i === 1 }"
            >
              <img :src="mainImage" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </div>

      <!-- KOLOM TENGAH: INFO PRODUK (6 cols) -->
      <div class="lg:col-span-6">
        <!-- Nama Produk -->
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
          {{ product.nama_produk }}
        </h1>

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
            viewBox="0 0 20 20"
          >
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z"
            />
          </svg>
          <span class="ml-1 font-medium text-gray-900">{{
            rating["rata-rata"]
          }}</span>
          <span class="text-gray-500 ml-1">({{ rating.jumlah }} rating)</span>
        </div>

        <!-- Harga -->
        <div class="mb-6">
          <h2 class="text-3xl font-bold text-gray-900">
            Rp {{ (product.variant?.[0]?.harga ?? 0).toLocaleString("id-ID") }}
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
              Detail Produk
            </button>
            <button
              @click="activeTab = 'spesifikasi'"
              :class="[
                'pb-3 text-sm font-bold border-b-2 transition-colors',
                activeTab === 'spesifikasi'
                  ? 'border-pink-600 text-pink-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700',
              ]"
            >
              Spesifikasi
            </button>
            <button
              @click="activeTab = 'info'"
              :class="[
                'pb-3 text-sm font-bold border-b-2 transition-colors',
                activeTab === 'info'
                  ? 'border-pink-600 text-pink-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700',
              ]"
            >
              Info Penting
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="mb-8 min-h-[200px]">
          <!-- Detail Tab -->
          <div v-if="activeTab === 'detail'">
            <div class="space-y-3 text-sm text-gray-700">
              <p>
                <span class="text-gray-500 w-32 inline-block">Kondisi:</span>
                <span class="font-medium text-gray-900">Baru</span>
              </p>
              <p>
                <span class="text-gray-500 w-32 inline-block"
                  >Min. Pemesanan:</span
                >
                <span class="font-medium text-gray-900">1 Buah</span>
              </p>
              <p>
                <span class="text-gray-500 w-32 inline-block">Etalase:</span>
                <span class="font-medium text-pink-600 font-bold"
                  >GAMING LAPTOP</span
                >
              </p>

              <div class="mt-4 prose prose-sm max-w-none text-gray-700">
                {{ product.deskripsi }}
              </div>
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
              src="@/assets/logo.png"
              alt="Zenith Logo"
              class="w-full h-full object-cover"
            />
          </div>
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-gray-900">
                {{ product.toko.toko_name }}
              </h3>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
              <div class="flex items-center">
                <svg
                  class="w-3 h-3 text-yellow-400 mr-1"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z"
                  />
                </svg>
                {{ toko }}
              </div>
            </div>
          </div>
          <button
            @click="
              router.push({
                name: 'toko.detail',
                params: { id: product.toko.id },
              })
            "
            class="px-6 py-1.5 border border-pink-600 text-pink-600 font-semibold rounded-lg text-sm hover:bg-pink-50 transition"
          >
            Lihat Toko
          </button>
        </div>
      </div>

      <!-- KOLOM KANAN: PURCHASE CARD (3 cols) -->
      <div class="lg:col-span-3">
        <div
          class="sticky top-24 border border-gray-200 rounded-xl p-4 shadow-sm bg-white"
        >
          <h3 class="font-bold text-gray-900 mb-4">Atur jumlah dan catatan</h3>

          <!-- Variant Selection (Simplified for UI match) -->
          <div
            v-if="product.variant && product.variant.length > 0"
            class="mb-4"
          >
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

          <!-- Quantity -->
          <div class="flex items-center gap-3 mb-6">
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

          <!-- Subtotal -->
          <div class="flex justify-between items-center mb-6">
            <span class="text-gray-500 text-sm">Subtotal</span>
            <span class="font-bold text-lg text-gray-900"
              >Rp
              {{
                ((selectedVariant?.harga || 0) * quantity).toLocaleString(
                  "id-ID"
                )
              }}</span
            >
          </div>

          <!-- Buttons -->
          <div class="space-y-3">
            <button
              @click="handleAddToCart"
              class="w-full bg-pink-600 text-white font-bold py-2.5 rounded-lg hover:bg-pink-700 transition flex items-center justify-center gap-2"
            >
              <span>+</span> Keranjang
            </button>
            <button
              @click="handleBuyNow"
              class="w-full border border-pink-600 text-pink-600 font-bold py-2.5 rounded-lg hover:bg-pink-50 transition"
            >
              Beli Langsung
            </button>
          </div>

          <!-- Actions -->
          <div
            class="flex justify-between mt-6 pt-4 border-t border-gray-100 text-sm font-medium text-gray-700"
          >
            <button class="flex items-center gap-1 hover:text-pink-600">
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
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"
                />
              </svg>
              Chat
            </button>
            <button class="flex items-center gap-1 hover:text-pink-600">
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
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                />
              </svg>
              Wishlist
            </button>
            <button class="flex items-center gap-1 hover:text-pink-600">
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
                  d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                />
              </svg>
              Share
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-gray-600 py-12">
      Produk tidak ditemukan.
    </div>
  </main>
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
