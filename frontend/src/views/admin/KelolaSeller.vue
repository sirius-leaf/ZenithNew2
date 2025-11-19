<!-- src/components/admin/KelolaSeller.vue -->
<template>
  <div class="bg-white rounded-xl shadow-md p-4 md:p-6 animate-fade-in max-w-5xl mx-auto mt-8">
    <h1 class="text-xl md:text-2xl font-bold text-blue-900 mb-6">Kelola Seller</h1>

    <!-- Search Bar -->
    <div class="mb-6">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by ID, store name, seller name, or email..."
          class="w-full pl-10 pr-4 py-3 border border-pink-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-transparent text-blue-900 placeholder-blue-500 bg-pink-50/30 transition"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full text-sm">
        <thead class="bg-pink-500 text-white">
          <tr>
            <th class="py-3 px-4 text-left font-medium">ID User</th>
            <th class="py-3 px-4 text-left font-medium">Nama Toko</th>
            <th class="py-3 px-4 text-left font-medium">Nama Seller</th>
            <th class="py-3 px-4 text-left font-medium">Email</th>
            <th class="py-3 px-4 text-right font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="seller in filteredSellers"
            :key="seller.id"
            class="hover:bg-pink-50/50 transition-colors duration-150"
          >
            <td class="py-3 px-4 font-mono text-blue-900">{{ seller.Id }}</td>
            <td class="py-3 px-4 font-medium text-blue-900">{{ seller.storeName }}</td>
            <td class="py-3 px-4">{{ seller.sellerName }}</td>
            <td class="py-3 px-4 text-blue-700 truncate max-w-xs">{{ seller.email }}</td>
            <td class="py-3 px-4 text-right space-x-2">
              <button
                class="p-2 rounded-full text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-150"
                title="Delete"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A1 1 0 0117.133 21H6.867A1 1 0 016 19.133L4.867 7H19zm-1 8.133L18 19H6l-1.133-4.133A1 1 0 015 14v-2a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1z" />
                </svg>
              </button>
              <button
                class="p-2 rounded-full text-orange-600 hover:bg-orange-50 hover:text-orange-700 transition-colors duration-150"
                title="Edit"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-5.414a2 2 0 012.828 0L18 10.828a2 2 0 010 2.828l-8 8a2 2 0 01-2.828 0l-4-4a2 2 0 012.828-2.828l4 4Z" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- No data -->
      <div v-if="filteredSellers.length === 0" class="py-10 px-4 text-center text-gray-500 bg-gray-50 rounded-b-lg">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.485 0-4.5-1.276-5.5-2.828" />
        </svg>
        <p class="mt-3">No seller found.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-6 space-x-1">
      <button
        v-for="page in [1, 2, 3]"
        :key="page"
        :class="[
          'px-3.5 py-1.5 text-sm font-medium rounded-md transition-colors',
          page === 1
            ? 'bg-pink-500 text-white'
            : 'bg-pink-100 text-pink-700 hover:bg-pink-200'
        ]"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const sellers = [
  { id: 1, Id: 2, storeName: 'Toko A', sellerName: 'Keonho', email: 'ahnkeonho@zenith.com' },
  { id: 2, Id: 3, storeName: 'Toko B', sellerName: 'Jake', email: 'shimjake@zenith.com' },
  { id: 3, Id: 4, storeName: 'Toko C', sellerName: 'Yuha', email: 'Yuha@zenith.com' },
  { id: 4, Id: 1, storeName: 'Toko Budi', sellerName: 'Budi Santoso', email: 'budi.santoso@email.com' }
]

const searchQuery = ref('')
const filteredSellers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return sellers
  return sellers.filter(s =>
    s.Id.toString().includes(q) ||
    s.storeName.toLowerCase().includes(q) ||
    s.sellerName.toLowerCase().includes(q) ||
    s.email.toLowerCase().includes(q)
  )
})
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>