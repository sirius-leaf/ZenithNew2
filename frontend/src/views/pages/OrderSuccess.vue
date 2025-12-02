<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const orderId = ref(route.query.id);
const orderData = ref(null);
const loading = ref(true);
const processingPayment = ref(false);
const error = ref(null);

// Helper untuk format tanggal
const formatDate = (dateString) => {
  if (!dateString) return "";
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return new Date(dateString).toLocaleDateString("id-ID", options);
};

// Helper untuk URL gambar
const getImageUrl = (path) => {
  if (!path) return "https://via.placeholder.com/100?text=No+Image";
  if (path.startsWith("http")) return path;
  return `http://127.0.0.1:8000/storage/${path}`;
};

// Fungsi mengambil data pesanan
const fetchOrderDetails = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem("authToken");
    if (!token || !orderId.value) {
      router.push({ name: "home" });
      return;
    }

    const response = await axios.get(
      `http://127.0.0.1:8000/api/orders/${orderId.value}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    orderData.value = response.data.data;
  } catch (err) {
    console.error(err);
    error.value = "Gagal memuat data pesanan.";
  } finally {
    loading.value = false;
  }
};

// Fungsi Simulasi Pembayaran (Midtrans)
const simulatePayment = async () => {
  if (!confirm("Simulasikan pembayaran sukses sekarang?")) return;

  processingPayment.value = true;
  try {
    const token = localStorage.getItem("authToken");

    await axios.post(
      `http://127.0.0.1:8000/api/payment/simulate/${orderId.value}`,
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    );

    // ✅ Langsung redirect ke riwayat setelah sukses
    await router.push("/riwayat");
  } catch (err) {
    alert(err.response?.data?.message || "Gagal memproses pembayaran.");
    processingPayment.value = false;
  }
};

onMounted(() => {
  if (!orderId.value) {
    alert("ID Pesanan tidak ditemukan.");
    router.push({ name: "home" });
    return;
  }

  fetchOrderDetails();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 font-ubuntu">
    <div class="max-w-3xl mx-auto">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-pink-600 mx-auto mb-4"
        ></div>
        <p class="text-gray-500">Memuat detail pesanan...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <div class="text-red-500 text-xl mb-4">⚠️</div>
        <p class="text-gray-800 font-medium">{{ error }}</p>
        <button
          @click="$router.push('/')"
          class="mt-4 text-pink-600 hover:underline"
        >
          Kembali ke Beranda
        </button>
      </div>

      <!-- Success Content -->
      <div v-else class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header Status -->
        <div
          class="bg-gradient-to-r p-8 text-center text-white"
          :class="
            orderData.status === 'paid'
              ? 'from-blue-600 to-blue-500'
              : 'from-pink-600 to-pink-500'
          "
        >
          <div class="mb-4 flex justify-center">
            <div class="bg-white/20 p-4 rounded-full backdrop-blur-sm">
              <svg
                v-if="orderData.status === 'paid'"
                class="w-12 h-12 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
              <svg
                v-else
                class="w-12 h-12 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
          </div>
          <h1 class="text-3xl font-bold mb-2">
            {{
              orderData.status === "paid"
                ? "Pembayaran Berhasil!"
                : "Menunggu Pembayaran"
            }}
          </h1>
          <p class="text-white/90">ID Pesanan: #{{ orderData.id }}</p>
          <p class="text-white/80 text-sm mt-1">
            {{ formatDate(orderData.created_at) }}
          </p>
        </div>

        <div class="p-8">
          <!-- Order Info Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <p class="text-gray-500 text-sm mb-1">Metode Pembayaran</p>
              <p
                class="font-bold text-gray-800 uppercase flex items-center gap-2"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-gray-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                  />
                </svg>
                {{
                  orderData.payment_method === "cod"
                    ? "COD (Bayar di Tempat)"
                    : "Pembayaran Online (Midtrans)"
                }}
              </p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <p class="text-gray-500 text-sm mb-1">Total Pembayaran</p>
              <p class="font-bold text-pink-600 text-xl">
                Rp
                {{ parseFloat(orderData.total_harga).toLocaleString("id-ID") }}
              </p>
            </div>
          </div>

          <!-- Product List -->
          <div class="mb-8">
            <h3
              class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-pink-500"
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
              Rincian Produk
            </h3>
            <div class="space-y-4">
              <div
                v-for="item in orderData.detail_pesanans"
                :key="item.id"
                class="flex gap-4 p-4 rounded-xl border border-gray-100 hover:border-pink-100 hover:bg-pink-50/30 transition-colors"
              >
                <!-- Product Image -->
                <div
                  class="w-20 h-20 flex-shrink-0 bg-white rounded-lg border border-gray-200 overflow-hidden"
                >
                  <img
                    :src="getImageUrl(item.variant?.gambar_varian)"
                    :alt="item.variant?.product?.nama_produk"
                    class="w-full h-full object-cover"
                  />
                </div>

                <!-- Product Details -->
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-gray-900 truncate">
                    {{ item.variant?.product?.nama_produk || "Produk dihapus" }}
                  </h4>
                  <p class="text-sm text-gray-500 mb-1">
                    Varian:
                    <span class="text-gray-700 font-medium">{{
                      item.variant?.nama_varian || "-"
                    }}</span>
                  </p>
                  <div class="flex justify-between items-end mt-2">
                    <p class="text-sm text-gray-500">
                      {{ item.kuantitas }} x
                      <span class="font-medium">
                        Rp {{ parseFloat(item.harga).toLocaleString("id-ID") }}
                      </span>
                    </p>
                    <p class="font-bold text-gray-900">
                      Rp
                      {{
                        (item.kuantitas * item.harga).toLocaleString("id-ID")
                      }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-3 pt-6 border-t border-gray-100">
            <!-- Midtrans Action -->
            <div
              v-if="
                orderData.status === 'pending' &&
                orderData.payment_method !== 'cod'
              "
            >
              <button
                @click="simulatePayment"
                :disabled="processingPayment"
                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-pink-200 disabled:opacity-50 flex justify-center items-center gap-2"
              >
                <span
                  v-if="processingPayment"
                  class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"
                ></span>
                {{ processingPayment ? "Memproses..." : "Bayar Sekarang" }}
              </button>
              <p class="text-center text-xs text-gray-400 mt-2">
                *Ini adalah simulasi pembayaran untuk lingkungan development
              </p>
            </div>

            <!-- COD Info -->
            <div
              v-else-if="
                orderData.status === 'pending' &&
                orderData.payment_method === 'cod'
              "
              class="bg-blue-50 text-blue-800 p-4 rounded-xl text-center text-sm font-medium mb-4"
            >
              Pesanan akan diproses oleh penjual. Silakan siapkan uang tunai
              saat kurir datang.
            </div>

            <!-- Navigation Buttons -->
            <div class="grid grid-cols-2 gap-4">
              <button
                @click="$router.push('/')"
                class="w-full px-6 py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition"
              >
                Belanja Lagi
              </button>
              <button
                @click="$router.push(`/riwayat/${orderData.id}`)"
                class="w-full px-6 py-3 bg-blue-800 text-white font-bold rounded-xl hover:bg-blue-900 hover:border-pink-500 border-2 border-transparent transition"
              >
                Lihat Pesanan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
