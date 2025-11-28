<template>
  <div class="min-h-screen bg-gray-50 font-ubuntu">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Kelola Toko Saya</h1>
        <button class="px-4 py-2 bg-[#e84797] text-white rounded-lg hover:bg-[#d03a84] transition shadow-sm flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Produk
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="text-gray-500 text-sm font-medium mb-1">Total Produk</div>
          <div class="text-3xl font-bold text-gray-900">12</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="text-gray-500 text-sm font-medium mb-1">Total Pesanan</div>
          <div class="text-3xl font-bold text-gray-900">45</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="text-gray-500 text-sm font-medium mb-1">Pendapatan Bulan Ini</div>
          <div class="text-3xl font-bold text-gray-900">Rp 4.500.000</div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-800">Daftar Produk</h2>
          <div class="relative">
            <input type="text" placeholder="Cari produk..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#e84797] focus:border-transparent">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-semibold uppercase tracking-wider">
              <tr>
                <th class="px-6 py-3">Produk</th>
                <th class="px-6 py-3">Harga</th>
                <th class="px-6 py-3">Stok</th>
                <th class="px-6 py-3">Terjual</th>
                <th class="px-6 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <!-- Static Data Row 1 -->
              <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                      <img src="https://via.placeholder.com/50" alt="Product" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">RTX 4090 Gaming OC</div>
                      <div class="text-xs text-gray-500">Kategori: GPU</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 font-medium">Rp 28.000.000</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Tersedia (5)</span>
                </td>
                <td class="px-6 py-4">12</td>
                <td class="px-6 py-4 text-right">
                  <button class="text-blue-600 hover:text-blue-800 font-medium mr-3">Edit</button>
                  <button class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                </td>
              </tr>
              <!-- Static Data Row 2 -->
              <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                      <img src="https://via.placeholder.com/50" alt="Product" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">Intel Core i9-13900K</div>
                      <div class="text-xs text-gray-500">Kategori: CPU</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 font-medium">Rp 9.500.000</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">Menipis (2)</span>
                </td>
                <td class="px-6 py-4">25</td>
                <td class="px-6 py-4 text-right">
                  <button class="text-blue-600 hover:text-blue-800 font-medium mr-3">Edit</button>
                  <button class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
            <span class="text-sm text-gray-500">Menampilkan 1-2 dari 12 produk</span>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm disabled:opacity-50" disabled>Previous</button>
                <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 text-sm">Next</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Logic placeholder
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap");

.font-ubuntu {
  font-family: "Ubuntu", sans-serif;
}
</style>
