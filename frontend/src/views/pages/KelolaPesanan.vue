<template>
  <div class="font-ubuntu p-6 max-w-7xl mx-auto">
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6"
    >
      <h1 class="text-2xl font-bold text-blue-800">Kelola Pesanan</h1>
      <div class="flex items-center gap-2">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari ID pesanan..."
          class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300"
        />
        <button
          @click="applySearch"
          class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg"
        >
          Cari
        </button>
      </div>
    </div>

    <!-- Filter Status -->
    <div class="flex flex-wrap gap-2 mb-6">
      <button
        v-for="status in statusOptions"
        :key="status.value"
        @click="selectedStatus = status.value"
        :class="[
          'px-4 py-1.5 rounded-full text-sm font-medium transition',
          selectedStatus === status.value
            ? 'bg-blue-600 text-white'
            : 'bg-blue-50 text-blue-700 border border-blue-200',
        ]"
      >
        {{ status.label }}
      </button>
    </div>

    <!-- Loading & Error -->
    <div v-if="loading" class="text-center py-12">
      <div
        class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-pink-500"
      ></div>
      <p class="mt-2 text-gray-600">Memuat pesanan...</p>
    </div>
    <div v-else-if="error" class="text-center py-12 text-pink-600">
      Gagal memuat data pesanan.
    </div>

    <!-- Tabel -->
    <div v-else class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              ID Pesanan
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              Tanggal
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              Pembeli
            </th>

            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              Total Item
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              Total Harga
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              Metode Bayar
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              Status
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="order in filteredOrders"
            :key="order.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 text-gray-800">
              ORD-{{ String(order.id).padStart(4, "0") }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ formatDate(order.created_at) }}
            </td>
            <td class="px-4 py-3 text-gray-800">
              {{
                order.user?.name || order.buyer_name || `User #${order.user_id}`
              }}
            </td>
            <td class="px-4 py-3 text-gray-800">
              {{
                order.detail_pesanans.reduce(
                  (sum, item) => sum + item.kuantitas,
                  0
                )
              }}
            </td>
            <td class="px-4 py-3 text-gray-800">
              Rp {{ parseFloat(order.total_harga).toLocaleString("id-ID") }}
            </td>
            <td class="px-4 py-3">
              <span
                class="px-2 py-1 text-xs font-medium rounded-full bg-pink-100 text-pink-800"
              >
                Midtrans
              </span>
            </td>
            <td class="px-4 py-3">
              <span
                class="px-2 py-1 text-xs font-medium rounded-full"
                :class="getStatusClass(order.status)"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </td>
            <td class="px-4 py-3">
              <!-- Lihat Detail -->
              <button
                @click="openDetailModal(order)"
                class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-1 mb-2"
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
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
                Detail
              </button>

              <!-- Aksi Dinamis -->
              <div class="flex flex-wrap gap-1">
                <button
                  v-if="order.status === 'paid'"
                  @click="updateStatus(order, 'confirmed')"
                  class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-2.5 py-1 rounded flex items-center gap-1"
                >
                  <svg
                    class="w-3.5 h-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  Konfirmasi
                </button>

                <button
                  v-else-if="order.status === 'confirmed'"
                  @click="updateStatus(order, 'packed')"
                  class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-2.5 py-1 rounded flex items-center gap-1"
                >
                  <svg
                    class="w-3.5 h-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                    />
                  </svg>
                  Kemas
                </button>

                <button
                  v-else-if="order.status === 'packed'"
                  @click="openResiModal(order)"
                  class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-2.5 py-1 rounded flex items-center gap-1"
                >
                  <svg
                    class="w-3.5 h-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"
                    />
                  </svg>
                  Kirim
                </button>

                <div
                  v-else-if="order.status === 'cancellation_requested'"
                  class="flex flex-wrap gap-1"
                >
                  <button
                    @click="approveCancellation(order)"
                    class="bg-green-600 hover:bg-green-700 text-white text-xs px-2.5 py-1 rounded flex items-center gap-1"
                  >
                    Setuju
                  </button>
                  <button
                    @click="rejectCancellation(order)"
                    class="bg-red-600 hover:bg-red-700 text-white text-xs px-2.5 py-1 rounded flex items-center gap-1"
                  >
                    Tolak
                  </button>
                </div>

                <span
                  v-else-if="
                    ['shipped', 'completed', 'cancelled'].includes(order.status)
                  "
                  class="text-xs text-gray-500"
                >
                  Tidak ada aksi
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Detail -->
    <div
      v-if="showModal"
      class="fixed inset-0 backdrop-blur-sm bg-black/20 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl w-full max-w-3xl max-h-[80vh] overflow-y-auto"
        @click.stop
      >
        <div
          class="p-5 border-b border-gray-200 flex justify-between items-center"
        >
          <h2 class="text-lg font-bold text-blue-800">
            Detail Pesanan ORD-{{ String(selectedOrder?.id).padStart(4, "0") }}
          </h2>
          <button
            @click="closeModal"
            class="text-gray-500 hover:text-gray-700 text-xl"
          >
            &times;
          </button>
        </div>

        <div class="p-6 space-y-4">
          <!-- Informasi Umum -->
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500">ID Pesanan</p>
              <p class="font-medium text-gray-900">
                ORD-{{ String(selectedOrder?.id).padStart(4, "0") }}
              </p>
            </div>
            <div>
              <p class="text-gray-500">Tanggal</p>
              <p class="font-medium text-gray-900">
                {{ formatDate(selectedOrder?.created_at) }}
              </p>
            </div>
            <div>
              <p class="text-gray-500">Status</p>
              <span
                class="px-2 py-0.5 text-xs font-medium rounded-full inline-block mt-1"
                :class="getStatusClass(selectedOrder?.status)"
              >
                {{ getStatusLabel(selectedOrder?.status) }}
              </span>
            </div>
            <div>
              <p class="text-gray-500">Metode Pembayaran</p>
              <p class="font-medium text-gray-900 uppercase">
                {{ selectedOrder?.payment_method || "Midtrans" }}
              </p>
            </div>
          </div>

          <!-- Alasan Pembatalan (Jika ada) -->
          <div
            v-if="
              selectedOrder?.status === 'cancelled' &&
              selectedOrder?.alasan_pembatalan
            "
            class="bg-red-50 p-3 rounded-lg border border-red-100"
          >
            <p class="text-sm text-red-800">
              <span class="font-bold">Alasan Pembatalan:</span>
              {{ selectedOrder.alasan_pembatalan }}
            </p>
          </div>

          <div class="border-t border-gray-100 pt-4">
            <h3 class="font-bold text-gray-800 mb-3 text-sm">
              Informasi Pembeli
            </h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500">ID User</p>
                <p class="font-medium text-gray-900">
                  #{{ selectedOrder?.user_id }}
                </p>
              </div>
              <div>
                <p class="text-gray-500">Nama User</p>
                <p class="font-medium text-gray-900">
                  {{
                    selectedOrder?.user?.name ||
                    selectedOrder?.buyer_name ||
                    "-"
                  }}
                </p>
              </div>
              <div class="col-span-2">
                <p class="text-gray-500">Alamat Pengiriman</p>
                <p class="font-medium text-gray-900">
                  {{ selectedOrder?.alamat_pengiriman || "–" }}
                </p>
              </div>
            </div>
          </div>

          <div class="border-t border-gray-100 pt-4">
            <h3 class="font-bold text-gray-800 mb-3 text-sm">Detail Produk</h3>
            <div class="bg-gray-50 rounded-lg p-3 space-y-3">
              <div
                v-for="(item, idx) in selectedOrder?.detail_pesanans"
                :key="idx"
                class="flex justify-between items-start text-sm border-b border-gray-200 last:border-0 pb-2 last:pb-0"
              >
                <div class="flex-1">
                  <p class="font-medium text-gray-900">
                    {{ item.variant?.product?.nama_produk }}
                  </p>
                  <p class="text-gray-500 text-xs">
                    Varian: {{ item.variant?.nama_varian }}
                  </p>
                  <p class="text-gray-500 text-xs mt-1">
                    {{ item.kuantitas }} x Rp
                    {{ parseFloat(item.harga).toLocaleString("id-ID") }}
                  </p>
                </div>
                <div class="text-right font-medium text-gray-900">
                  Rp {{ (item.kuantitas * item.harga).toLocaleString("id-ID") }}
                </div>
              </div>
            </div>
          </div>

          <div
            class="border-t border-gray-100 pt-4 flex justify-between items-center"
          >
            <div>
              <p class="text-sm text-gray-500">Total Item</p>
              <p class="font-bold text-gray-900">
                {{
                  selectedOrder?.detail_pesanans?.reduce(
                    (sum, item) => sum + item.kuantitas,
                    0
                  )
                }}
                Pcs
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">Total Harga</p>
              <p class="text-xl font-bold text-blue-600">
                Rp
                {{
                  parseFloat(selectedOrder?.total_harga).toLocaleString("id-ID")
                }}
              </p>
            </div>
          </div>

          <div
            v-if="selectedOrder?.resi"
            class="bg-blue-50 p-3 rounded-lg border border-blue-100 mt-2"
          >
            <p class="text-sm text-blue-800">
              <span class="font-bold">Nomor Resi:</span>
              {{ selectedOrder.resi }}
            </p>
          </div>
        </div>
        <div class="p-5 border-t border-gray-200 text-right">
          <button
            @click="closeModal"
            class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Input Resi -->
    <div
      v-if="showResiModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-xl w-full max-w-md" @click.stop>
        <div class="p-5 border-b border-gray-200">
          <h2 class="text-lg font-bold text-blue-800">Masukkan Nomor Resi</h2>
          <p class="text-sm text-gray-600 mt-1">
            Pesanan: ORD-{{ String(selectedOrderForResi?.id).padStart(4, "0") }}
          </p>
        </div>
        <div class="p-5">
          <label class="block text-gray-700 text-sm font-medium mb-2"
            >Nomor Resi</label
          >
          <input
            v-model="resiInput"
            type="text"
            placeholder="Contoh: JNE123456789ID"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300"
            :class="resiError ? 'border-red-500' : ''"
          />
          <p v-if="resiError" class="mt-1 text-red-500 text-sm">
            {{ resiError }}
          </p>
        </div>
        <div class="p-5 border-t border-gray-200 flex justify-end gap-2">
          <button
            @click="showResiModal = false"
            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg"
          >
            Batal
          </button>
          <button
            @click="handleKirimWithResi"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            Kirim Pesanan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

