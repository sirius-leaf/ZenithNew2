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
  <div class="font-ubuntu container mx-auto p-4 md:p-8">
    <div
      class="max-w-xl mx-auto bg-white shadow-xl rounded-xl p-8 text-center border-t-4"
      :class="
        orderData?.status === 'paid' ? 'border-blue-600' : 'border-pink-500'
      "
    >
      <div v-if="loading" class="py-10 text-gray-500">Memuat data...</div>
      <div v-else-if="error" class="py-10 text-pink-600">{{ error }}</div>

      <div v-else>
        <!-- Icon Status -->
        <div class="mb-6 flex justify-center">
          <div
            v-if="orderData.status === 'paid'"
            class="bg-blue-50 p-4 rounded-full"
          >
            <svg
              class="w-12 h-12 text-blue-600"
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
          </div>
          <div v-else class="bg-pink-50 p-4 rounded-full">
            <svg
              class="w-12 h-12 text-pink-600"
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

        <!-- Judul -->
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
          {{
            orderData.status === "paid"
              ? "Pembayaran Berhasil!"
              : "Menunggu Pembayaran"
          }}
        </h1>

        <p class="text-gray-500 mb-6">ID Pesanan: #{{ orderData.id }}</p>

        <!-- Detail Pembayaran -->
        <div
          class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-100 text-left"
        >
          <div class="flex justify-between mb-2">
            <span class="text-gray-600">Metode</span>
            <span class="font-medium uppercase text-blue-800">
              {{ orderData.payment_method === "midtrans" ? "Midtrans" : "COD" }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Total Bayar</span>
            <span class="font-bold text-blue-800">
              Rp {{ parseFloat(orderData.total_harga).toLocaleString("id-ID") }}
            </span>
          </div>
        </div>

        <!-- Aksi -->
        <div class="space-y-4">
          <!-- Midtrans: Tombol Bayar (Simulasi) -->
          <div
            v-if="
              orderData.status === 'pending' &&
              orderData.payment_method === 'midtrans'
            "
          >
            <p class="text-sm text-gray-500 mb-3">
              Klik tombol di bawah untuk mensimulasikan pembayaran sukses.
            </p>
            <button
              @click="simulatePayment"
              :disabled="processingPayment"
              class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-lg transition disabled:opacity-50"
            >
              {{
                processingPayment ? "Memproses..." : "Bayar Sekarang (Simulasi)"
              }}
            </button>
          </div>

          <!-- COD: Tidak ada aksi bayar -->
          <div
            v-else-if="
              orderData.status === 'pending' &&
              orderData.payment_method === 'cod'
            "
          >
            <div
              class="bg-blue-50 p-4 rounded text-blue-800 text-sm font-medium"
            >
              Pesanan Anda menggunakan metode
              <strong>Bayar di Tempat (COD)</strong>.<br />
              Silakan bayar tunai ke kurir saat barang sampai.
            </div>
          </div>

          <!-- Status selain pending -->
          <div
            v-else
            class="bg-blue-50 p-4 rounded text-blue-800 text-sm font-medium"
          >
            Terima kasih! Pesanan Anda sedang diproses oleh penjual.
          </div>

          <!-- Navigasi Kembali (opsional) -->
          <router-link
            :to="`/riwayat/${orderData.id}`"
            class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition text-center"
          >
            Lihat Pesanan Anda
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
