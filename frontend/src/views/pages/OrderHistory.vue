<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold mb-8 text-gray-800">Riwayat Pembelian</h1>

      <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Nama Toko
                </th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Produk
                </th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Jumlah
                </th>
                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Total Harga
                </th>
                <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="purchasedItems.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                  Belum ada riwayat pembelian.
                </td>
              </tr>
              <tr v-for="(item, index) in purchasedItems" :key="index" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ item.toko_nama }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 font-medium">{{ item.produk_nama }}</div>
                  <div class="text-xs text-gray-500 mt-0.5" v-if="item.variant_nama">
                    Varian: {{ item.variant_nama }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="text-sm text-gray-900">{{ item.kuantitas }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="text-sm font-medium text-gray-900">
                    Rp {{ formatPrice(item.total_harga) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span
                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': item.status === 'completed' || item.status === 'success',
                      'bg-yellow-100 text-yellow-800': item.status === 'pending',
                      'bg-red-100 text-red-800': item.status === 'cancelled' || item.status === 'failed',
                      'bg-blue-100 text-blue-800': item.status === 'shipped' || item.status === 'processing'
                    }"
                  >
                    {{ item.status }}
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
  
  // Sort by newest order (assuming order array is already sorted or we rely on insertion order)
  return items;
});

const formatPrice = (value) => {
  return new Intl.NumberFormat("id-ID").format(value);
};

onMounted(() => {
  fetchOrders();
});
</script>
