<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";
import { useCartStore } from "@/stores/cartStore";

const route = useRoute();
const { updateCartItem } = useCartStore();

const product = ref(null);
const loading = ref(true);
const error = ref(null);

// State untuk varian yang dipilih
const selectedVariant = ref(null);
const quantity = ref(1);

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
  alert(`Beli langsung ${quantity.value}x ${selectedVariant.value.nama_varian}`);
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

  alert(`Berhasil menambahkan ${quantity.value}x ${selectedVariant.value.nama_varian} ke keranjang!`);
};

// Fetch Product Detail
const fetchProductDetail = async () => {
  try {
    const productId = route.params.id;
    const response = await axios.get(
      `http://127.0.0.1:8000/api/products/${productId}`
    );
    product.value = response.data;

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
    <router-link
      to="/"
      class="inline-block mb-4 text-indigo-600 hover:text-indigo-800 transition duration-200"
    >
      &larr; Kembali ke Daftar Produk
    </router-link>

    <div v-if="loading" class="text-center text-gray-600 py-12">
      Memuat detail produk...
    </div>

    <div v-else-if="error" class="text-center text-red-500 py-12">
      Terjadi kesalahan saat memuat detail produk: {{ error.message }}
    </div>

    <div v-else-if="product" class="bg-white rounded-lg shadow-lg p-6 md:p-8">
      <!-- Main Content -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        <!-- Kolom Kiri: Gambar Produk -->
        <div class="md:col-span-5">
          <img
            :src="mainImage"
            :alt="product.nama_produk"
            class="w-full h-auto object-contain rounded-lg shadow-md"
          />
          <p class="text-xs text-gray-500 mt-2 text-center">Gambar utama</p>
        </div>

        <!-- Kolom Kanan: Detail Produk -->
        <div class="md:col-span-7 space-y-6">
          <!-- Nama Produk & Rating -->
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ product.nama_produk }}</h1>
            <div class="flex items-center mt-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z" />
              </svg>
              <span class="ml-1 text-sm text-gray-600">4.8</span>
            </div>
          </div>

          <!-- Harga -->
          <div>
            <p class="text-3xl font-bold text-pink-600">
              Rp {{ (product.variant?.[0]?.harga ?? 0).toLocaleString('id-ID') }}
            </p>
          </div>

          <!-- Pilih Warna -->
          <div v-if="product.variant && product.variant.length > 0">
            <h3 class="font-semibold text-gray-800 mb-2">Pilih Warna:</h3>
            <div class="flex flex-wrap gap-2 mb-4">
              <button
                v-for="variant in product.variant"
                :key="variant.id_varian"
                @click="selectVariant(variant)"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium transition',
                  selectedVariant?.id_varian === variant.id_varian
                    ? 'bg-pink-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200'
                ]"
              >
                {{ variant.nama_varian }}
              </button>
            </div>
          </div>

          <!-- Pilih Kategori (sebelumnya: Kapasitas) -->
          <div v-if="product.variant && product.variant.length > 0">
            <h3 class="font-semibold text-gray-800 mb-2">Pilih Kategori:</h3>
            <div class="flex flex-wrap gap-2 mb-4">
              <button
                v-for="variant in product.variant"
                :key="variant.id_varian"
                @click="selectVariant(variant)"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium transition',
                  selectedVariant?.id_varian === variant.id_varian
                    ? 'bg-pink-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200'
                ]"
              >
                {{ variant.nama_varian }}
              </button>
            </div>
          </div>

          <!-- Kuantitas & Stok -->
          <div class="flex items-center gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kuantitas:</label>
              <div class="flex items-center border border-gray-300 rounded-md">
                <button
                  @click="decrementQuantity"
                  class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition"
                  :disabled="quantity <= 1"
                >
                  –
                </button>
                <span class="w-12 text-center py-2">{{ quantity }}</span>
                <button
                  @click="incrementQuantity"
                  class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition"
                  :disabled="quantity >= (selectedVariant?.stok || 1)"
                >
                  +
                </button>
              </div>
            </div>
            <div class="mt-6">
              <span class="inline-block px-3 py-1 text-sm font-medium text-gray-700">
                Stok: {{ selectedVariant?.stok || 0 }}
              </span>
            </div>
          </div>

          <!-- Tombol Aksi -->
          <div class="flex gap-3 mt-6">
            <button
              @click="handleBuyNow"
              class="flex-1 bg-pink-600 text-white px-6 py-3 rounded-md font-semibold hover:bg-pink-700 transition"
            >
              Beli Langsung
            </button>
            <button
              @click="handleAddToCart"
              class="flex-1 bg-pink-100 text-pink-700 px-6 py-3 rounded-md font-semibold hover:bg-pink-200 transition"
            >
              Keranjang
            </button>
          </div>
        </div>
      </div>

      <!-- Ulasan Pelanggan -->
      <div class="mt-12 pt-8 border-t border-gray-300">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Ulasan Pelanggan ({{ product.reviews.length }})</h2>
        <div v-if="product.reviews.length === 0" class="text-gray-500">
          Belum ada ulasan untuk produk ini.
        </div>
        <div v-else class="space-y-6">
          <div
            v-for="review in product.reviews"
            :key="review.id"
            class="bg-gray-50 p-4 rounded-lg border border-gray-200"
          >
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-medium text-gray-900">{{ review.user.name }}</h3>
                <p class="text-sm text-gray-500">{{ review.created_at }}</p>
              </div>
              <div class="flex">
                <span
                  v-for="star in 5"
                  :key="star"
                  class="text-xl"
                  :class="
                    review.rating >= star
                      ? 'text-yellow-500'
                      : 'text-gray-300'
                  "
                >
                  ★
                </span>
              </div>
            </div>
            <p class="mt-3 text-gray-700 italic">"{{ review.komentar }}"</p>
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
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;     /* Firefox */
}

/* Hover effect untuk tombol varian */
.bg-gray-100:hover {
  background-color: #f3f4f6;
}
</style>