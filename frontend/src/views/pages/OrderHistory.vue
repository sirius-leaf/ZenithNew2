<template>
  <div class="font-ubuntu p-6 min-h-screen bg-white">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold mb-8 text-blue-800">Riwayat Pembelian</h1>

      <div
        class="bg-white rounded-xl shadow overflow-hidden border border-gray-200"
      >
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  ID Pesanan
                </th>

                <th
                  scope="col"
                  class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Tanggal
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Total Harga
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  scope="col"
                  class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                >
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-if="orders.length === 0">
                <td
                  colspan="6"
                  class="px-6 py-12 text-center text-gray-500 text-lg"
                >
                  Belum ada riwayat pembelian.
                </td>
              </tr>
              <tr
                v-for="order in orders"
                :key="order.id"
                class="hover:bg-blue-50 transition-colors"
              >
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">
                    #{{ String(order.id).padStart(4, "0") }}
                  </div>
                </td>

                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm text-gray-600">
                    {{ formatDate(order.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right">
                  <div class="text-sm font-bold text-gray-800">
                    Rp {{ formatPrice(order.total_harga) }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-center">
                  <span
                    class="px-3 py-1 inline-flex text-xs font-semibold rounded-full"
                    :class="{
                      'bg-blue-100 text-blue-800': [
                        'completed',
                        'success',
                      ].includes(order.status),
                      'bg-pink-100 text-pink-700': order.status === 'pending',
                      'bg-red-100 text-red-700': [
                        'cancelled',
                        'failed',
                      ].includes(order.status),
                      'bg-blue-50 text-blue-700': [
                        'shipped',
                        'processing',
                        'paid',
                        'confirmed',
                        'packed',
                      ].includes(order.status),
                    }"
                  >
                    {{ getStatusLabel(order) }}
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-center">
                  <div class="flex flex-col gap-2 items-center">
                    <router-link
                      :to="`/riwayat/${order.id}`"
                      class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      Lihat Detail
                    </router-link>
                    <button
                      v-if="
                        ['pending', 'paid', 'confirmed'].includes(
                          order.status
                        ) && !order.is_cancellation_rejected
                      "
                      @click="openCancelModal(order)"
                      class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                      Ajukan Pembatalan
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Konfirmasi Pembatalan -->
    <div
      v-if="showCancelModal"
      class="fixed inset-0 backdrop-blur-sm bg-black/20 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl w-full max-w-md p-6 shadow-lg"
        @click.stop
      >
        <h2 class="text-xl font-bold text-gray-800 mb-4">Ajukan Pembatalan</h2>

        <!-- Step 1: Konfirmasi Awal -->
        <div v-if="cancelStep === 1">
          <p class="text-gray-600 mb-6">
            Apakah Anda yakin ingin mengajukan pembatalan untuk pesanan
            <span class="font-bold"
              >#{{ String(selectedOrder?.id).padStart(4, "0") }}</span
            >?
          </p>
          <div class="flex justify-end gap-3">
            <button
              @click="closeCancelModal"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Tidak
            </button>
            <button
              @click="cancelStep = 2"
              class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
            >
              Ya, Lanjutkan
            </button>
          </div>
        </div>

        <!-- Step 2: Pilih Alasan -->
        <div v-else-if="cancelStep === 2">
          <p class="text-gray-600 mb-4 text-sm">
            Silakan pilih alasan pembatalan:
          </p>

          <div class="space-y-2 mb-4">
            <label
              v-for="reason in cancelReasons"
              :key="reason"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="radio"
                v-model="selectedReason"
                :value="reason"
                class="text-red-600 focus:ring-red-500"
              />
              <span class="text-sm text-gray-700">{{ reason }}</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="radio"
                v-model="selectedReason"
                value="Lainnya"
                class="text-red-600 focus:ring-red-500"
              />
              <span class="text-sm text-gray-700">Lainnya</span>
            </label>
          </div>

          <textarea
            v-if="selectedReason === 'Lainnya' || selectedReason"
            v-model="customReason"
            placeholder="Tuliskan detail alasan (opsional)..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 mb-4"
            rows="3"
          ></textarea>

          <div class="flex justify-end gap-3">
            <button
              @click="cancelStep = 1"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Kembali
            </button>
            <button
              @click="submitCancel"
              :disabled="!selectedReason"
              class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Kirim Pengajuan
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const orders = ref([]);
const showCancelModal = ref(false);
const selectedOrder = ref(null);
const cancelStep = ref(1);
const selectedReason = ref("");
const customReason = ref("");

const cancelReasons = [
  "Salah Produk",
  "Salah Alamat",
  "Berubah Pikiran",
  "Double Order Tidak Disengaja",
  "Salah Memilih Varian",
];

const fetchOrders = async () => {
  try {
    const token = localStorage.getItem("authToken");
    const res = await axios.get("http://127.0.0.1:8000/api/order/history", {
      headers: { Authorization: `Bearer ${token}` },
    });
    orders.value = res.data.data;
  } catch (err) {
    console.error("Gagal mengambil riwayat pesanan:", err);
  }
};

const formatPrice = (value) => {
  return new Intl.NumberFormat("id-ID").format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return "-";
  return new Date(dateString).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getStatusLabel = (order) => {
  const status = order.status;
  const map = {
    pending: "Menunggu",
    paid: "Dibayar",
    confirmed: "Dikonfirmasi",
    packed: "Dikemas",
    shipped: "Dikirim",
    completed: "Selesai",
    success: "Berhasil",
    cancelled: "Dibatalkan",
    failed: "Gagal",
    processing: "Diproses",
    invalid: "Invalid",
    cancellation_requested: "Pengajuan Pembatalan",
  };

  // Jika status dikembalikan (misal: confirmed) tapi pernah ditolak
  if (
    status !== "cancellation_requested" &&
    status !== "cancelled" &&
    order.is_cancellation_rejected
  ) {
    return `${map[status] || status} (Pengajuan Ditolak)`;
  }

  return map[status] || status;
};

const openCancelModal = (order) => {
  selectedOrder.value = order;
  cancelStep.value = 1;
  selectedReason.value = "";
  customReason.value = "";
  showCancelModal.value = true;
};

const closeCancelModal = () => {
  showCancelModal.value = false;
  selectedOrder.value = null;
};

const submitCancel = async () => {
  if (!selectedOrder.value || !selectedReason.value) return;

  let finalReason = selectedReason.value;
  if (selectedReason.value === "Lainnya" && customReason.value.trim()) {
    finalReason = `Lainnya: ${customReason.value}`;
  } else if (selectedReason.value === "Lainnya" && !customReason.value.trim()) {
    alert("Mohon isi detail alasan pembatalan.");
    return;
  }

  try {
    const token = localStorage.getItem("authToken");
    await axios.post(
      `http://127.0.0.1:8000/api/orders/${selectedOrder.value.id}/cancel`,
      {
        alasan: finalReason,
      },
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    // Refresh orders
    await fetchOrders();
    closeCancelModal();
    alert("Pengajuan pembatalan berhasil dikirim.");
  } catch (err) {
    console.error("Gagal membatalkan pesanan:", err);
    alert(err.response?.data?.message || "Gagal membatalkan pesanan.");
  }
};

onMounted(() => {
  fetchOrders();
});
</script>
