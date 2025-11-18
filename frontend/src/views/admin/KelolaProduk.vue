<!-- src/components/admin/KelolaProduk.vue -->
<template>
  <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in max-w-5xl mx-auto">
    <h1 class="text-xl font-bold text-blue-900 mb-4">Kelola Produk Seller</h1>

    <!-- Search Bar + Kategori Dropdown -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
      <div class="flex-1 relative">
        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari Produk, Toko..."
          class="w-full pl-10 pr-4 py-2 bg-pink-100 border-none rounded-lg text-blue-900 outline-none"
        />
      </div>
      <div class="md:w-48">
        <select
          v-model="selectedCategory"
          class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-pink-500"
        >
          <option value="">Semua Kategori</option>
          <option value="Smartphone">Smartphone</option>
          <option value="Laptop">Laptop</option>
          <option value="Tablet">Tablet</option>
          <option value="Aksesoris">Aksesoris</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-pink-500 text-white">
            <th class="py-2 px-4 text-left">ID</th>
            <th class="py-2 px-4 text-left">Nama Toko</th>
            <th class="py-2 px-4 text-left">Produk</th>
            <th class="py-2 px-4 text-left">Kategori</th>
            <th class="py-2 px-4 text-left">Stok</th>
            <th class="py-2 px-4 text-left">Harga</th>
            <th class="py-2 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="product in filteredProducts"
            :key="product.id"
            class="border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200"
          >
            <td class="py-2 px-4">{{ product.id }}</td>
            <td class="py-2 px-4">{{ product.storeName }}</td>
            <td class="py-2 px-4">{{ product.productName }}</td>
            <td class="py-2 px-4">{{ product.category }}</td>
            <td class="py-2 px-4">{{ product.stock }}</td>
            <td class="py-2 px-4">{{ formatCurrency(product.price) }}</td>
            <td class="py-2 px-4 text-right">
              <button class="text-red-600 hover:text-red-800 mr-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A1 1 0 0117.133 21H6.867A1 1 0 016 19.133L4.867 7H19zm-1 8.133L18 19H6l-1.133-4.133A1 1 0 015 14v-2a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1z" />
                </svg>
              </button>
              <button class="text-orange-600 hover:text-orange-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-5.414a2 2 0 012.828 0L18 10.828a2 2 0 010 2.828l-8 8a2 2 0 01-2.828 0l-4-4a2 2 0 012.828-2.828l4 4Z" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- No data -->
      <div v-if="filteredProducts.length === 0" class="py-8 text-center text-gray-500">
        Tidak ada produk ditemukan.
      </div>
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

const products = [
  { id: 2, storeName: 'Samsung Indonesia', productName: 'Samsung A55 5G', category: 'Smartphone', stock: 150, price: 4999000 },
  { id: 3, storeName: 'Samsung Indonesia', productName: 'Samsung A55 5G', category: 'Smartphone', stock: 80, price: 4999000 },
  { id: 4, storeName: 'Samsung Indonesia', productName: 'Samsung A55 5G', category: 'Smartphone', stock: 200, price: 4999000 },
  { id: 5, storeName: 'Apple Store Jakarta', productName: 'iPhone 15 Pro', category: 'Smartphone', stock: 50, price: 18999000 },
  { id: 6, storeName: 'Dell Official', productName: 'Dell XPS 13', category: 'Laptop', stock: 30, price: 24999000 }
]

const searchQuery = ref('')
const selectedCategory = ref('')
const currentPage = ref(1)
const itemsPerPage = 3

const filteredProducts = computed(() => {
  let result = products.filter(p =>
    p.id.toString().includes(searchQuery.value) ||
    p.storeName.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    p.productName.toLowerCase().includes(searchQuery.value.toLowerCase())
  )

  if (selectedCategory.value) {
    result = result.filter(p => p.category === selectedCategory.value)
  }

  const start = (currentPage.value - 1) * itemsPerPage
  return result.slice(start, start + itemsPerPage)
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
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
</style>