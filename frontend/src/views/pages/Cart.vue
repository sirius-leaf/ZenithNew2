<!-- src/components/user/Keranjang.vue -->
<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4">
    <!-- Guest View -->
    <div v-if="!isLoggedIn" class="max-w-md mx-auto mt-20 text-center">
      <div class="bg-white rounded-xl shadow-md p-8">
        <div class="mb-6">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-20 w-20 text-pink-200 mx-auto"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
            />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-3">Akses Dibatasi</h2>
        <p class="text-gray-600 mb-8">
          Login terlebih dahulu agar bisa belanja!
        </p>
        <router-link
          to="/login"
          class="inline-block w-full px-6 py-3 bg-pink-600 text-white font-semibold rounded-lg hover:bg-pink-700 transition shadow-lg shadow-pink-200"
        >
          Login Sekarang
        </router-link>
      </div>
    </div>

    <!-- User Cart View -->
    <div
      v-else
      class="bg-white rounded-xl shadow-md p-6 animate-fade-in max-w-6xl mx-auto"
    >
      <!-- Header -->
      <h1 class="text-2xl font-bold text-pink-600 mb-6">KERANJANG</h1>

      <!-- Error Handling -->
      <div
        v-if="apiError"
        class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm"
      >
        <strong>Error:</strong> {{ apiError }}
        <p class="mt-1">Harap hapus atau kurangi item yang bermasalah.</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-10 text-gray-500">
        Memuat detail keranjang...
      </div>

      <!-- Keranjang Kosong -->
      <div
        v-else-if="!cartSummary || cartSummary.length === 0"
        class="text-center py-10"
      >
        <p class="text-lg text-gray-600">Keranjang Anda masih kosong.</p>
        <router-link
          to="/dashboard"
          class="mt-4 inline-block text-blue-600 hover:underline font-medium"
        >
          Lihat Semua Produk
        </router-link>
      </div>

      <!-- Layout 2 Kolom -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Daftar Produk -->
        <div class="lg:col-span-2">
          <!-- Pilih Semua -->
          <div class="flex items-center mb-5">
            <input
              v-model="selectAll"
              type="checkbox"
              class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-2 focus:ring-pink-500"
            />
            <label class="ml-2 text-base font-medium text-gray-800"
              >Pilih Semua</label
            >
          </div>

          <!-- Daftar Produk Grouped by Store -->
          <div class="space-y-6">
            <div
              v-for="(items, storeName) in groupedCartItems"
              :key="storeName"
              class="bg-white border border-gray-200 rounded-lg overflow-hidden"
            >
              <!-- Store Header -->
              <div
                class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center gap-2"
              >
                <div
                  class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4"
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
                </div>
                <h3 class="font-bold text-gray-800">{{ storeName }}</h3>
              </div>

              <!-- Items -->
              <div class="divide-y divide-gray-100">
                <div
                  v-for="item in items"
                  :key="item.variant.id_varian"
                  class="flex items-center p-4 hover:bg-pink-50 transition-colors"
                >
                  <!-- Checkbox -->
                  <input
                    v-model="checkedItems[item.variant.id_varian]"
                    type="checkbox"
                    class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-2 focus:ring-pink-500 mr-4 flex-shrink-0"
                  />

                  <!-- Gambar -->
                  <img
                    :src="getProductImage(item.variant)"
                    :alt="item.variant.product.nama_produk"
                    class="w-16 h-16 object-cover rounded-md mr-4 flex-shrink-0"
                  />

                  <!-- Detail -->
                  <div class="flex-1 min-w-0">
                    <p class="text-gray-800 font-medium text-sm line-clamp-2">
                      {{ item.variant.product.nama_produk }}
                    </p>
                    <p class="text-gray-500 text-xs mt-1">
                      {{ item.variant.nama_varian }}
                    </p>
                  </div>

                  <!-- Kolom Kanan -->
                  <div class="flex items-center gap-3 ml-4 flex-shrink-0">
                    <!-- Harga -->
                    <p class="font-bold text-gray-800 text-sm">
                      {{ formatCurrency(item.variant.harga) }}
                    </p>

                    <!-- Quantity Controls -->
                    <div class="flex items-center gap-1">
                      <button
                        @click="updateQuantity(item.variant.id_varian, -1)"
                        :disabled="item.kuantitas <= 1"
                        class="w-7 h-7 flex items-center justify-center bg-blue-900 text-white text-xs rounded hover:bg-blue-800 disabled:bg-gray-400 transition-colors"
                      >
                        -
                      </button>
                      <span
                        class="w-8 text-center text-xs font-medium text-gray-800"
                      >
                        {{ item.kuantitas }}
                      </span>
                      <button
                        @click="updateQuantity(item.variant.id_varian, 1)"
                        class="w-7 h-7 flex items-center justify-center bg-blue-900 text-white text-xs rounded hover:bg-blue-800 transition-colors"
                      >
                        +
                      </button>
                    </div>

                    <!-- Tombol Hapus -->
                    <button
                      @click="removeItem(item.variant.id_varian)"
                      class="w-7 h-7 flex items-center justify-center text-red-600 hover:text-red-800 transition-colors"
                      title="Hapus item"
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
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Ringkasan -->
        <div
          class="bg-pink-50 rounded-lg border border-pink-200 p-5 h-fit lg:sticky lg:top-4"
        >
          <h2 class="text-lg font-bold text-blue-900 mb-4">Ringkasan Order</h2>

          <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
            <div
              v-for="item in filteredCartForCheckout"
              :key="item.variant.id_varian"
              class="border-b border-pink-200 pb-3"
            >
              <div class="flex justify-between text-sm">
                <div class="font-medium text-gray-800">
                  {{ item.variant.product.nama_produk }}
                </div>
                <div class="text-gray-600">
                  {{ formatCurrency(item.variant.harga) }}
                </div>
              </div>
              <div class="text-xs text-gray-600 mt-1">
                {{ item.variant.nama_varian }}
              </div>
              <div class="text-xs text-gray-600">
                Jumlah: {{ item.kuantitas }}
              </div>
              <div class="text-sm font-bold text-blue-900 mt-1">
                {{ formatCurrency(item.subtotal) }}
              </div>
            </div>
          </div>

          <div class="border-t border-pink-300 pt-4 mt-4">
            <div
              class="flex justify-between mb-2 text-sm font-medium text-gray-700"
            >
              <span>Total ({{ totalCheckedItems }} item)</span>
              <span>{{ formatCurrency(filteredTotalPrice) }}</span>
            </div>

            <button
              @click="goToCheckout"
              :disabled="
                filteredCartForCheckout.length === 0 || loading || apiError
              "
              class="w-full py-2.5 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition-colors text-sm disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              Buat Pesanan ({{ totalCheckedItems }})
            </button>

            <router-link
              to="/product"
              class="block mt-3 text-center text-sm text-blue-600 hover:underline"
            >
              Lanjutkan Belanja
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Peringatan Multi-Toko -->
    <Transition name="modal-fade">
      <div
        v-if="showMultiStoreModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click="showMultiStoreModal = false"
      >
        <Transition name="modal-slide">
          <div
            class="w-full max-w-md bg-white rounded-xl shadow-xl overflow-hidden"
            @click.stop
          >
            <div class="p-6 text-center">
              <div
                class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-8 h-8"
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
              </div>
              <h3 class="text-xl font-bold text-gray-800 mb-2">
                Checkout Dibatasi
              </h3>
              <p class="text-gray-600 mb-6">
                Anda hanya dapat melakukan checkout untuk produk dari
                <strong>satu toko</strong> dalam satu transaksi. Silakan pilih
                produk dari satu toko saja.
              </p>
              <button
                @click="showMultiStoreModal = false"
                class="px-6 py-2.5 bg-blue-900 text-white font-medium rounded-lg hover:bg-blue-800 transition-colors"
              >
                Mengerti
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "@/stores/cartStore";
import axios from "axios";

