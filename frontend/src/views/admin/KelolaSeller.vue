<!-- src/views/admin/KelolaSeller.vue -->
<template>
  <div class="animate-fade-in mt-4">
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
            <th class="py-3 px-4 text-right font-medium">Opsi</th>
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
            <td class="py-3 px-4 text-right">
              <button
                @click="openDetailModal(seller)"
                class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors"
              >
                Details
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

    <Teleport to="body">
      <div
        v-if="showFreezeModal"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-white/10 backdrop-blur-sm"
      >
        <div
          class="bg-white rounded-lg p-6 w-full max-w-md shadow-2xl border border-gray-100"
        >
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
    </Teleport>

    <Teleport to="body">
      <div
        v-if="showDetailModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
      >
        <div
          class="bg-white rounded-xl shadow-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto"
        >
          <div class="flex justify-between items-start mb-6">
            <h3 class="text-xl font-bold text-gray-900">Detail Toko</h3>
            <button
              @click="showDetailModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div v-if="loadingDetail" class="text-center py-10 text-gray-500">
            Memuat detail toko...
          </div>

          <div v-else class="space-y-6">
            <!-- Store Info -->
            <div class="flex items-start gap-4">
              <div
                class="w-20 h-20 bg-gray-100 rounded-full overflow-hidden flex-shrink-0 border border-gray-200"
              >
                <div
                  class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50"
                >
                  <img
                    v-if="selectedDetail?.user?.store_photo"
                    :src="`http://127.0.0.1:8000/storage/${selectedDetail.user.store_photo}`"
                    alt="Store Profile"
                    class="w-full h-full object-cover"
                  />
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
              </div>
              <div>
                <h4 class="text-lg font-bold text-gray-900">
                  {{ selectedDetail?.toko_name }}
                </h4>
                <p class="text-sm text-gray-500">
                  {{ selectedDetail?.deskripsi || "Tidak ada deskripsi" }}
                </p>
                <div class="mt-2 flex gap-2">
                  <span
                    v-if="selectedDetail?.is_frozen"
                    class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full"
                    >Frozen</span
                  >
                  <span
                    v-else
                    class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"
                    >Aktif</span
                  >
                  <span
                    class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full"
                  >
                    Rating: {{ storeRating?.["rata-rata"] || 0 }} / 5 ({{
                      storeRating?.jumlah || 0
                    }}
                    ulasan)
                  </span>
                </div>
              </div>
            </div>

            <!-- Store Details (Owner Info) -->
            <div
              class="bg-gray-50 rounded-lg border border-gray-100 overflow-hidden"
            >
              <button
                @click="toggleOwnerInfo"
                class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition-colors"
              >
                <h5
                  class="font-bold text-gray-800 text-sm uppercase tracking-wide"
                >
                  Informasi Pemilik
                </h5>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-gray-500 transform transition-transform duration-200"
                  :class="{ 'rotate-180': showOwnerInfo }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </button>

              <div
                v-show="showOwnerInfo"
                class="p-4 border-t border-gray-100 bg-white"
              >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                  <div>
                    <p class="text-gray-500 mb-1">Nama Pemilik</p>
                    <p class="font-medium text-gray-900">
                      {{ currentDetailSeller?.name || "—" }}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500 mb-1">Email</p>
                    <p class="font-medium text-gray-900">
                      {{ currentDetailSeller?.email || "—" }}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500 mb-1">Tanggal Bergabung</p>
                    <p class="font-medium text-gray-900">
                      {{
                        currentDetailSeller?.created_at
                          ? new Date(
                              currentDetailSeller.created_at
                            ).toLocaleDateString("id-ID", {
                              day: "numeric",
                              month: "long",
                              year: "numeric",
                            })
                          : "—"
                      }}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500 mb-1">Status Toko</p>
                    <span
                      v-if="selectedDetail?.is_frozen"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                    >
                      Frozen
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                    >
                      Aktif
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Products List -->
            <!-- Products List -->
            <div>
              <h5
                class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-pink-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                  />
                </svg>
                Semua Produk
              </h5>

              <div
                v-if="storeProducts.length === 0"
                class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300"
              >
                <p class="text-gray-500 italic">Tidak ada produk.</p>
              </div>

              <div v-else>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                  <div
                    v-for="product in paginatedProducts"
                    :key="product.id_produk"
                    class="bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300 group"
                  >
                    <!-- Product Image -->
                    <div
                      class="aspect-square bg-gray-100 relative overflow-hidden"
                    >
                      <img
                        :src="getProductImage(product)"
                        :alt="product.nama_produk"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>

                    <!-- Product Info -->
                    <div class="p-3">
                      <h3
                        class="font-medium text-gray-900 line-clamp-2 mb-1 text-sm group-hover:text-pink-600 transition-colors"
                      >
                        {{ product.nama_produk }}
                        <span
                          class="text-xs text-gray-500 ml-1 font-normal whitespace-nowrap"
                        >
                          ({{ product.variant ? product.variant.length : 0 }}
                          Varian)
                        </span>
                      </h3>
                      <p class="text-xs text-gray-500 mb-2">
                        {{ product.merek || "No Brand" }}
                      </p>
                      <div class="font-bold text-pink-600 text-sm">
                        {{ getProductPrice(product) }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Pagination Controls -->
                <div
                  v-if="totalPages > 1"
                  class="flex justify-center items-center gap-4 mt-4"
                >
                  <button
                    @click="prevPage"
                    :disabled="currentPage === 1"
                    class="p-2 rounded-full hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5 text-gray-600"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                      />
                    </svg>
                  </button>
                  <span class="text-sm font-medium text-gray-600">
                    Halaman {{ currentPage }} dari {{ totalPages }}
                  </span>
                  <button
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    class="p-2 rounded-full hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5 text-gray-600"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-between items-center">
            <div class="flex gap-2">
              <template v-if="selectedDetail?.is_frozen">
                <button
                  @click="unfreezeStore(currentDetailSeller)"
                  class="px-4 py-2 bg-green-100 text-green-700 font-medium rounded-lg hover:bg-green-200 transition"
                >
                  Cairkan Toko
                </button>
                <button
                  @click="viewAppeal"
                  class="px-4 py-2 bg-blue-100 text-blue-700 font-medium rounded-lg hover:bg-blue-200 transition"
                >
                  Lihat Banding
                </button>
              </template>
              <button
                v-else
                @click="openFreezeModal(currentDetailSeller)"
                class="px-4 py-2 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition"
              >
                Bekukan Toko
              </button>
            </div>
            <button
              @click="showDetailModal = false"
              class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
      <div
        v-if="showAppealModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
      >
        <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full">
          <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-bold text-gray-900">Detail Banding</h3>
            <button
              @click="showAppealModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div class="mb-6">
            <div
              v-if="selectedDetail?.appeal_reason"
              class="bg-gray-50 p-4 rounded-lg border border-gray-100"
            >
              <p class="text-sm text-gray-700 whitespace-pre-wrap">
                {{ selectedDetail.appeal_reason }}
              </p>
            </div>
            <div v-else class="text-center py-8 text-gray-500 italic">
              Seller belum mengirim pengajuan banding
            </div>
          </div>

          <div class="flex justify-end">
            <button
              @click="showAppealModal = false"
              class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
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

    // Update detail modal state if open and matching
    if (
      selectedDetail.value &&
      selectedDetail.value.id === selectedSeller.value.toko.id
    ) {
      selectedDetail.value.is_frozen = 1;
    }

    fetchSellers(); // Refresh data
    closeFreezeModal();
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

    // Update detail modal state if open and matching
    if (selectedDetail.value && selectedDetail.value.id === seller.toko.id) {
      selectedDetail.value.is_frozen = 0;
    }
  } catch (e) {
    alert("Gagal mencairkan toko.");
  }
};

