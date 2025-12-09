<template>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Daftar Produk</h2>

    <!-- Success Message -->
    <div
      v-if="successMessage"
      class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg"
    >
      {{ successMessage }}
    </div>

    <!-- Frozen Warning -->
    <div
      v-if="isFrozen"
      class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3 cursor-pointer hover:bg-red-100 transition"
      @click="showFrozenModal = true"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="w-6 h-6 text-red-600 flex-shrink-0"
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
      <div>
        <h3 class="font-bold text-red-800">Toko Dibekukan</h3>
        <p class="text-sm text-red-700">
          Toko Anda sedang dibekukan oleh admin. Klik untuk detail.
        </p>
      </div>
    </div>

    <div class="mb-6">
      <button
        @click="handleAddProduct"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-block transition-colors"
        :class="{ 'opacity-50 cursor-not-allowed': isFrozen }"
      >
        + Tambah Produk
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div
        class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-pink-500"
      ></div>
      <p class="mt-2 text-gray-600">Memuat data...</p>
    </div>

    <!-- Error Message -->
    <div v-else-if="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
      {{ error }}
    </div>

    <!-- Product Table -->
    <div v-else class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Nama Produk
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Merek
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Deskripsi
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Aksi
            </th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="product in products" :key="product.id_produk">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.nama_produk }}
              <span class="text-gray-500 text-xs ml-1"
                >({{
                  product.variant ? product.variant.length : 0
                }}
                varian)</span
              >
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.merek }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
              {{ product.deskripsi }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <RouterLink
                :to="{
                  name: 'produk.edit',
                  params: { id: product.id_produk },
                }"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mr-2"
              >
                Edit
              </RouterLink>
              <button
                @click="deleteProduct(product.id_produk)"
                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
              >
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="products.length === 0" class="text-center py-10 text-gray-500">
        Tidak ada produk ditemukan.
      </div>
    </div>

    <!-- Frozen Reason Modal -->
    <div
      v-if="showFrozenModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex items-center gap-3 mb-4 text-red-600">
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
          <h3 class="text-xl font-bold text-gray-900">Toko Dibekukan</h3>
        </div>
        <p class="text-gray-600 mb-2">
          Toko Anda telah dibekukan oleh admin. Anda tidak dapat membuat produk
          baru, namun pesanan yang ada masih dapat diproses.
        </p>
        <div class="bg-gray-50 p-3 rounded-md border border-gray-200 mb-6">
          <p class="text-xs text-gray-500 uppercase font-bold mb-1">Alasan:</p>
          <p class="text-gray-800 italic">"{{ frozenReason }}"</p>
        </div>
        <div class="flex justify-end">
          <button
            @click="showFrozenModal = false"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { RouterLink, useRouter } from "vue-router";

const router = useRouter();

const products = ref([]);
const loading = ref(false);
const error = ref("");
const successMessage = ref("");

// Frozen Logic
const isFrozen = ref(false);
const frozenReason = ref("");
const showFrozenModal = ref(false);

// Fetch products from API
const fetchProducts = async () => {
  loading.value = true;
  error.value = "";
  try {
    const response = await axios.get(
      "http://127.0.0.1:8000/api/manage/product",
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      }
    );
    console.log("API Response:", response.data);
    if (
      response.data.data &&
      response.data.data[0] &&
      response.data.data[0].products
    ) {
      products.value = response.data.data[0].products;
    } else {
      products.value = response.data.data;
    }
    console.log("Products:", products.value);
  } catch (err) {
    error.value = "Gagal memuat data produk.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const fetchUserStatus = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
    });
    if (res.data && res.data.toko) {
      isFrozen.value = !!res.data.toko.is_frozen;
      frozenReason.value =
        res.data.toko.frozen_reason || "Tidak ada alasan spesifik.";
    }
  } catch (e) {
    console.error("Gagal cek status user", e);
  }
};

const handleAddProduct = () => {
  if (isFrozen.value) {
    showFrozenModal.value = true;
    return;
  }
  router.push({ name: "produk.create" });
};

// Delete product
const deleteProduct = async (id) => {
  if (!confirm("Hapus produk ini?")) return;

  try {
    await axios.delete(`http://127.0.0.1:8000/api/manage/product/${id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
    });
    successMessage.value = "Produk berhasil dihapus!";
    // Refresh data
    await fetchProducts();
  } catch (err) {
    alert("Gagal menghapus produk.");
    console.error(err);
  }
};

onMounted(() => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  if (localStorage.getItem("userRole") !== "penjual") {
    alert("Akses ditolak, Anda bukan penjual");
    router.push("/dashboard");
  }

  fetchProducts();
  fetchUserStatus();
});
</script>

<style scoped>
/* Jika Anda ingin menambahkan styling khusus */
</style>
