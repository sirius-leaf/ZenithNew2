<template>
  <div class="font-ubuntu p-6 min-h-screen bg-white">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold mb-8 text-blue-800">Riwayat Pembelian</h1>

      <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  ID Pesanan
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Toko
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Tanggal
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Total Harga
                </th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-if="orders.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-lg">
                  Belum ada riwayat pembelian.
                </td>
              </tr>
              <tr v-for="order in orders" :key="order.id" class="hover:bg-blue-50 transition-colors">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">#{{ String(order.id).padStart(4, '0') }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-medium text-blue-800">{{ order.toko?.toko_name || "Toko Tidak Diketahui" }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm text-gray-600">{{ formatDate(order.created_at) }}</div>
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
                      'bg-blue-100 text-blue-800': ['completed', 'success'].includes(order.status),
                      'bg-pink-100 text-pink-700': order.status === 'pending',
                      'bg-red-100 text-red-700': ['cancelled', 'failed'].includes(order.status),
                      'bg-blue-50 text-blue-700': ['shipped', 'processing', 'paid', 'confirmed', 'packed'].includes(order.status)
                    }"
                  >
                    {{ getStatusLabel(order.status) }}
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-center">
                  <router-link
                    :to="`/riwayat/${order.id}`"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    Lihat Detail
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const orders = ref([]);

const fetchOrders = async () => {
  try {
    const token = localStorage.getItem("authToken");
    const res = await axios.get("http://127.0.0.1:8000/api/order/history", {
      headers: { Authorization: `Bearer ${token}` }
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

const getStatusLabel = (status) => {
  const map = {
    pending: 'Menunggu',
    paid: 'Dibayar',
    confirmed: 'Dikonfirmasi',
    packed: 'Dikemas',
    shipped: 'Dikirim',
    completed: 'Selesai',
    success: 'Berhasil',
    cancelled: 'Dibatalkan',
    failed: 'Gagal',
    processing: 'Diproses'
  }
  return map[status] || status
};

onMounted(() => {
  fetchOrders();
});
</script>