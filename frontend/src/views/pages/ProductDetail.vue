<script setup>
import { ref, onMounted, computed } from "vue"; // <-- Tambahkan computed
import axios from "axios";
import { useRoute } from "vue-router";
import { useCartStore } from "@/stores/cartStore";

const route = useRoute();
const { updateCartItem, cartItems } = useCartStore();

const product = ref(null);
const loading = ref(true);
const loggedIn = ref(localStorage.getItem("authToken") != null);
const error = ref(null);
const successMessage = ref(null);
const quantities = ref({});
const canReview = ref(false);
const checkingReviewPermission = ref(false);

// 1. COMPUTED PROPERTY untuk mendapatkan URL Gambar Utama
const mainImage = computed(() => {
  // Cek apakah produk dan varian pertama ada
  if (
    product.value &&
    product.value.variant &&
    product.value.variant.length > 0
  ) {
    const imagePath = product.value.variant[0].gambar_varian;
    // Kita konstruksi URL storage lengkap:
    if (imagePath) {
      return `http://127.0.0.1:8000/storage/${imagePath}`;
    }
  }
  // Default gambar jika tidak ada varian atau gambar
  return "https://via.placeholder.com/400x300/CCCCCC?text=No+Image";
});

const initializeQuantities = (variants) => {
  const q = {};
  variants.forEach((variant) => {
    q[variant.id_varian] = 1;
  });
  quantities.value = q;
};

const handleAddToCart = (variant) => {
  const kuantitas = quantities.value[variant.id_varian];

  if (kuantitas <= 0 || kuantitas > variant.stok) {
    alert("Kuantitas tidak valid atau melebihi stok.");
    return;
  }

  updateCartItem(variant.id_varian, kuantitas, {
    nama_varian: variant.nama_varian,
    harga: variant.harga,
    stok: variant.stok,
    product_name: product.value.nama_produk,
  });

  successMessage.value = `Berhasil menambahkan ${kuantitas}x ${variant.nama_variant} ke keranjang!`;
  setTimeout(() => (successMessage.value = null), 3000);
};

const fetchProductDetail = async () => {
  try {
    const productId = route.params.id;
    const response = await axios.get(
      `http://127.0.0.1:8000/api/products/${productId}`
    );

    product.value = response.data;
    initializeQuantities(response.data.variant);
    console.log();
  } catch (err) {
    error.value = err;
    console.error("Error fetching product detail:", err);
  } finally {
    loading.value = false;
  }
};

// Tambahkan reactive state untuk ulasan
const reviewForm = ref({
  rating: 5, // default bintang
  komentar: "",
});

const reviewMessage = ref(null);
const reviewError = ref(null);

// State untuk ulasan user (jika ada)
const userReview = ref(null);

var isEditing = false;

// Fetch ulasan user untuk produk ini
const fetchUserReview = async () => {
  try {
    const token = localStorage.getItem("authToken");
    if (!token) return;

    const response = await axios.get(
      `http://127.0.0.1:8000/api/review/product/${product.value.id_produk}`
    );

    userReview.value = response.data; // bisa null atau object review
    isEditing = Object.keys(userReview.value).length > 0;

    // Jika ada ulasan, isi form dengan data tersebut
    if (userReview.value && isEditing) {
      reviewForm.value.rating = userReview.value.rating;
      reviewForm.value.komentar = userReview.value.komentar;
    }
  } catch (err) {
    console.warn("Gagal memuat ulasan Anda:", err);
    // Tidak perlu error fatal — biarkan tetap tampilkan form
  }
};

const checkReviewPermission = async () => {
  const token = localStorage.getItem("authToken");
  if (!token || !product.value?.id_produk) return;

  checkingReviewPermission.value = true;
  try {
    const response = await axios.get(
      `http://127.0.0.1:8000/api/review/can-review/${product.value.id_produk}`
    );
    canReview.value = response.data.can_review;
    console.log(canReview.value);
  } catch (err) {
    console.warn("Gagal memeriksa izin ulasan:", err);
    canReview.value = false;
  } finally {
    checkingReviewPermission.value = false;
  }
};

