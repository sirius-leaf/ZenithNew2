<template>
  <div class="font-ubuntu min-h-screen bg-white p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="flex items-center gap-4 mb-6">
        <button @click="$router.back()" class="text-blue-600 hover:text-blue-800">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <h1 class="text-2xl font-bold text-blue-800">Detail Pesanan</h1>
      </div>

      <!-- Loading & Error -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-pink-500"></div>
        <p class="mt-2 text-gray-600">Memuat detail pesanan...</p>
      </div>
      <div v-else-if="error" class="text-center py-12 text-pink-600">
        {{ error }}
      </div>

      <!-- Konten Utama -->
      <div v-else class="space-y-6">
        <!-- Informasi Toko -->
        <!-- Informasi Toko (Simplified) -->
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <div>
            <p class="text-lg font-bold text-gray-800">ID Pesanan: #{{ String(order.id).padStart(4, '0') }}</p>
          </div>
        </div>

        <!-- Status & Ringkasan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Status -->
          <div class="bg-white rounded-xl shadow border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-3">Status Pesanan</h3>
            <div class="flex items-center gap-2">
              <span
                class="px-3 py-1 text-xs font-semibold rounded-full"
                :class="{
                  'bg-blue-100 text-blue-800': ['completed', 'success'].includes(order.status),
                  'bg-pink-100 text-pink-700': order.status === 'pending',
                  'bg-red-100 text-red-700': ['cancelled', 'failed'].includes(order.status),
                  'bg-blue-50 text-blue-700': ['shipped', 'processing', 'paid', 'confirmed', 'packed'].includes(order.status)
                }"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-600 mt-3">
              Dibuat pada: {{ formatDate(order.created_at) }}
            </p>
          </div>

          <!-- Ringkasan Pembayaran -->
          <div class="bg-white rounded-xl shadow border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-700 mb-3">Ringkasan</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Total Item</span>
                <span class="font-medium">{{ getTotalItems }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Metode Bayar</span>
                <span class="font-medium text-blue-800">
                  {{ order.payment_method === 'cod' ? 'COD' : 'Pembayaran Online (Midtrans)' }}
                </span>
              </div>
              <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                <span class="font-bold text-gray-800">Total Bayar</span>
                <span class="font-bold text-blue-800">Rp {{ parseFloat(order.total_harga).toLocaleString('id-ID') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Alamat Pengiriman -->
        <div class="bg-white rounded-xl shadow border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-700 mb-3">Alamat Pengiriman</h3>
          <p class="text-gray-800">{{ order.alamat_pengiriman || '–' }}</p>
        </div>

        <!-- Daftar Produk -->
        <div class="bg-white rounded-xl shadow border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-700 mb-4">Produk</h3>
          <div class="space-y-4">
            <div
              v-for="(item, idx) in order.detail_pesanans"
              :key="idx"
              class="flex gap-4"
            >
              <img
                :src="item.variant?.gambar_varian ? `http://127.0.0.1:8000/storage/${item.variant.gambar_varian}` : 'https://via.placeholder.com/150'"
                alt="Product Image"
                class="w-16 h-16 rounded-lg object-cover flex-shrink-0 border border-gray-200"
              />
              <div class="flex-1">
                <h4 class="font-medium text-gray-800">
                  {{ item.variant?.product?.nama_produk || 'Produk Tidak Diketahui' }}
                </h4>
                <p v-if="item.variant?.nama_varian" class="text-sm text-pink-600 mt-0.5">
                  Varian: {{ item.variant.nama_varian }}
                </p>
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  {{ order.toko?.toko_name }}
                </p>
                <div class="flex justify-between mt-2">
                  <span class="text-sm text-gray-600">{{ item.kuantitas }} item</span>
                  <span class="font-medium text-gray-800">
                    Rp {{ (parseFloat(item.harga) * item.kuantitas).toLocaleString('id-ID') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Nomor Resi (jika ada) -->
        <div v-if="order.resi" class="bg-white rounded-xl shadow border border-gray-200 p-5">
          <h3 class="font-semibold text-gray-700 mb-2">Nomor Resi</h3>
          <p class="text-blue-800 font-medium">{{ order.resi }}</p>
        </div>

        <!-- Aksi: Konfirmasi Diterima -->
        <div v-if="order.status === 'shipped'" class="text-center">
          <button
            @click="confirmReceived"
            class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-6 rounded-lg transition"
          >
            Konfirmasi Pesanan Diterima
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const orderId = route.params.id

const order = ref(null)
const loading = ref(true)
const error = ref(null)

const fetchOrder = async () => {
  loading.value = true
  error.value = null
  try {
    const token = localStorage.getItem('authToken')
    const response = await axios.get(`http://127.0.0.1:8000/api/orders/${orderId}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    order.value = response.data.data
  } catch (err) {
    console.error(err)
    error.value = 'Gagal memuat detail pesanan.'
  } finally {
    loading.value = false
  }
}

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
}

const formatDate = (isoDate) => {
  if (!isoDate) return '-'
  return new Date(isoDate).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTotalItems = computed(() => {
  if (!order.value?.detail_pesanans) return 0
  return order.value.detail_pesanans.reduce((sum, item) => sum + item.kuantitas, 0)
})

const confirmReceived = async () => {
  if (!confirm('Apakah Anda yakin telah menerima pesanan ini?')) return

  try {
    const token = localStorage.getItem('authToken')
    await axios.patch(
      `http://127.0.0.1:8000/api/order/${orderId}/status`,
      { status: 'completed' },
      { headers: { Authorization: `Bearer ${token}` } }
    )
    alert('Pesanan berhasil dikonfirmasi sebagai diterima!')
    order.value.status = 'completed'
  } catch (err) {
    alert('Gagal mengonfirmasi penerimaan pesanan.')
  }
}

onMounted(() => {
  fetchOrder()
})
</script>