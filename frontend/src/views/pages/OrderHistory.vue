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
                  Nama Toko
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Produk
                </th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Jumlah
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Total Harga
                </th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-if="purchasedItems.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-lg">
                  Belum ada riwayat pembelian.
                </td>
              </tr>
              <tr v-for="(item, index) in purchasedItems" :key="index" class="hover:bg-blue-50 transition-colors">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-medium text-blue-800">{{ item.toko_nama }}</div>
                </td>
                <td class="px-6 py-5">
                  <div class="text-sm font-medium text-gray-800">{{ item.produk_nama }}</div>
                  <div class="text-xs text-pink-600 mt-1" v-if="item.variant_nama">
                    Varian: {{ item.variant_nama }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-center">
                  <div class="text-sm text-gray-800 font-medium">{{ item.kuantitas }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right">
                  <div class="text-sm font-bold text-gray-800">
                    Rp {{ formatPrice(item.total_harga) }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-center">
                  <span
                    class="px-3 py-1 inline-flex text-xs font-semibold rounded-full"
                    :class="{
                      'bg-blue-100 text-blue-800': ['completed', 'success'].includes(item.status),
                      'bg-pink-100 text-pink-700': item.status === 'pending',
                      'bg-red-100 text-red-700': ['cancelled', 'failed'].includes(item.status),
                      'bg-blue-50 text-blue-700': ['shipped', 'processing', 'paid', 'confirmed', 'packed'].includes(item.status)
                    }"
                  >
                    {{ getStatusLabel(item.status) }}
                  </span>
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
import { ref, onMounted, computed } from "vue";
import axios from "axios";

const orders = ref([]);

const fetchOrders = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/order/history");
    orders.value = res.data.data;
  } catch (err) {
    console.error("Gagal mengambil riwayat pesanan:", err);
  }
};

const purchasedItems = computed(() => {
  const items = [];
  if (!orders.value) return items;

  orders.value.forEach((order) => {
    if (order.detail_pesanans) {
      order.detail_pesanans.forEach((detail) => {
        items.push({
          toko_nama: order.toko?.nama_toko || "Toko Tidak Diketahui",
          produk_nama: detail.variant?.product?.nama_produk || "Produk Tidak Diketahui",
          variant_nama: detail.variant?.nama_varian,
          kuantitas: detail.kuantitas,
          total_harga: detail.harga * detail.kuantitas,
          status: order.status,
        });
      });
    }
  });
  
  return items;
});

const formatPrice = (value) => {
  return new Intl.NumberFormat("id-ID").format(value);
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