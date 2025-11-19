<script setup>
import { ref, onMounted, computed } from "vue"; // Import computed
import { useCartStore } from "@/stores/cartStore";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();
const { cartItems, removeCartItem, updateCartItem, totalItems } = useCartStore();

const loading = ref(true);
const cartSummary = ref(null); // Hasil dari API Order/Preview
const totalPrice = ref(0);
const apiError = ref(null);
const checkedItems = ref({}); // State baru: { id_varian: true/false }

// Inisialisasi semua item di keranjang sebagai checked
const initializeChecklist = (summary) => {
  const initialChecks = {};
  summary.forEach((item) => {
    // Pertahankan status checked sebelumnya jika ada, jika tidak, set default true
    initialChecks[item.variant.id_varian] = checkedItems.value[item.variant.id_varian] !== undefined ? checkedItems.value[item.variant.id_varian] : true;
  });
  checkedItems.value = initialChecks;
};

// =========================================================
// COMPUTED PROPERTIES BARU (untuk filter harga dan item)
// =========================================================
const filteredCartForCheckout = computed(() => {
  return cartSummary.value.filter((item) => checkedItems.value[item.variant.id_varian]);
});

const filteredTotalPrice = computed(() => {
  return filteredCartForCheckout.value.reduce((sum, item) => sum + item.subtotal, 0);
});

const totalCheckedItems = computed(() => {
  return filteredCartForCheckout.value.reduce((total, item) => total + item.kuantitas, 0);
});

// Fungsi memanggil API untuk mendapatkan ringkasan harga dan cek stok
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
      {
        cartItems: cartDataForApi,
      },
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    cartSummary.value = response.data.cartItems;
    totalPrice.value = response.data.totalPrice;

    // Panggil inisialisasi setelah data dari API diterima
    initializeChecklist(response.data.cartItems);
  } catch (error) {
    console.error("Error preview cart:", error);
    apiError.value = error.response?.data?.message || "Gagal memuat ringkasan keranjang.";
  } finally {
    loading.value = false;
  }
};

const updateKuantitas = (id_varian, kuantitas) => {
  // Lakukan update kuantitas di global store (yang akan memicu refresh di sini)
  updateCartItem(id_varian, kuantitas - cartItems.value.find((i) => i.id_varian === id_varian).kuantitas);
  fetchCartPreview();
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

  // Simpan item yang dipilih ke localStorage untuk halaman checkout
  const selectedItems = filteredCartForCheckout.value.map((item) => ({
    id_varian: item.variant.id_varian,
    kuantitas: item.kuantitas,
  }));

  localStorage.setItem("checkout_selection", JSON.stringify(selectedItems));
  router.push({ name: "checkout" });
};

// Lifecycle
onMounted(() => {
  fetchCartPreview();
});
</script>

<template>
  <div class="container mx-auto p-4 md:p-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Keranjang Belanja</h1>

    <div v-if="apiError" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
      <h3 class="font-bold">Error Stok/Koneksi:</h3>
      <p>{{ apiError }}</p>
      <p class="mt-2 text-sm">Harap hapus atau kurangi item yang bermasalah.</p>
    </div>

    <div v-if="loading" class="text-center py-10 text-gray-500">Memuat detail keranjang...</div>

    <div v-else-if="cartItems.length === 0" class="text-center py-10">
      <p class="text-lg text-gray-600">Keranjang Anda masih kosong. Mari belanja!</p>
      <router-link to="/product" class="mt-4 inline-block text-blue-600 hover:underline">Lihat Semua Produk</router-link>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-4">
        <div v-for="item in cartSummary" :key="item.variant.id_varian" class="flex items-center bg-white p-4 rounded-lg shadow-sm border border-gray-100">
          <input type="checkbox" v-model="checkedItems[item.variant.id_varian]" class="form-checkbox h-5 w-5 text-pink-600 rounded mr-4 transition duration-150 ease-in-out" />

          <div class="flex-1 min-w-0 pr-4">
            <h3 class="text-lg font-semibold text-gray-900 truncate">
              {{ item.variant.product.nama_produk }} <small class="text-gray-500">({{ item.variant.nama_varian }})</small>
            </h3>
            <p class="text-sm text-gray-600">Toko: {{ item.variant.product.toko.toko_name }}</p>
            <p class="text-md font-medium text-green-600 mt-1">Rp {{ item.variant.harga.toLocaleString("id-ID") }}</p>
          </div>

          <div class="w-24 flex flex-col items-center">
            <label class="text-xs text-gray-500 mb-1">Qty</label>
            <input type="number" :value="item.kuantitas" @change="updateKuantitas(item.variant.id_varian, $event.target.valueAsNumber)" min="1" class="w-full text-center border border-gray-300 rounded-md py-1 text-sm focus:ring-blue-500" />
          </div>

          <div class="w-32 text-right px-4">
            <p class="text-sm text-gray-700 font-bold">Rp {{ item.subtotal.toLocaleString("id-ID") }}</p>
          </div>

          <button @click="removeItem(item.variant.id_varian)" class="text-red-500 hover:text-red-700 ml-4 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </div>

      <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-100 sticky top-4">
          <h2 class="text-xl font-bold mb-4 border-b pb-3">Ringkasan Pesanan</h2>

          <div class="flex justify-between text-lg font-semibold mt-4 pt-3 border-t border-gray-200">
            <span>Total Harga ({{ totalCheckedItems }} Item Dipilih)</span>
            <span class="text-green-600">Rp {{ filteredTotalPrice.toLocaleString("id-ID") }}</span>
          </div>

          <button @click="goToCheckout" :disabled="filteredCartForCheckout.length === 0 || apiError || loading" class="w-full mt-6 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-lg transition disabled:bg-gray-400">
            Lanjut ke Checkout
          </button>

          <router-link :to="{ name: 'product-list' }" class="block text-center mt-3 text-sm text-blue-600 hover:underline">
            Lanjutkan Belanja
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
