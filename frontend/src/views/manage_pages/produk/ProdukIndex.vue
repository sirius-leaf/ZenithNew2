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

    <div class="mb-6">
      <RouterLink
        :to="{ name: 'produk.create' }"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-block"
      >
        + Tambah Produk
      </RouterLink>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-10">
      <p class="text-gray-600">Memuat data...</p>
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
                @click.prevent="confirmDelete(product.id_produk)"
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

// Fetch products from API
const fetchProducts = async () => {
  loading.value = true;
  error.value = "";
  try {
    const response = await axios.get(
      "http://127.0.0.1:8000/api/manage/product"
    ); // Sesuaikan dengan URL API Anda
    products.value = response.data.data[0].products;
  } catch (err) {
    error.value = "Gagal memuat data produk.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// Delete product
const deleteProduct = async (id) => {
  if (!confirm("Hapus produk ini?")) return;

  try {
    await axios.delete(`http://127.0.0.1:8000/api/manage/product/${id}`);
    successMessage.value = "Produk berhasil dihapus!";
    // Refresh data
    await fetchProducts();
  } catch (err) {
    alert("Gagal menghapus produk.");
    console.error(err);
  }
};

onMounted(() => {
  if (localStorage.getItem("userRole") !== "penjual") {
    alert("Akses ditolak, Anda bukan penjual");
    router.push("/dashboard");
  }

  fetchProducts();
});
</script>

<style scoped>
/* Jika Anda ingin menambahkan styling khusus */
</style>