const submitReview = async () => {
  reviewError.value = null;
  reviewMessage.value = null;

  if (!reviewForm.value.komentar.trim()) {
    reviewError.value = "Komentar tidak boleh kosong.";
    return;
  }

  const token = localStorage.getItem("authToken");
  if (!token) {
    reviewError.value = "Silakan login untuk memberi ulasan.";
    return;
  }

  try {
    if (isEditing) {
      // ✏️ Mode EDIT
      await axios.put(
        `http://127.0.0.1:8000/api/review/${userReview.value.id_ulasan}`,
        {
          rating: reviewForm.value.rating,
          komentar: reviewForm.value.komentar,
        }
      );
      reviewMessage.value = "Ulasan berhasil diperbarui!";
    } else {
      // ➕ Mode TAMBAH
      await axios.post("http://127.0.0.1:8000/api/review", {
        rating: reviewForm.value.rating,
        komentar: reviewForm.value.komentar,
        id_produk: product.value.id_produk,
      });
      reviewMessage.value = "Ulasan berhasil dikirim!";
      // Setelah sukses kirim, update userReview agar berpindah ke mode edit
      await fetchUserReview(); // opsional: refresh data ulasan
    }

    // Reset pesan setelah 3 detik
    setTimeout(() => {
      reviewMessage.value = null;
    }, 3000);
  } catch (err) {
    console.error("Error:", err);
    reviewError.value = err.response?.data?.message || "Terjadi kesalahan.";
  }
};

const deleteReview = async () => {
  if (!confirm("Apakah Anda yakin ingin menghapus ulasan ini?")) {
    return;
  }

  const token = localStorage.getItem("authToken");
  if (!token || !userReview.value) return;

  try {
    await axios.delete(
      `http://127.0.0.1:8000/api/review/${userReview.value.id_ulasan}`
    );

    // Reset state
    userReview.value = null;
    reviewForm.value.rating = 5;
    reviewForm.value.komentar = "";
    reviewMessage.value = "Ulasan berhasil dihapus.";

    // Sembunyikan pesan setelah 3 detik
    setTimeout(() => {
      reviewMessage.value = null;
    }, 3000);
  } catch (err) {
    console.error("Gagal menghapus ulasan:", err);
    reviewError.value =
      err.response?.data?.message || "Gagal menghapus ulasan.";
  }
};

onMounted(() => {
  fetchProductDetail().then(() => {
    // Setelah produk dimuat, cek ulasan user
    if (localStorage.getItem("authToken")) {
      fetchUserReview();
      checkReviewPermission();
    }
  });
});
</script>

