<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { useCartStore } from "@/stores/cartStore";

const router = useRouter();
const { updateCartItem } = useCartStore();
const pcBuild = ref([]);
const loading = ref(true);
const message = ref(null);

// Ambil data dari API
const fetchBuild = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/manage/pcBuild");
    pcBuild.value = res.data.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const deleteBuild = async (id) => {
  if (!confirm("Hapus PC Build ini?")) return;

  try {
    await axios.delete(`http://127.0.0.1:8000/api/manage/pcBuild/${id}`);

    message.value = "PC Build berhasil dihapus!";
    pcBuild.value = pcBuild.value.filter((b) => b.id_build !== id);

    setTimeout(() => (message.value = null), 2000);
  } catch (err) {
    console.error(err);
  }
};

// Checkout Build ke Keranjang
const checkoutBuild = (build) => {
  if (!build.build_detail || build.build_detail.length === 0) {
    alert("Rakitan kosong, tidak ada yang bisa di-checkout.");
    return;
  }

  let addedCount = 0;
  build.build_detail.forEach((detail) => {
    if (detail.variant) {
       updateCartItem(detail.variant.id_varian, 1, {
         nama_varian: detail.variant.nama_varian,
         harga: detail.variant.harga,
         stok: detail.variant.stok,
         product_name: detail.variant.product?.nama_produk || 'Produk',
       });
       addedCount++;
    }
  });

  if (addedCount > 0) {
    alert(`${addedCount} item berhasil ditambahkan ke keranjang!`);
  }
};

// Hitung Total Harga
const calculateTotalPrice = (build) => {
  if (!build.build_detail) return 0;
  return build.build_detail.reduce((total, detail) => {
    return total + (detail.variant?.harga || 0);
  }, 0);
};

// Format Rupiah
const formatRupiah = (number) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(number);
};

onMounted(() => {
  fetchBuild();
});
</script>

<template>
  <div class="p-6 md:p-10 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">PC Builds</h2>
        <p class="text-gray-500 mt-1">Kelola daftar rakitan PC impianmu.</p>
      </div>

      <button
        @click="router.push('/dashboard/manage/desktopLab/create')"
        class="bg-pink-600 text-white px-6 py-2.5 rounded-xl font-medium shadow-lg shadow-pink-200 hover:bg-pink-700 hover:shadow-xl transition-all duration-300 flex items-center gap-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Buat Rakitan Baru
      </button>
    </div>

    <!-- Notification -->
    <div
      v-if="message"
      class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 animate-fade-in-down"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      </svg>
      {{ message }}
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-pink-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="pcBuild.length === 0" class="text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
      <div class="bg-white p-4 rounded-full inline-block mb-4 shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900">Belum ada rakitan</h3>
      <p class="text-gray-500 mt-1">Mulai buat PC impianmu sekarang!</p>
    </div>

    <!-- Table List -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50/50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-100">
              <th class="py-4 px-6 font-semibold">Nama Build</th>
              <th class="py-4 px-6 font-semibold">Komponen</th>
              <th class="py-4 px-6 font-semibold">Total Harga</th>
              <th class="py-4 px-6 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="b in pcBuild"
              :key="b.id_build"
              class="hover:bg-gray-50/80 transition-colors group"
            >
              <td class="py-4 px-6">
                <div class="font-bold text-gray-900 text-lg">{{ b.nama_build }}</div>
                <div class="text-xs text-gray-400 mt-0.5">ID: #{{ b.id_build }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <span class="bg-blue-50 text-blue-700 py-1 px-2.5 rounded-md text-xs font-semibold border border-blue-100">
                    {{ b.build_detail?.length || 0 }} Item
                  </span>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="text-pink-600 font-bold text-base">
                  {{ formatRupiah(calculateTotalPrice(b)) }}
                </div>
              </td>
              <td class="py-4 px-6 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="checkoutBuild(b)"
                    class="p-2 text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors shadow-sm"
                    title="Checkout ke Keranjang"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </button>
                  <button
                    @click="router.push(`/dashboard/manage/desktopLab/${b.id_build}/edit`)"
                    class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                  </button>
                  <button
                    @click="deleteBuild(b.id_build)"
                    class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Hapus"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in-down {
  animation: fadeInDown 0.3s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
