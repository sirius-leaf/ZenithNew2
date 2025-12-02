<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { RouterLink, useRouter } from "vue-router";
import { useToast } from "vue-toastification";

const toast = useToast();
const router = useRouter();

// State
const users = ref([]);
const loading = ref(true);
const error = ref(null);
const successMessage = ref(null);

// Ambil Token dari localStorage (asumsi Anda login pakai token)
const token = localStorage.getItem("authToken");

// Konfig Axios Header
const authConfig = {
  headers: {
    Authorization: `Bearer ${token}`,
  },
};

// 1. Fungsi Mengambil Data User
const fetchUsers = async () => {
  try {
    loading.value = true;
    // Sesuaikan URL dengan route API Anda
    const response = await axios.get(
      "http://127.0.0.1:8000/api/manage/users",
      authConfig
    );
    users.value = response.data.data;
  } catch (err) {
    error.value = "Gagal memuat data user.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// 2. Fungsi Update Role
const updateRole = async (user) => {
  // Simpan role lama untuk jaga-jaga jika update gagal
  const oldRole = user.role;

  try {
    // Kirim request PUT
    await axios.put(
      `http://127.0.0.1:8000/api/manage/users/${user.id}`,
      {
        role: user.role, // Nilai role baru dari dropdown
      },
      authConfig
    );

    // Tampilkan notifikasi sukses sementara
    toast.success("Role berhasil diperbarui!");
  } catch (err) {
    console.error(err);
    toast.error("Gagal mengubah role.");
    user.role = oldRole; // Kembalikan ke role lama jika gagal
  }
};

// Confirmation Modal State
const showConfirmModal = ref(false);
const confirmMessage = ref("");
const confirmCallback = ref(null);

const openConfirmModal = (message, callback) => {
  confirmMessage.value = message;
  confirmCallback.value = callback;
  showConfirmModal.value = true;
};

const handleConfirm = () => {
  if (confirmCallback.value) confirmCallback.value();
  showConfirmModal.value = false;
};

// 3. Fungsi Hapus User
const deleteUser = (id) => {
  openConfirmModal("Yakin ingin menghapus akun ini?", async () => {
    try {
      await axios.delete(
        `http://127.0.0.1:8000/api/manage/users/${id}`,
        authConfig
      );

      // Hapus user dari list lokal agar tidak perlu refresh halaman
      users.value = users.value.filter((u) => u.id !== id);
      toast.success("User berhasil dihapus!");
    } catch (err) {
      console.error(err);
      toast.error("Gagal menghapus user.");
    }
  });
};

// Helper untuk notifikasi sukses (Deprecated, replaced by toast)
const showSuccess = (msg) => {
  toast.success(msg);
};

// Appeal Review State
const showAppealModal = ref(false);
const selectedUser = ref(null);

const viewAppeal = (user) => {
  selectedUser.value = user;
  showAppealModal.value = true;
};

const unfreezeStore = (user) => {
  openConfirmModal("Aktifkan kembali toko ini?", async () => {
    try {
      await axios.post(
        `http://127.0.0.1:8000/api/manage/toko/${user.toko.id}/unfreeze`,
        {},
        authConfig
      );
      toast.success("Toko berhasil diaktifkan kembali!");
      showAppealModal.value = false;
      
      // Update local state
      if (user.toko) {
        user.toko.is_frozen = false;
        user.toko.frozen_reason = null;
        user.toko.appeal_reason = null;
      }
    } catch (err) {
      console.error(err);
      toast.error("Gagal mengaktifkan toko.");
    }
  });
};

// Store Detail View State
const showStoreDetailModal = ref(false);
const selectedStoreDetail = ref(null);
const storeProducts = ref([]);
const storeRating = ref(null);
const loadingDetail = ref(false);

const viewStoreDetail = async (user) => {
  if (!user.toko) return;
  
  showStoreDetailModal.value = true;
  loadingDetail.value = true;
  selectedStoreDetail.value = user.toko; // Basic info first
  
  try {
    // Fetch full details including products
    const res = await axios.get(`http://127.0.0.1:8000/api/toko/${user.toko.id}`);
    if (res.data.data && res.data.data.length > 0) {
      selectedStoreDetail.value = res.data.data[0];
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

// Jalankan saat komponen dimuat
onMounted(() => {
  if (localStorage.getItem("userRole") !== "admin") {
    toast.error("Akses ditolak, Anda bukan admin");
    router.push("/dashboard");
  }

  fetchUsers();
});
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Daftar User</h1>
    </div>

    <!-- Notifikasi Error (Deprecated, replaced by toast) -->
    <div
      v-if="error"
      class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border border-red-200"
    >
      {{ error }}
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-10 text-gray-500">
      Memuat data...
    </div>

    <!-- Tabel User -->
    <div v-else class="overflow-x-auto bg-white shadow-md rounded-lg">
      <table class="min-w-full leading-normal">
        <thead>
          <tr>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              ID
            </th>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Nama
            </th>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Email
            </th>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              No Telepon
            </th>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Alamat
            </th>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Status Toko
            </th>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Role
            </th>
            <th
              class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
            >
              Aksi
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              {{ user.id }}
            </td>
            <td
              class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-medium text-gray-900"
            >
              {{ user.name }}
            </td>
            <td
              class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600"
            >
              {{ user.email }}
            </td>
            <td
              class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600"
            >
              {{ user.no_telpon ?? "-" }}
            </td>
            <td
              class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600"
            >
              {{ user.alamat ?? "-" }}
            </td>

            <!-- Status Toko (Frozen/Appeal) -->
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              <div v-if="user.toko?.is_frozen" class="flex flex-col gap-1">
                <span
                  class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full w-fit"
                >
                  Frozen
                </span>
                <button
                  v-if="user.toko?.appeal_reason"
                  @click="viewAppeal(user)"
                  class="text-xs text-blue-600 hover:underline font-medium text-left"
                >
                  Lihat Banding
                </button>
              </div>
              <span v-else class="text-green-600 text-xs">Aktif</span>
            </td>

            <!-- Kolom Ubah Role -->
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              <select
                v-model="user.role"
                @change="updateRole(user)"
                class="block w-full bg-white border border-gray-300 text-gray-700 py-1 px-2 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
              >
                <option value="user">User</option>
                <option value="penjual">Penjual</option>
                <option value="admin">Admin</option>
              </select>
            </td>

            <!-- Kolom Aksi -->
            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
              <div class="flex items-center gap-3">
                <!-- View Button (Text) -->
                <button
                  v-if="user.role === 'penjual' && user.toko"
                  @click="viewStoreDetail(user)"
                  class="text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline transition-colors"
                >
                  Lihat detail
                </button>

                <!-- Delete Button (Trash Icon) -->
                <button
                  @click="deleteUser(user.id)"
                  class="text-red-500 hover:text-red-700 transition-colors"
                  title="Hapus User"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
            </td>
          </tr>

          <!-- Pesan jika data kosong -->
          <tr v-if="users.length === 0">
            <td
              colspan="7"
              class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500"
            >
              Belum ada user terdaftar.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Appeal Review Modal -->
    <div
      v-if="showAppealModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4"
    >
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tinjau Banding</h3>
        <div class="mb-4">
          <p class="text-sm text-gray-500 mb-1">Nama Toko:</p>
          <p class="font-medium">{{ selectedUser?.toko?.toko_name }}</p>
        </div>
        <div class="mb-4">
          <p class="text-sm text-gray-500 mb-1">Alasan Pembekuan:</p>
          <p class="text-red-600 text-sm bg-red-50 p-2 rounded">
            {{ selectedUser?.toko?.frozen_reason }}
          </p>
        </div>
        <div class="mb-6">
          <p class="text-sm text-gray-500 mb-1">Alasan Banding:</p>
          <p class="text-gray-800 text-sm bg-gray-50 p-3 rounded border">
            {{ selectedUser?.toko?.appeal_reason }}
          </p>
        </div>
        <div class="flex justify-end gap-3">
          <button
            @click="showAppealModal = false"
            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
          >
            Tutup
          </button>
          <button
            @click="unfreezeStore(selectedUser)"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
          >
            Aktifkan Toko
          </button>
        </div>
      </div>
    </div>

    <!-- Store Detail Modal -->
    <div
      v-if="showStoreDetailModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4"
    >
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-6">
          <h3 class="text-xl font-bold text-gray-900">Detail Toko</h3>
          <button @click="showStoreDetailModal = false" class="text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div v-if="loadingDetail" class="text-center py-10 text-gray-500">
          Memuat detail toko...
        </div>

        <div v-else class="space-y-6">
          <!-- Store Info -->
          <div class="flex items-start gap-4">
            <div class="w-20 h-20 bg-gray-100 rounded-full overflow-hidden flex-shrink-0 border border-gray-200">
               <!-- Placeholder for store image if not available in data -->
               <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-50">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
               </div>
            </div>
            <div>
              <h4 class="text-lg font-bold text-gray-900">{{ selectedStoreDetail?.toko_name }}</h4>
              <p class="text-sm text-gray-500">{{ selectedStoreDetail?.deskripsi || 'Tidak ada deskripsi' }}</p>
              <div class="mt-2 flex gap-2">
                <span v-if="selectedStoreDetail?.is_frozen" class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Frozen</span>
                <span v-else class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Aktif</span>
                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                  Rating: {{ storeRating?.['rata-rata'] || 0 }} / 5 ({{ storeRating?.jumlah || 0 }} ulasan)
                </span>
              </div>
            </div>
          </div>

          <!-- Products List -->
          <div>
            <h5 class="font-bold text-gray-800 mb-3 border-b pb-2">Produk Toko ({{ storeProducts.length }})</h5>
            <div v-if="storeProducts.length === 0" class="text-sm text-gray-500 italic">
              Tidak ada produk.
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-for="product in storeProducts" :key="product.id_produk" class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50">
                <div class="w-12 h-12 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                   <img 
                    v-if="product.variant && product.variant.length > 0 && product.variant[0].gambar_varian" 
                    :src="`http://127.0.0.1:8000/storage/${product.variant[0].gambar_varian}`" 
                    class="w-full h-full object-cover"
                   />
                   <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v12a2 2 0 002 2z" />
                     </svg>
                   </div>
                </div>
                <div class="overflow-hidden">
                  <p class="font-medium text-sm text-gray-900 truncate">{{ product.nama_produk }}</p>
                  <p class="text-xs text-gray-500">Stok: {{ product.variant?.[0]?.stok || 0 }} | Terjual: {{ product.terjual || 0 }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-6 flex justify-end">
          <button
            @click="showStoreDetailModal = false"
            class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div
      v-if="showConfirmModal"
      class="fixed inset-0 z-[60] flex items-center justify-center backdrop-blur-sm bg-black/20 px-4"
    >
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 animate-fade-in">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi</h3>
        <p class="text-gray-600 mb-6">
          {{ confirmMessage }}
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showConfirmModal = false"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
          >
            Batal
          </button>
          <button
            @click="handleConfirm"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
          >
            Ya, Lanjutkan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