// State
const orders = ref([]);
const loading = ref(false);
const error = ref(false);
const searchQuery = ref("");
const selectedStatus = ref("all");
const showModal = ref(false);
const selectedOrder = ref(null);
const showResiModal = ref(false);
const selectedOrderForResi = ref(null);
const resiInput = ref("");
const resiError = ref("");

// Fetch data
onMounted(() => {
  loadOrders();
});

const loadOrders = async () => {
  loading.value = true;
  error.value = false;
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/manage/orders");
    if (response.data.success) {
      orders.value = response.data.data || [];
    } else {
      error.value = true;
    }
  } catch (err) {
    console.error("API Error:", err);
    error.value = true;
  } finally {
    loading.value = false;
  }
};

// Filter
const filteredOrders = computed(() => {
  let result = [...orders.value];
  if (selectedStatus.value !== "all") {
    result = result.filter((o) => o.status === selectedStatus.value);
  }
  if (searchQuery.value) {
    const q = searchQuery.value.trim();
    result = result.filter((o) => o.id.toString().includes(q));
  }
  return result;
});

const applySearch = () => {}; // computed already reactive

// Utils
const formatDate = (isoDate) => {
  if (!isoDate) return "-";
  return new Date(isoDate).toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getStatusLabel = (status) => {
  const map = {
    pending: "Menunggu Pembayaran",
    paid: "Dibayar",
    confirmed: "Dikonfirmasi",
    packed: "Dikemas",
    shipped: "Dikirim",
    completed: "Selesai",
    cancelled: "Dibatalkan",
    invalid: "Invalid",
    cancellation_requested: "Pengajuan Pembatalan",
  };
  return map[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    pending: "bg-orange-100 text-orange-800",
    paid: "bg-green-100 text-green-800",
    confirmed: "bg-blue-100 text-blue-800",
    packed: "bg-purple-100 text-purple-800",
    shipped: "bg-blue-100 text-blue-800",
    completed: "bg-emerald-100 text-emerald-800",
    cancelled: "bg-red-100 text-red-800",
    invalid: "bg-red-100 text-red-800",
    cancellation_requested: "bg-yellow-100 text-yellow-800",
  };
  return classes[status] || "bg-gray-100 text-gray-800";
};

// Modal
const openDetailModal = (order) => {
  selectedOrder.value = order;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedOrder.value = null;
};

// Resi Modal
const openResiModal = (order) => {
  selectedOrderForResi.value = order;
  resiInput.value = order.resi || "";
  resiError.value = "";
  showResiModal.value = true;
};

const handleKirimWithResi = () => {
  if (!resiInput.value.trim()) {
    resiError.value = "Nomor resi wajib diisi";
    return;
  }
  resiError.value = "";

  submitStatusUpdate(
    selectedOrderForResi.value,
    "shipped",
    resiInput.value.trim()
  );
  showResiModal.value = false;
};

// Update Status
const updateStatus = (order, newStatus) => {
  const actions = {
    confirmed: "Konfirmasi Pesanan",
    packed: "Kemas Pesanan",
  };
  const action = actions[newStatus] || "Perbarui Status";

  if (!confirm(`Apakah Anda yakin ingin ${action.toLowerCase()}?`)) return;
  submitStatusUpdate(order, newStatus);
};

const approveCancellation = async (order) => {
  if (!confirm("Apakah Anda yakin ingin menyetujui pembatalan ini?")) return;
  try {
    await axios.post(
      `http://127.0.0.1:8000/api/orders/${order.id}/approve-cancellation`
    );
    alert("Pembatalan disetujui.");
    loadOrders();
  } catch (err) {
    console.error("Gagal menyetujui pembatalan:", err);
    alert("Gagal memproses persetujuan.");
  }
};

const rejectCancellation = async (order) => {
  if (!confirm("Apakah Anda yakin ingin menolak pembatalan ini?")) return;
  try {
    await axios.post(
      `http://127.0.0.1:8000/api/orders/${order.id}/reject-cancellation`
    );
    alert("Pembatalan ditolak.");
    loadOrders();
  } catch (err) {
    console.error("Gagal menolak pembatalan:", err);
    alert("Gagal memproses penolakan.");
  }
};

const submitStatusUpdate = async (order, status, resi = null) => {
  try {
    const payload = { status };
    if (resi) payload.resi = resi;

    await axios.patch(
      `http://127.0.0.1:8000/api/order/${order.id}/status`,
      payload
    );

    // Update UI
    order.status = status;
    if (resi) order.resi = resi;

    alert("Status berhasil diperbarui!");
  } catch (err) {
    console.error("Gagal update status:", err);
    alert("Gagal memperbarui status pesanan");
  }
};

// Status options
const statusOptions = [
  { value: "all", label: "Semua Status" },
  { value: "pending", label: "Menunggu Pembayaran" },
  { value: "paid", label: "Dibayar" },
  { value: "confirmed", label: "Dikonfirmasi" },
  { value: "packed", label: "Dikemas" },
  { value: "shipped", label: "Dikirim" },
  { value: "completed", label: "Selesai" },
  { value: "cancellation_requested", label: "Pengajuan Pembatalan" },
  { value: "cancelled", label: "Dibatalkan" },
  { value: "invalid", label: "Invalid" },
];
</script>
