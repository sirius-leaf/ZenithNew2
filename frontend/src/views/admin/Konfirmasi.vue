<!-- src/components/admin/Konfirmasi.vue -->
<template>
  <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in max-w-5xl mx-auto">
    <h1 class="text-xl font-bold text-blue-900 mb-4">Konfirmasi</h1>

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

    <!-- List Konfirmasi -->
    <div class="space-y-4">
      <div
        v-for="seller in filteredSellers"
        :key="seller.id"
        class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow duration-200"
      >
        <!-- Header -->
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-semibold text-blue-900">{{ seller.storeName }}</h3>
            <p class="text-sm text-gray-600">ID User: {{ seller.userId }}</p>
          </div>
          <div class="flex space-x-2">
            <button
              @click="acceptSeller(seller.id)"
              class="px-3 py-1 bg-blue-900 text-white rounded hover:bg-blue-800 transition-colors"
            >
              Terima
            </button>
            <button
              @click="rejectSeller(seller.id)"
              class="px-3 py-1 bg-pink-500 text-white rounded hover:bg-pink-600 transition-colors"
            >
              Tolak
            </button>
          </div>
        </div>

        <!-- Detail -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div class="font-medium text-gray-700">Nama Seller:</div>
            <div class="text-gray-900">{{ seller.sellerName }}</div>
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

        <!-- Dokumen -->
        <div class="mt-4 pt-4 border-t border-gray-200">
          <div class="flex space-x-4">
            <div class="flex-1">
              <div class="font-medium text-gray-700">KTP:</div>
              <img
                :src="seller.ktpUrl || 'https://placehold.co/150x100?text=KTP+Belum+Diunggah'"
                alt="KTP"
                class="mt-2 w-full max-w-[150px] h-auto rounded border"
              />
            </div>
            <div class="flex-1">
              <div class="font-medium text-gray-700">NPWP:</div>
              <img
                :src="seller.npwpUrl || 'https://placehold.co/150x100?text=NPWP+Belum+Diunggah'"
                alt="NPWP"
                class="mt-2 w-full max-w-[150px] h-auto rounded border"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Data -->
    <div v-if="filteredSellers.length === 0" class="py-8 text-center text-gray-500">
      Tidak ada permintaan konfirmasi.
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-6 space-x-2">
      <button @click="currentPage = 1" :class="['px-3 py-1 rounded', currentPage === 1 ? 'bg-blue-900 text-white' : 'bg-blue-900/20 text-blue-900']">1</button>
      <button @click="currentPage = 2" :class="['px-3 py-1 rounded', currentPage === 2 ? 'bg-blue-900 text-white' : 'bg-blue-900/20 text-blue-900']">2</button>
      <button @click="currentPage = 3" :class="['px-3 py-1 rounded', currentPage === 3 ? 'bg-blue-900 text-white' : 'bg-blue-900/20 text-blue-900']">3</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const sellers = [
  {
    id: 2,
    userId: 2,
    storeName: 'Toko A',
    sellerName: 'Ahn Keonho',
    email: 'ahnkeonho@zenith.com',
    address: 'Jl. Sudirman No. 123, Jakarta Pusat',
    description: 'Toko yang menjual gadget terbaru dengan harga bersaing dan layanan ramah.',
    ktpUrl: 'https://placehold.co/150x100?text=KTP+Keonho',
    npwpUrl: 'https://placehold.co/150x100?text=NPWP+Keonho'
  },
  {
    id: 3,
    userId: 3,
    storeName: 'Toko B',
    sellerName: 'Jang Wonyoung',
    email: 'jangwonyoung@zenith.com',
    address: 'Jl. Thamrin No. 456, Jakarta Selatan',
    description: 'Spesialis laptop dan aksesoris komputer. Garansi resmi dan pengiriman cepat.',
    ktpUrl: 'https://placehold.co/150x100?text=KTP+Wonyoung',
    npwpUrl: 'https://placehold.co/150x100?text=NPWP+Wonyoung'
  },
  {
    id: 4,
    userId: 4,
    storeName: 'Toko C',
    sellerName: 'Kang Haerin',
    email: 'kanghaerin@zenith.com',
    address: 'Jl. Gatot Subroto No. 789, Jakarta Timur',
    description: 'Menjual smartphone dan tablet premium. Harga grosir untuk reseller.',
    ktpUrl: 'https://placehold.co/150x100?text=KTP+Haerin',
    npwpUrl: 'https://placehold.co/150x100?text=NPWP+Haerin'
  }
]

const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 3

const filteredSellers = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  let result = sellers

  if (query) {
    result = sellers.filter(s =>
      s.id.toString().includes(query) ||
      s.storeName.toLowerCase().includes(query) ||
      s.sellerName.toLowerCase().includes(query) ||
      s.email.toLowerCase().includes(query)
    )
  }

  const start = (currentPage.value - 1) * itemsPerPage
  return result.slice(start, start + itemsPerPage)
})

const acceptSeller = (id) => {
  console.log('Terima seller:', id)
  // Tambahkan logika API di sini
}

const rejectSeller = (id) => {
  console.log('Tolak seller:', id)
  // Tambahkan logika API di sini
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

/* Responsive: limit height of description */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>