const router = useRouter();
const { cartItems, removeCartItem, updateCartItem } = useCartStore();

const loading = ref(true);
const cartSummary = ref([]);
const apiError = ref(null);
const checkedItems = ref({}); // default: semua false
const isLoggedIn = ref(false);
const showMultiStoreModal = ref(false);

const checkLogin = () => {
  const token = localStorage.getItem("authToken");
  isLoggedIn.value = !!token;
};

const filteredCartForCheckout = computed(() => {
  return cartSummary.value.filter(
    (item) => checkedItems.value[item.variant.id_varian]
  );
});

const groupedCartItems = computed(() => {
  const groups = {};
  cartSummary.value.forEach((item) => {
    const storeName = item.variant.product.toko?.toko_name || "Toko";
    if (!groups[storeName]) {
      groups[storeName] = [];
    }
    groups[storeName].push(item);
  });
  return groups;
});

const filteredTotalPrice = computed(() => {
  return filteredCartForCheckout.value.reduce(
    (sum, item) => sum + item.subtotal,
    0
  );
});

const totalCheckedItems = computed(() => {
  return filteredCartForCheckout.value.reduce(
    (sum, item) => sum + item.kuantitas,
    0
  );
});

const selectAll = computed({
  get: () =>
    cartSummary.value.length > 0 &&
    cartSummary.value.every(
      (item) => checkedItems.value[item.variant.id_varian]
    ),
  set: (value) => {
    cartSummary.value.forEach((item) => {
      checkedItems.value[item.variant.id_varian] = value;
    });
  },
});

