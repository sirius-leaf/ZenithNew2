<!-- src/components/admin/KelolaUser.vue -->
<template>
  <div
    class="bg-white rounded-xl shadow-md p-4 md:p-6 animate-fade-in max-w-5xl mx-auto mt-8"
  >
    <h1 class="text-xl md:text-2xl font-bold text-blue-900 mb-6">
      Kelola User
    </h1>

    <!-- Search Bar -->
    <div class="mb-6">
      <div class="relative">
        <div
          class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
        >
          <svg
            class="w-5 h-5 text-pink-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari berdasarkan ID, nama, atau email..."
          class="w-full pl-10 pr-4 py-3 border border-pink-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-transparent text-blue-900 placeholder-blue-500 bg-pink-50/30 transition"
          @input="debounceSearch"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-10 text-center">
      <div
        class="inline-block w-8 h-8 border-4 border-pink-300 border-t-pink-600 rounded-full animate-spin"
      ></div>
      <p class="mt-3 text-gray-600">Memuat data pengguna...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full text-sm">
        <thead class="bg-pink-500 text-white">
          <tr>
            <th class="py-3 px-3 text-left font-medium">ID</th>
            <th class="py-3 px-3 text-left font-medium">Nama</th>
            <th class="py-3 px-3 text-left font-medium">Email</th>
            <th class="py-3 px-3 text-left font-medium">Role</th>
            <th class="py-3 px-3 text-right font-medium">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="user in users"
            :key="user.id"
            class="hover:bg-pink-50/50 transition-colors duration-150"
          >
            <td class="py-3 px-3 font-mono text-blue-900">{{ user.id }}</td>
            <td class="py-3 px-3 font-medium text-blue-900">{{ user.name }}</td>
            <td class="py-3 px-3 text-blue-700 truncate max-w-xs">
              {{ user.email }}
            </td>
            <td class="py-3 px-3">
              <span
                class="px-2 py-1 text-xs rounded-full font-medium"
                :class="{
                  'bg-blue-100 text-blue-800': user.role === 'admin',
                  'bg-pink-100 text-pink-800': user.role === 'penjual',
                  'bg-gray-100 text-gray-800': user.role === 'user',
                }"
              >
                {{ user.role }}
              </span>
            </td>
            <td class="py-3 px-3 text-right">
              <span
                v-if="user.is_banned"
                class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 mr-2"
              >
                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                Dibatasi
              </span>
              <button
                v-if="!user.is_banned"
                @click="confirmBan(user.id)"
                class="px-3 py-1 text-xs bg-yellow-500 text-white rounded hover:bg-yellow-600 transition cursor-pointer"
              >
                Batasi
              </button>
              <button
                v-else
                @click="confirmUnban(user.id)"
                class="px-3 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600 transition cursor-pointer"
              >
                Pulihkan
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty -->
      <div
        v-if="users.length === 0"
        class="py-10 px-4 text-center text-gray-500 bg-gray-50 rounded-b-lg"
      >
        <svg
          class="mx-auto h-12 w-12 text-gray-300"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.485 0-4.5-1.276-5.5-2.828"
          />
        </svg>
        <p class="mt-3">Tidak ada pengguna ditemukan.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="!loading && meta.last_page > 1"
      class="flex justify-center mt-6 space-x-1"
    >
      <button
        @click="goToPage(1)"
        :disabled="meta.current_page === 1"
        class="px-2 py-1 text-xs font-medium rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
        :class="
          meta.current_page === 1
            ? 'bg-gray-200 text-gray-500'
            : 'bg-pink-100 text-pink-700 hover:bg-pink-200'
        "
      >
        &laquo;
      </button>

      <template v-for="page in visiblePages" :key="page">
        <button
          v-if="typeof page === 'number'"
          @click="goToPage(page)"
          class="px-2 py-1 text-xs font-medium rounded-md transition-colors"
          :class="
            meta.current_page === page
              ? 'bg-pink-500 text-white'
              : 'bg-pink-100 text-pink-700 hover:bg-pink-200'
          "
        >
          {{ page }}
        </button>
        <span v-else class="px-2 py-1 text-xs text-gray-500">...</span>
      </template>

      <button
        @click="goToPage(meta.last_page)"
        :disabled="meta.current_page === meta.last_page"
        class="px-2 py-1 text-xs font-medium rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
        :class="
          meta.current_page === meta.last_page
            ? 'bg-gray-200 text-gray-500'
            : 'bg-pink-100 text-pink-700 hover:bg-pink-200'
        "
      >
        &raquo;
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";

// State
const users = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const currentPage = ref(1);
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0,
});

// Fetch data
const fetchUsers = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get("/users", {
      params: {
        page: currentPage.value,
        search: searchQuery.value.trim(),
        per_page: 5,
      },
    });
    users.value = data.data;
    meta.value = data.meta;
  } catch (error) {
    console.error("[API Error]", error.response?.data || error.message);
    alert("❌ Gagal memuat data. Periksa konsol untuk detail.");
  } finally {
    loading.value = false;
  }
};

// Debounce search
let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchUsers();
  }, 300);
};

// Pagination helpers
const visiblePages = computed(() => {
  const total = meta.value.last_page;
  const current = meta.value.current_page;
  const delta = 1;
  let range = [];

  for (
    let i = Math.max(2, current - delta);
    i <= Math.min(total - 1, current + delta);
    i++
  ) {
    range.push(i);
  }

  if (current > 2) range.unshift("...");
  range.unshift(1);

  if (current < total - 1) range.push("...");
  if (total > 1) range.push(total);

  return range;
});

// Navigation
const goToPage = (page) => {
  if (typeof page === "number" && page >= 1 && page <= meta.value.last_page) {
    currentPage.value = page;
    fetchUsers();
  }
};

// 🔒 Ban / Unban
const confirmBan = (id) => {
  if (
    confirm(
      "⚠️ Yakin ingin membatasi akun ini?\nUser yang terbatasi tidak dapat login."
    )
  ) {
    banUser(id);
  }
};

const banUser = async (id) => {
  try {
    await axios.post(`/users/${id}/ban`);
    const userIndex = users.value.findIndex((u) => u.id === id);
    if (userIndex !== -1) {
      users.value[userIndex].is_banned = true;
    }
    alert("✅ Akun berhasil dibatasi.");
  } catch (error) {
    console.error("[Ban Error]", error.response?.data || error.message);
    alert("❌ Gagal membatasi akun.");
  }
};

const confirmUnban = (id) => {
  if (confirm("⚠️ Yakin ingin mengembalikan akses akun ini?")) {
    unbanUser(id);
  }
};

const unbanUser = async (id) => {
  try {
    await axios.post(`/users/${id}/unban`);
    const userIndex = users.value.findIndex((u) => u.id === id);
    if (userIndex !== -1) {
      users.value[userIndex].is_banned = false;
    }
    alert("✅ Akun berhasil dikembalikan.");
  } catch (error) {
    console.error("[Unban Error]", error.response?.data || error.message);
    alert("❌ Gagal mengembalikan akses akun.");
  }
};

// Watchers
watch(currentPage, fetchUsers);

// Init
onMounted(() => {
  fetchUsers();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
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
