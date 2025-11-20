<!-- src/views/admin/Konfirmasi.vue -->
<template>
  <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in max-w-5xl mx-auto">
    <h1 class="text-xl font-bold text-blue-900 mb-4">Konfirmasi Permintaan Seller</h1>

    <!-- Search Bar -->
    <div class="mb-6 p-3 bg-pink-100 rounded-lg flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Cari ID, nama toko, atau nama seller..."
        class="bg-transparent outline-none flex-1 text-blue-900 placeholder-blue-600"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-8 text-center">
      <p class="text-gray-600">Memuat permintaan...</p>
    </div>

    <!-- List Konfirmasi -->
    <div v-else class="space-y-4">
      <div
        v-for="seller in filteredSellers"
        :key="seller.id"
        class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow duration-200"
      >
        <!-- Header -->
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-semibold text-blue-900">{{ seller.store_name || seller.storeName }}</h3>
            <p class="text-sm text-gray-600">ID User: {{ seller.id }}</p>
          </div>
          <div class="flex space-x-2">
            <button
              @click="acceptSeller(seller.id)"
              :disabled="seller.processing"
              class="px-3 py-1 bg-blue-900 text-white rounded hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ seller.processing ? 'Memproses...' : 'Terima' }}
            </button>
            <button
              @click="rejectSeller(seller.id)"
              :disabled="seller.processing"
              class="px-3 py-1 bg-pink-500 text-white rounded hover:bg-pink-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ seller.processing ? 'Memproses...' : 'Tolak' }}
            </button>
          </div>
        </div>

        <!-- Detail -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div class="font-medium text-gray-700">Nama Seller:</div>
            <div class="text-gray-900">{{ seller.name || seller.sellerName }}</div>
          </div>
          <div>
            <div class="font-medium text-gray-700">Email:</div>
            <div class="text-gray-900">{{ seller.email }}</div>
          </div>
          <div>
            <div class="font-medium text-gray-700">Alamat Toko:</div>
            <div class="text-gray-900">{{ seller.address }}</div>
          </div>
          <div>
            <div class="font-medium text-gray-700">Deskripsi Toko:</div>
            <div class="text-gray-900 line-clamp-2">{{ seller.description }}</div>
          </div>
        </div>

        <!-- Dokumen (placeholder — opsional dikembangkan nanti) -->
        <div class="mt-4 pt-4 border-t border-gray-200">
          <p class="text-xs text-gray-500 italic">
            ⚠️ Upload file (KTP/NPWP) belum diimplementasi — akan ditambahkan di versi berikutnya.
          </p>
        </div>
      </div>
    </div>

    <!-- No Data -->
    <div v-if="!loading && filteredSellers.length === 0" class="py-8 text-center text-gray-500">
      Tidak ada permintaan konfirmasi.
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ✅ State
const sellers = ref([])
const searchQuery = ref('')
const loading = ref(false)

// 🔁 Fetch data saat mount
onMounted(async () => {
  await fetchSellerRequests()
})

const fetchSellerRequests = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/role/seller-requests')
    // Pastikan setiap item punya `processing` flag
    sellers.value = res.data.map(s => ({
      ...s,
      processing: false
    }))
  } catch (error) {
    console.error('Gagal memuat permintaan seller:', error)
    alert('Gagal memuat data. Coba lagi nanti.')
  } finally {
    loading.value = false
  }
}

// 🔍 Filter
const filteredSellers = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return sellers.value

  return sellers.value.filter(s =>
    s.id.toString().includes(query) ||
    (s.store_name || '').toLowerCase().includes(query) ||
    (s.name || '').toLowerCase().includes(query) ||
    s.email.toLowerCase().includes(query)
  )
})

// ✅ Terima seller
const acceptSeller = async (id) => {
  if (!confirm('Yakin terima permintaan seller ini?')) return

  const seller = sellers.value.find(s => s.id === id)
  if (!seller) return

  seller.processing = true

  try {
    await axios.post(`/api/role/approve-seller/${id}`)
    alert('✅ Seller berhasil disetujui!')
    // Hapus dari daftar lokal
    sellers.value = sellers.value.filter(s => s.id !== id)
  } catch (error) {
    console.error('Gagal menyetujui seller:', error.response?.data || error)
    alert('❌ Gagal menyetujui. Coba lagi.')
  } finally {
    seller.processing = false
  }
}

// ❌ Tolak seller
const rejectSeller = async (id) => {
  if (!confirm('Yakin tolak permintaan seller ini?')) return

  const seller = sellers.value.find(s => s.id === id)
  if (!seller) return

  seller.processing = true

  try {
    await axios.post(`/api/role/reject-seller/${id}`)
    alert('❌ Permintaan seller ditolak.')
    sellers.value = sellers.value.filter(s => s.id !== id)
  } catch (error) {
    console.error('Gagal menolak seller:', error.response?.data || error)
    alert('❌ Gagal menolak. Coba lagi.')
  } finally {
    seller.processing = false
  }
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>