<!-- src/components/admin/KelolaUser.vue -->
<template>
  <div class="bg-white rounded-xl shadow-md p-4 md:p-6 animate-fade-in max-w-5xl mx-auto">
    <h1 class="text-xl font-bold text-blue-900 mb-4">Kelola User</h1>

    <!-- Search Bar -->
    <div class="mb-6 p-3 bg-pink-100 rounded-lg flex items-center gap-2">
      <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search by ID, name or email..."
        class="bg-transparent outline-none flex-1 text-blue-900 placeholder-blue-600"
      />
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-pink-500 text-white">
            <th class="py-2 px-3 md:px-4 text-left">ID</th>
            <th class="py-2 px-3 md:px-4 text-left">Name</th>
            <th class="py-2 px-3 md:px-4 text-left">Email</th>
            <th class="py-2 px-3 md:px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="user in filteredUsers"
            :key="user.id"
            class="border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200"
          >
            <td class="py-2 px-3 md:px-4">{{ user.id }}</td>
            <td class="py-2 px-3 md:px-4">{{ user.name }}</td>
            <td class="py-2 px-3 md:px-4">{{ user.email }}</td>
            <td class="py-2 px-3 md:px-4 text-right">
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

      <!-- Pesan jika tidak ada data -->
      <div v-if="filteredUsers.length === 0" class="py-8 text-center text-gray-500">
        No user found.
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-6 space-x-2">
      <button class="px-3 py-1 bg-blue-900 text-white rounded">1</button>
      <button class="px-3 py-1 bg-blue-900/20 text-blue-900 rounded">2</button>
      <button class="px-3 py-1 bg-blue-900/20 text-blue-900 rounded">3</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Data asli
const users = [
  { id: 2, name: 'Keonho', email: 'ahnkeonho@zenith.com' },
  { id: 3, name: 'Jake', email: 'shimjake@zenith.com' },
  { id: 4, name: 'Yuha', email: 'Yuha@zenith.com' },
  { id: 5, name: 'Budi Santoso', email: 'budi.santoso@email.com' }
]

// Input pencarian
const searchQuery = ref('')

// Filter berdasarkan ID, name, atau email (case-insensitive)
const filteredUsers = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return users

  return users.filter(user =>
    user.id.toString().includes(query) ||
    user.name.toLowerCase().includes(query) ||
    user.email.toLowerCase().includes(query)
  )
})
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