// ambil gambar
const getProductImage = (variant) => {
  if (variant?.gambar_varian) {
    return `http://127.0.0.1:8000/storage/${variant.gambar_varian}`;
  }
  return "https://via.placeholder.com/144x161/CCCCCC?text=No+Image";
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
};

const fetchCartPreview = async () => {
  apiError.value = null;
  loading.value = true;

  const cartDataForApi = cartItems.value.map((item) => ({
    id_varian: item.id_varian,
    kuantitas: item.kuantitas,
  }));

  if (cartDataForApi.length === 0) {
    cartSummary.value = [];
    loading.value = false;
    return;
  }

  try {
    const token = localStorage.getItem("authToken");
    const response = await axios.post(
      "http://127.0.0.1:8000/api/order/preview",
      { cartItems: cartDataForApi },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    cartSummary.value = response.data.cartItems;

    const newChecked = {};
    response.data.cartItems.forEach((item) => {
      newChecked[item.variant.id_varian] = false; // <-- ini kunci perbaikan Anda
    });
    checkedItems.value = newChecked;
  } catch (error) {
    // Handle 422 Validation Errors (e.g. Item deleted from DB)
    if (error.response?.status === 422 && error.response.data?.errors) {
      const errors = error.response.data.errors;
      let itemsRemoved = false;

      // Parse errors like "cartItems.0.id_varian"
      Object.keys(errors).forEach((key) => {
        const match = key.match(/cartItems\.(\d+)\.id_varian/);
        if (match) {
          const index = parseInt(match[1]);
          if (cartDataForApi[index]) {
            const variantIdToRemove = cartDataForApi[index].id_varian;
            removeCartItem(variantIdToRemove);
            itemsRemoved = true;
          }
        }
      });

      if (itemsRemoved) {
        // alert('Beberapa item di keranjang Anda tidak lagi tersedia dan telah dihapus.')
        // Retry fetch after removal
        return fetchCartPreview();
      }
    }

    // Handle 400 Stock Errors
    if (error.response?.status === 400 && error.response.data?.variant_id) {
      const variantId = error.response.data.variant_id;
      const item = cartItems.value.find((i) => i.id_varian === variantId);
      if (item?.variantDetail) {
        removeCartItem(variantId);
        // Gunakan pesan dari backend jika ada, atau default ke pesan stok
        const msg =
          error.response.data.message ||
          `Stok untuk produk "${item.variantDetail.product_name} (${item.variantDetail.nama_varian})" tidak mencukupi.`;
        alert(`⚠️ ${msg} Item dihapus dari keranjang.`);
        return fetchCartPreview();
      }
    }
    apiError.value =
      error.response?.data?.message || "Gagal memuat data keranjang.";
  } finally {
    loading.value = false;
  }
};

const updateQuantity = (id_varian, delta) => {
  const item = cartItems.value.find((i) => i.id_varian === id_varian);
  if (!item) return;
  const newQty = item.kuantitas + delta;
  if (newQty < 1) return;

  updateCartItem(id_varian, delta);

  const cartItem = cartSummary.value.find(
    (i) => i.variant.id_varian === id_varian
  );
  if (cartItem) {
    cartItem.kuantitas = newQty;
    cartItem.subtotal = cartItem.variant.harga * newQty;
  }
};

const removeItem = (id_varian) => {
  if (confirm("Yakin ingin menghapus item ini?")) {
    removeCartItem(id_varian);
    delete checkedItems.value[id_varian];
    fetchCartPreview();
  }
};

const goToCheckout = () => {
  if (filteredCartForCheckout.value.length === 0) {
    alert("Pilih minimal 1 produk untuk checkout");
    return;
  }

  // Check for multiple stores
  const stores = new Set();
  filteredCartForCheckout.value.forEach((item) => {
    stores.add(item.variant.product.toko?.toko_name || "Toko");
  });

  if (stores.size > 1) {
    showMultiStoreModal.value = true;
    return;
  }

  const selectedItems = filteredCartForCheckout.value.map((item) => ({
    id_varian: item.variant.id_varian,
    kuantitas: item.kuantitas,
  }));
  localStorage.setItem("checkout_selection", JSON.stringify(selectedItems));
  router.push({ name: "checkout" });
};

onMounted(() => {
  checkLogin();
  if (isLoggedIn.value) {
    fetchCartPreview();
  } else {
    loading.value = false;
  }
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
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Animasi Modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-slide-enter-active,
.modal-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.modal-slide-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}
.modal-slide-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>