const showAppealModal = ref(false);

const viewAppeal = () => {
  showAppealModal.value = true;
};

// Detail Modal Logic
const showDetailModal = ref(false);
const selectedDetail = ref(null);
const storeProducts = ref([]);
const storeRating = ref(null);
const loadingDetail = ref(false);
const currentDetailSeller = ref(null);
const showOwnerInfo = ref(false);

const toggleOwnerInfo = () => {
  showOwnerInfo.value = !showOwnerInfo.value;
};

const openDetailModal = async (seller) => {
  if (!seller.toko) {
    alert("User ini belum memiliki toko.");
    return;
  }

  showDetailModal.value = true;
  loadingDetail.value = true;
  selectedDetail.value = seller.toko;
  currentDetailSeller.value = seller; // Store seller for actions
  currentPage.value = 1; // Reset pagination
  showOwnerInfo.value = false; // Reset dropdown state

  try {
    const res = await axios.get(
      `http://127.0.0.1:8000/api/toko/${seller.toko.id}`
    );
    if (res.data.data && res.data.data.length > 0) {
      selectedDetail.value = res.data.data[0];
    }
    storeProducts.value = res.data.products || [];
    storeRating.value = res.data.ratingToko;
  } catch (err) {
    console.error(err);
    alert("Gagal memuat detail toko.");
  } finally {
    loadingDetail.value = false;
  }
};

const currentPage = ref(1);
const itemsPerPage = 3;

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return storeProducts.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(storeProducts.value.length / itemsPerPage);
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const getProductImage = (product) => {
  if (
    product.variant &&
    product.variant.length > 0 &&
    product.variant[0].gambar_varian
  ) {
    return `http://127.0.0.1:8000/storage/${product.variant[0].gambar_varian}`;
  }
  return "https://via.placeholder.com/300?text=No+Image";
};

const getProductPrice = (product) => {
  if (product.variant && product.variant.length > 0) {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(Number(product.variant[0].harga));
  }
  return "Rp 0";
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
