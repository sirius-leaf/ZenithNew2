<!-- src/views/admin/KelolaSeller.vue -->
<template>
  <div
    class="bg-white rounded-xl shadow-md p-4 md:p-6 animate-fade-in max-w-5xl mx-auto mt-8"
  >
    <h1 class="text-xl md:text-2xl font-bold text-blue-900 mb-6">
      Kelola Seller
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
          placeholder="Search by ID, store name, seller name, or email..."
          class="w-full pl-10 pr-4 py-3 border border-pink-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-transparent text-blue-900 placeholder-blue-500 bg-pink-50/30 transition"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-8 text-center">
      <p class="text-gray-600">Memuat daftar seller...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
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
            <td class="py-3 px-4 font-mono text-blue-900">{{ seller.id }}</td>
            <td class="py-3 px-4 font-medium text-blue-900">
              {{ seller.store_name || "—" }}
            </td>
            <td class="py-3 px-4">{{ seller.name || "—" }}</td>
            <td class="py-3 px-4 text-blue-700 truncate max-w-xs">
              {{ seller.email }}
            </td>
            <td class="py-3 px-4 text-right space-x-2">
              <!-- 🔧 Edit (opsional — redirect ke form edit) -->
              <RouterLink
                :to="`/admin/edit-seller/${seller.id}`"
                class="p-2 rounded-full text-orange-600 hover:bg-orange-50 hover:text-orange-700 transition-colors duration-150"
                title="Edit"
                v-if="false"
                <!--
                nonaktif
                dulu,
                bisa
                diaktifkan
                nanti
                --
              >
                >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-5.414a2 2 0 012.828 0L18 10.828a2 2 0 010 2.828l-8 8a2 2 0 01-2.828 0l-4-4a2 2 0 012.828-2.828l4 4Z"
                  />
                </svg>
              </RouterLink>

              <!-- ❄️ Freeze / Unfreeze -->
              <button
                v-if="!seller.toko?.is_frozen"
                @click="openFreezeModal(seller)"
                class="p-2 rounded-full text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors duration-150"
                title="Bekukan Toko"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-4 h-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </button>
              <button
                v-else
                @click="unfreezeStore(seller)"
                class="p-2 rounded-full text-green-600 hover:bg-green-50 hover:text-green-700 transition-colors duration-150"
                title="Cairkan Toko"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-4 h-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </button>

              <!-- 🗑️ Delete (opsional — bisa diimplementasi nanti) -->
              <button
                class="p-2 rounded-full text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-150 disabled:opacity-50"
                title="Nonaktifkan"
                disabled
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A1 1 0 0117.133 21H6.867A1 1 0 016 19.133L4.867 7H19zm-1 8.133L18 19H6l-1.133-4.133A1 1 0 015 14v-2a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1z"
                  />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- No data -->
      <div
        v-if="!loading && filteredSellers.length === 0"
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
        <p class="mt-3">No seller found.</p>
      </div>
    </div>

    <!-- Freeze Modal -->
    <div
      v-if="showFreezeModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-sm"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4 text-gray-900">Bekukan Toko</h3>
        <p class="text-sm text-gray-600 mb-4">
          Apakah Anda yakin ingin membekukan toko
          <span class="font-bold">{{ selectedSeller?.store_name }}</span
          >? Seller tidak akan bisa menambah produk baru.
        </p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Alasan Pembekuan</label
          >
          <textarea
            v-model="freezeReason"
            class="w-full border rounded-md p-2 text-sm"
            rows="3"
            placeholder="Contoh: Melanggar syarat dan ketentuan..."
          ></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="closeFreezeModal"
            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-md"
          >
            Batal
          </button>
          <button
            @click="confirmFreeze"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            Bekukan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const sellers = ref([]);
const searchQuery = ref("");
const loading = ref(false);

// Freeze Logic
const showFreezeModal = ref(false);
const selectedSeller = ref(null);
const freezeReason = ref("");

onMounted(async () => {
  await fetchSellers();
});

const fetchSellers = async () => {
  loading.value = true;
  try {
    // ✅ Tambahkan params: role=penjual
    const res = await axios.get("http://127.0.0.1:8000/api/users", {
      params: {
        role: "penjual",
        search: searchQuery.value.trim(),
        page: 1,
        per_page: 10,
      },
      headers: { Authorization: `Bearer ${localStorage.getItem("authToken")}` },
    });
    // Sesuaikan dengan struktur respons pagination
    sellers.value = res.data.data || res.data;
  } catch (error) {
    console.error("Gagal memuat seller:", error);
    alert("Gagal memuat data seller.");
  } finally {
    loading.value = false;
  }
};

const filteredSellers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return sellers.value;

  return sellers.value.filter(
    (s) =>
      s.id.toString().includes(q) ||
      (s.store_name || "").toLowerCase().includes(q) ||
      (s.name || "").toLowerCase().includes(q) ||
      s.email.toLowerCase().includes(q)
  );
});

const openFreezeModal = (seller) => {
  selectedSeller.value = seller;
  freezeReason.value = "";
  showFreezeModal.value = true;
};

const closeFreezeModal = () => {
  showFreezeModal.value = false;
  selectedSeller.value = null;
};

const confirmFreeze = async () => {
  if (!selectedSeller.value || !selectedSeller.value.toko) {
    alert("Data toko tidak valid.");
    return;
  }

  try {
    await axios.post(
      `http://127.0.0.1:8000/api/manage/toko/${selectedSeller.value.toko.id}/freeze`,
      {
        reason: freezeReason.value,
      },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      }
    );
    alert("Toko berhasil dibekukan.");
    closeFreezeModal();
    fetchSellers(); // Refresh data
  } catch (e) {
    console.error(e);
    alert("Gagal membekukan toko.");
  }
};

const unfreezeStore = async (seller) => {
  if (!confirm(`Cairkan toko ${seller.store_name}?`)) return;

  try {
    await axios.post(
      `http://127.0.0.1:8000/api/manage/toko/${seller.toko.id}/unfreeze`,
      {},
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      }
    );
    alert("Toko berhasil dicairkan.");
    fetchSellers(); // Refresh data
  } catch (e) {
    console.error(e);
    alert("Gagal mencairkan toko.");
  }
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
