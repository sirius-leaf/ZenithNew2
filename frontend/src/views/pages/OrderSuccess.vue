<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const orderId = ref(route.query.id);
const orderStatus = ref("pending");
const simulationLoading = ref(false);
const simulationMessage = ref("Pesanan menunggu pembayaran.");
const orderDetails = ref(null); // Detail order (alamat, subtotal, produk, dsb)

// === FETCH DETAIL ORDER ===
const getOrderDetails = async () => {
  try {
    const token = localStorage.getItem("authToken");
    const res = await axios.get(`http://127.0.0.1:8000/api/orders/${orderId.value}`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    orderDetails.value = res.data.data;
    orderStatus.value = res.data.data.status; // Set status dari backend
    console.log("DETAIL YANG DIPAKAI COMPONENT:", res.data);
  } catch (error) {
    console.error("Fetch order failed:", error);
  }
};

// === SIMULASI PEMBAYARAN ===
const simulatePayment = async () => {
  if (!orderId.value) return;

  simulationLoading.value = true;

  try {
    const token = localStorage.getItem("authToken");

    const response = await axios.post(
      `http://127.0.0.1:8000/api/payment/simulate/${orderId.value}`,
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    );

    simulationMessage.value = response.data.message;
    orderStatus.value = "paid"; // Update status UI

    // Refresh detailnya
    await getOrderDetails();
  } catch (error) {
    simulationMessage.value = error.response?.data?.message || "Gagal memproses pembayaran.";
  } finally {
    simulationLoading.value = false;
  }
};

// === ON MOUNT ===
onMounted(async () => {
  if (!orderId.value) {
    alert("ID Pesanan tidak ditemukan.");
    return router.push({ name: "home" });
  }

  await getOrderDetails();
});
console.log("Order ID dari URL:", orderId.value);
</script>

<template>
  <div class="min-h-screen bg-white">
    <main class="px-4 sm:px-8 md:px-12 py-8 md:py-10 grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-10">
      <!-- =============== LEFT SECTION =============== -->
      <div class="md:col-span-2 border rounded-lg p-6 md:p-8 shadow-sm">
        <!-- Status -->
        <div
          :class="{
            'bg-green-100 text-green-700': orderStatus === 'paid',
            'bg-yellow-100 text-yellow-700': orderStatus === 'pending',
          }"
          class="p-4 rounded-lg mb-6 text-lg font-semibold transition">
          Status Pesanan #{{ orderId }}:
          <span class="uppercase font-bold">{{ orderStatus }}</span>
        </div>

        <h2 class="text-lg font-semibold mb-4">Scan QR Code</h2>

        <!-- QR PLACEHOLDER -->
        <div class="w-full sm:w-64 h-64 mx-auto bg-gray-100 flex items-center justify-center rounded-lg border mb-4">
          <span class="text-gray-400">[QR Code]</span>
        </div>

        <!-- INSTRUKSI -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 class="font-semibold mb-2 text-blue-600">Cara Pembayaran:</h3>
          <ul class="list-decimal list-inside text-gray-700 text-sm space-y-1">
            <li>Buka aplikasi e-wallet Anda (Dana, OVO, Gopay, dll)</li>
            <li>Pilih menu “Scan QR”</li>
            <li>Arahkan kamera ke kode QR di atas</li>
            <li>Pastikan nominal sudah sesuai sebelum konfirmasi</li>
          </ul>
        </div>

        <!-- TOMBOL -->
        <div class="flex justify-end">
          <button
            @click="simulatePayment"
            :disabled="simulationLoading || orderStatus === 'paid'"
            class="mt-6 bg-pink-500 text-white px-6 py-2 rounded-lg hover:bg-pink-600 transition w-full sm:w-auto disabled:bg-gray-400">
            {{ simulationLoading ? "Mengkonfirmasi..." : "Konfirmasi Pembayaran" }}
          </button>
        </div>

        <p class="mt-4 text-sm text-gray-500">
          {{ simulationMessage }}
        </p>
      </div>

      <!-- =============== RIGHT SECTION =============== -->
      <div class="border rounded-lg p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Detail Transaksi</h3>

        <div v-if="orderDetails" class="text-sm space-y-2 text-gray-700">
          <p>
            <strong>ID Pembayaran:</strong>
            {{ orderDetails.id }}
          </p>
          <p>
            <strong>Metode:</strong>
            {{ orderDetails.payment_method || "QRIS" }}
          </p>
          <p>
            <strong>Alamat Pengiriman:</strong>
            {{ orderDetails.shipping_address || "-" }}
          </p>
          <p>
            <strong>Atas Nama:</strong>
            {{ orderDetails.customer_name || "-" }}
          </p>

          <hr class="my-3" />
          <p>
            <strong>Sub Total:</strong>
            {{ orderDetails.total_harga }}
          </p>
          <p class="text-pink-600 font-semibold">Total Pembayaran: Rp {{ orderDetails.total_harga }}</p>

          <hr class="my-3" />
          <h4 class="font-semibold mb-2">Pesanan Anda:</h4>

          <div v-for="item in orderDetails.detail_pesanans" :key="item.id" class="border rounded-lg p-3 bg-gray-50">
            <p class="text-sm font-medium">{{ item.variant?.product?.nama_produk || "-" }}</p>
            <p class="text-gray-500 text-sm">Qty: {{ item.kuantitas }}</p>
            <p class="text-gray-700 font-semibold mt-2">Rp {{ item.harga }}</p>
          </div>
        </div>

        <div v-else class="text-gray-500 text-sm">Memuat detail...</div>
      </div>
    </main>
  </div>
</template>

<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css";
</style>