<template>
  <div class="container mx-auto p-4">
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

    <div
      v-else-if="product"
      class="bg-white rounded-lg shadow-lg p-8 grid grid-cols-1 md:grid-cols-12 gap-8"
    >
      <div class="md:col-span-4">
        <img
          :src="mainImage"
          :alt="product.nama_produk"
          class="w-full rounded-lg shadow-md object-cover h-auto"
        />
        <p class="text-sm text-gray-500 mt-2 text-center">
          Gambar dari varian pertama
        </p>
      </div>

      <div class="md:col-span-8">
        <h1 class="text-4xl font-bold mb-2 text-gray-900">
          {{ product.nama_produk }}
        </h1>
        <p class="text-muted mb-4">Merek: {{ product.merek }}</p>
        <p class="text-lg text-gray-700 mb-6">{{ product.deskripsi }}</p>

        <hr class="my-6 border-gray-300" />
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Pilih Varian:</h2>

        <div
          v-if="successMessage"
          class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"
        >
          {{ successMessage }}
        </div>

        <div
          v-if="product.variant && product.variant.length > 0"
          class="space-y-4"
        >
          <div
            v-for="variant in product.variant"
            :key="variant.id_varian"
            class="bg-gray-50 p-4 rounded-md shadow-sm border border-gray-200 flex justify-between items-center"
          >
            <div>
              <h3 class="text-xl font-medium mb-1 text-gray-800">
                {{ variant.nama_variant }}
              </h3>
              <p class="text-gray-600 mb-1">
                Harga:
                <span class="font-bold text-green-700"
                  >Rp {{ variant.harga.toLocaleString("id-ID") }}</span
                >
              </p>
              <p class="text-sm text-red-500">Stok: {{ variant.stok }}</p>
            </div>

            <div class="flex items-center space-x-3">
              <div v-if="variant.stok > 0" class="flex items-center">
                <input
                  type="number"
                  v-model.number="quantities[variant.id_varian]"
                  :min="1"
                  :max="variant.stok"
                  class="w-20 text-center border border-gray-300 rounded-l-md py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <button
                  @click="handleAddToCart(variant)"
                  class="bg-pink-600 text-white px-4 py-2 rounded-r-md hover:bg-pink-700 transition duration-150 text-sm font-medium"
                >
                  + Keranjang
                </button>
              </div>
              <div v-else>
                <span
                  class="text-sm font-semibold text-red-600 bg-red-100 px-3 py-1 rounded-full"
                  >Stok Habis</span
                >
              </div>
            </div>
          </div>
        </div>
        <div v-else>
          <p class="text-gray-600">Belum ada varian untuk produk ini.</p>
        </div>

        <!-- Bagian Ulasan (Rating & Komentar) -->
        <div
          v-if="loggedIn && canReview"
          class="mt-12 pt-8 border-t border-gray-300"
        >
          <h2 class="text-2xl font-semibold mb-4 text-gray-800">
            {{ isEditing ? "Edit Ulasan Anda" : "Beri Ulasan" }}
          </h2>

          <div
            v-if="reviewMessage"
            class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"
          >
            {{ reviewMessage }}
          </div>
          <div
            v-if="reviewError"
            class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"
          >
            {{ reviewError }}
          </div>

          <form @submit.prevent="submitReview" class="space-y-4">
            <!-- Rating (1–5 bintang via radio) -->
            <div>
              <label class="block text-gray-700 font-medium mb-2"
                >Rating:</label
              >
              <div class="flex items-center space-x-2">
                <label
                  v-for="star in 5"
                  :key="star"
                  class="flex cursor-pointer items-center"
                >
                  <input
                    type="radio"
                    v-model="reviewForm.rating"
                    :value="star"
                    class="sr-only"
                  />
                  <span
                    class="text-2xl"
                    :class="
                      reviewForm.rating >= star
                        ? 'text-yellow-500'
                        : 'text-gray-300'
                    "
                  >
                    ★
                  </span>
                </label>
              </div>
            </div>

            <!-- Komentar -->
            <div>
              <label for="komentar" class="block text-gray-700 font-medium mb-2"
                >Komentar:</label
              >
              <textarea
                id="komentar"
                v-model="reviewForm.komentar"
                rows="4"
                class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                placeholder="Tulis ulasan Anda di sini..."
              ></textarea>
            </div>

            <!-- Tombol Submit -->
            <button
              type="submit"
              class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition duration-150 font-medium"
            >
              {{ isEditing ? "Simpan Perubahan" : "Kirim Ulasan" }}
            </button>

            <div v-if="isEditing" class="mt-2">
              <button
                type="button"
                @click="deleteReview"
                class="text-red-600 hover:text-red-800 font-medium text-sm"
              >
                Hapus Ulasan
              </button>
            </div>
          </form>
        </div>

        <!-- Daftar Semua Ulasan -->
        <div class="mt-12 pt-8 border-t border-gray-300">
          <h2 class="text-2xl font-semibold mb-4 text-gray-800">
            Ulasan Pelanggan ({{ product.reviews.length }})
          </h2>

          <div v-if="loading" class="text-gray-500">Memuat ulasan...</div>

          <div v-else-if="product.reviews.length === 0" class="text-gray-500">
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
                  <h3 class="font-medium text-gray-900">
                    {{ review.user.name }}
                  </h3>
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
    </div>

    <div v-else class="text-center text-gray-600 py-12">
      Produk tidak ditemukan.
    </div>
  </div>
</template>
