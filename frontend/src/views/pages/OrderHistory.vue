<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Riwayat Pesanan</h1>

    <div v-for="order in orders" :key="order.id">
      <table class="min-w-full border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="p-2 border">Toko ID</th>
            <th class="p-2 border">Total Harga</th>
            <th class="p-2 border">Status</th>
            <th class="p-2 border">Alamat Pengiriman</th>
            <th class="p-2 border">Detail</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="p-2 border">{{ order.toko_id }}</td>
            <td class="p-2 border">{{ order.total_harga }}</td>
            <td class="p-2 border">{{ order.status }}</td>
            <td class="p-2 border">{{ order.alamat_pengiriman }}</td>
            <td class="p-2 border">
              <button
                @click="toggleDetail(order.id)"
                class="px-3 py-1 bg-blue-500 text-white rounded"
              >
                Lihat
              </button>
            </td>
          </tr>

          <!-- detail pesanan -->
          <tr>
            <td colspan="5" class="p-4 bg-gray-50 border">
              <h2 class="font-semibold mb-2">Detail Pesanan</h2>
              <table class="min-w-full border border-gray-300">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="p-2 border">ID Varian</th>
                    <th class="p-2 border">Kuantitas</th>
                    <th class="p-2 border">Harga</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="detail in order.detail_pesanans">
                    <td class="p-2 border">
                      {{ detail.id_varian }}
                    </td>
                    <td class="p-2 border">
                      {{ detail.kuantitas }}
                    </td>
                    <td class="p-2 border">
                      {{ detail.harga }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const orders = ref([]);
const expandedOrder = ref(null);

const fetchOrders = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/order/history");
    orders.value = res.data.data;
    console.log(orders.value);
  } catch (err) {
    console.error(err);
  }
};

const toggleDetail = (id) => {
  expandedOrder.value = expandedOrder.value === id ? null : id;
};

onMounted(() => {
  fetchOrders();
});
</script>
