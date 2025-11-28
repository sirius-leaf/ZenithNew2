<template>
  <div class="w-full bg-white p-4 sm:p-6 font-ubuntu min-h-screen">
    <!-- Header -->
    <div class="mb-8 flex items-center gap-2">
      <button @click="$router.back()" class="text-[#203f9a] hover:text-[#94c2da] text-sm font-medium">
        ← Kembali ke Dashboard
      </button>
    </div>

    <!-- Loading -->
    <div v-if="!user" class="max-w-2xl mx-auto text-center py-10">
      <p class="text-gray-600">Memuat data akun...</p>
    </div>

    <!-- Profile Card (Collapsible) -->
    <div v-else class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300">
      <!-- Header (Always Visible) -->
      <div 
        @click="isProfileExpanded = !isProfileExpanded"
        class="p-6 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition-colors"
      >
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full overflow-hidden border border-gray-200 bg-gray-100">
             <img 
              :src="user.profile_photo ? `http://127.0.0.1:8000/storage/${user.profile_photo}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=random'" 
              alt="Avatar" 
              class="w-full h-full object-cover"
            />
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Biodata Diri</h2>
            <p class="text-sm text-gray-500">{{ user.name }} • {{ user.email }}</p>
          </div>
        </div>
        
        <!-- Chevron Icon -->
        <div class="transform transition-transform duration-300" :class="{ 'rotate-180': isProfileExpanded }">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

      <!-- Collapsible Content -->
      <div 
        v-show="isProfileExpanded"
        class="border-t border-gray-100 bg-gray-50/50"
      >
        <div class="p-6">
          <div class="flex flex-col md:flex-row gap-8">
            <!-- Left: Profile Photo (Expanded) -->
            <div class="flex flex-col items-center gap-4 w-full md:w-1/3 border-b md:border-b-0 md:border-r border-gray-200 pb-6 md:pb-0 md:pr-6">
              <div class="relative w-48 h-48 bg-gray-100 rounded-full overflow-hidden shadow-sm border border-gray-200">
                <img 
                  :src="user.profile_photo ? `http://127.0.0.1:8000/storage/${user.profile_photo}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&background=random'" 
                  alt="Profile Photo" 
                  class="w-full h-full object-cover"
                />
              </div>
              
              <label class="w-full max-w-[200px]">
                <div class="w-full py-2.5 px-4 bg-white border border-gray-300 rounded-lg text-gray-700 font-semibold text-center cursor-pointer hover:bg-gray-50 transition shadow-sm">
                  Pilih Foto
                </div>
                <input type="file" class="hidden" accept="image/*" @change="handlePhotoUpload">
              </label>

              <p class="text-xs text-gray-500 text-center max-w-[200px] leading-relaxed">
                Besar file: maksimum 2.000.000 bytes (2 Megabytes). Ekstensi file yang diperbolehkan: .JPG .JPEG .PNG
              </p>
            </div>

            <!-- Right: Info -->
            <div class="flex-1 space-y-8">
              <!-- Section: Ubah Biodata Diri -->
              <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4">Detail Biodata</h3>
                <div class="space-y-4">
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    <span class="text-gray-500 text-sm">Nama</span>
                    <div class="sm:col-span-2 flex items-center gap-2">
                      <span class="text-gray-900 font-medium">{{ user.name }}</span>
                      <button @click="$router.push('/profile/edit')" class="text-[#e84797] text-sm font-medium hover:underline">Ubah</button>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    <span class="text-gray-500 text-sm">Tanggal Lahir</span>
                    <div class="sm:col-span-2 flex items-center gap-2">
                      <span class="text-[#e84797] text-sm font-medium cursor-pointer hover:underline">Tambah Tanggal Lahir</span>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    <span class="text-gray-500 text-sm">Jenis Kelamin</span>
                    <div class="sm:col-span-2 flex items-center gap-2">
                      <span class="text-[#e84797] text-sm font-medium cursor-pointer hover:underline">Tambah Jenis Kelamin</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section: Ubah Kontak -->
              <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4">Detail Kontak</h3>
                <div class="space-y-4">
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    <span class="text-gray-500 text-sm">Email</span>
                    <div class="sm:col-span-2 flex items-center gap-2 flex-wrap">
                      <span class="text-gray-900 font-medium">{{ user.email }}</span>
                      <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Terverifikasi</span>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    <span class="text-gray-500 text-sm">Nomor HP</span>
                    <div class="sm:col-span-2 flex items-center gap-2 flex-wrap">
                      <span v-if="user.no_telpon" class="text-gray-900 font-medium">{{ user.no_telpon }}</span>
                      <span v-else class="text-gray-400 italic">Belum diatur</span>
                      <span v-if="user.no_telpon" class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Terverifikasi</span>
                      <button @click="$router.push('/profile/edit')" class="text-[#e84797] text-sm font-medium hover:underline">Ubah</button>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    <span class="text-gray-500 text-sm">Alamat</span>
                    <div class="sm:col-span-2 flex items-center gap-2 flex-wrap">
                      <span v-if="user.alamat" class="text-gray-900 font-medium">{{ user.alamat }}</span>
                      <span v-else class="text-gray-400 italic">Belum diatur</span>
                      <button @click="$router.push('/profile/edit')" class="text-[#e84797] text-sm font-medium hover:underline">Ubah</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tombol Edit Profil -->
          <button
            @click="$router.push('/profile/edit')"
            class="w-full py-2.5 mt-8 bg-[#203f9a] text-white font-medium rounded-lg disabled:opacity-50 hover:bg-[#94c2da] transition shadow-sm"
          >
            Edit Profil Lengkap
          </button>
        </div>
      </div>
    </div>

 <!-- Status Toko & Form Daftar Seller -->
<div v-if="user" class="max-w-4xl mx-auto mt-8 px-6 sm:px-0">
  
  <!-- Jika User Adalah Seller: Tampilkan Informasi Toko (Collapsible) -->
  <div v-if="user.role === 'penjual'" class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300">
      <!-- Header (Always Visible) -->
      <div 
        @click="isStoreExpanded = !isStoreExpanded"
        class="p-6 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition-colors"
      >
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-full flex justify-center items-center bg-pink-100 text-pink-600">
             <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
             </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Informasi Toko</h2>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">{{ user.store_name || 'Nama Toko' }}</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Aktif</span>
            </div>
          </div>
        </div>
        
        <!-- Chevron Icon -->
        <div class="transform transition-transform duration-300" :class="{ 'rotate-180': isStoreExpanded }">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

      <!-- Collapsible Content -->
      <div 
        v-show="isStoreExpanded"
        class="border-t border-gray-100 bg-gray-50/50"
      >
        <div class="p-6 space-y-6">
            <!-- Detail Toko -->
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-start">
                    <span class="text-gray-500 text-sm">Nama Toko</span>
                    <div class="sm:col-span-2 text-gray-900 font-medium">{{ user.store_name }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-start">
                    <span class="text-gray-500 text-sm">Alamat Toko</span>
                    <div class="sm:col-span-2 text-gray-900 font-medium">{{ user.address }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-start">
                    <span class="text-gray-500 text-sm">Deskripsi</span>
                    <div class="sm:col-span-2 text-gray-900 font-medium text-sm leading-relaxed">{{ user.description }}</div>
                </div>
            </div>

            <!-- Tombol Kelola Toko -->
            <button
                @click="$router.push('/manage/my-shop')"
                class="w-full py-2.5 bg-[#e84797] text-white font-medium rounded-lg hover:bg-[#d03a84] transition shadow-sm flex items-center justify-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Kelola Toko Saya
            </button>
        </div>
      </div>
  </div>

  <!-- Jika User BUKAN Seller: Tampilkan Card Ajakan Buka Toko -->
  <div v-else-if="user.role !== 'penjual_pending'" class="bg-white rounded-xl p-6 shadow-md border border-gray-200">
    <div class="flex flex-col sm:flex-row items-start gap-4">
      <div class="w-12 h-12 bg-[#203f9a] rounded-full flex justify-center items-center shadow-sm flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l1-4h16l1 4M4 9h16v10H4V9zm4 5h8" />
        </svg>
      </div>
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-[#203f9a] mb-2">Buka Toko Anda Sendiri</h3>
        <p class="text-sm text-gray-600 mb-4">
          Jadilah penjual dan raih penghasilan dengan membuka toko di platform kami.
          Proses pendaftaran mudah dan gratis!
        </p>
        <button 
          @click="openStoreForm"
          class="w-full py-2 px-4 bg-[#e84797] hover:bg-[#d03a84] text-white font-medium rounded-lg flex items-center justify-center gap-2 transition-all duration-200 hover:shadow-md active:scale-[0.98]"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8m12 0a8 8 0 11-16 0 8 8 0 0116 0z" />
          </svg>
          Daftar Toko Sekarang
        </button>
      </div>
    </div>
  </div>

  <!-- Jika Pending -->
  <div v-else class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
      <h3 class="text-lg font-semibold text-yellow-800 mb-2">Pendaftaran Sedang Diproses</h3>
      <p class="text-sm text-yellow-700">
          Permintaan Anda untuk menjadi penjual sedang ditinjau oleh admin. Harap tunggu konfirmasi selanjutnya.
      </p>
  </div>

</div>

      <!-- Modal Daftar Seller -->
      <Transition name="modal-fade">
        <div v-if="showStoreForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click="closeModal">
          <Transition name="modal-slide">
            <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl overflow-hidden" @click.stop>
              <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-[#203f9a]">
                  {{ currentStep === 1 ? 'Buat Toko Anda' : 'Lengkapi Persyaratan' }}
                </h3>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <div class="p-6 max-h-[70vh] overflow-y-auto">
                <Transition name="step-fade" mode="out-in">
                  <!-- Step 1: Data Toko -->
                  <div v-if="currentStep === 1" key="step-1" class="space-y-4">
                    <div>
                      <label class="block text-gray-700 text-sm font-medium mb-1">Nama Toko</label>
                      <input v-model="storeName" type="text" placeholder="Masukkan nama toko" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e84797]" />
                    </div>
                    <div>
                      <label class="block text-gray-700 text-sm font-medium mb-1">Alamat Toko</label>
                      <input v-model="storeAddress" type="text" placeholder="Masukkan alamat lengkap" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e84797]" />
                    </div>
                    <div>
                      <label class="block text-gray-700 text-sm font-medium mb-1">Deskripsi Toko</label>
                      <textarea v-model="storeDescription" rows="3" placeholder="Ceritakan tentang tokomu..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#e84797] resize-none"></textarea>
                    </div>
                  </div>

                  <!-- Step 2: Dokumen -->
                  <div v-else-if="currentStep === 2" key="step-2" class="space-y-4">
                    <div>
                      <label class="block text-gray-700 text-sm font-medium mb-1">Upload KTP</label>
                      <input type="file" @change="handleFileUpload($event, 'ktp')" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-[#e84797]/10 file:text-[#e84797]" />
                    </div>
                    <div>
                      <label class="block text-gray-700 text-sm font-medium mb-1">Upload NPWP</label>
                      <input type="file" @change="handleFileUpload($event, 'npwp')" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-[#e84797]/10 file:text-[#e84797]" />
                    </div>
                    <div class="flex items-start gap-2">
                      <input v-model="agreed" type="checkbox" class="mt-0.5 w-4 h-4 text-[#e84797] rounded focus:ring-[#e84797]" />
                      <label class="text-xs text-gray-700">
                        Saya setuju dengan <span class="text-[#e84797] font-medium">Syarat dan Ketentuan</span>
                      </label>
                    </div>
                  </div>
                </Transition>
              </div>

              <div class="px-6 py-4 bg-gray-50 flex justify-between gap-3">
                <button v-if="currentStep === 2" @click="prevStep" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100">
                  Kembali
                </button>
                <div class="flex-grow"></div>
                <button v-if="currentStep === 1" @click="nextStep" :disabled="!storeName.trim() || !storeAddress.trim()" class="px-6 py-2.5 bg-[#203f9a] text-white text-sm font-medium rounded-lg hover:bg-[#94c2da] disabled:opacity-50">
                  Lanjut
                </button>
                <button v-else @click="submitSellerRequest" :disabled="!agreed || !ktpFile || !npwpFile" class="px-6 py-2.5 bg-[#e84797] text-white text-sm font-medium rounded-lg hover:bg-[#d03a84] disabled:opacity-50">
                  Kirim Pendaftaran
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "vue-toastification";

const router = useRouter();
const toast = useToast();
const user = ref(null);
const loadingSeller = ref(false);
const isProfileExpanded = ref(false);
const isStoreExpanded = ref(false);

// === State untuk Modal Daftar Seller ===
const showStoreForm = ref(false);
const currentStep = ref(1);
const storeName = ref("");
const storeAddress = ref("");
const storeDescription = ref("");
const ktpFile = ref(null);
const npwpFile = ref(null);
const agreed = ref(false);

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) return router.push("/login");

  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  try {
    const res = await axios.get("http://127.0.0.1:8000/api/profile");
    user.value = res.data;
  } catch (error) {
    console.error("Gagal mengambil data user:", error);
    localStorage.removeItem("authToken");
    router.push("/login");
  }
});

// === Modal Handler ===
const openStoreForm = () => {
  currentStep.value = 1;
  storeName.value = "";
  storeAddress.value = "";
  storeDescription.value = "";
  ktpFile.value = null;
  npwpFile.value = null;
  agreed.value = false;
  showStoreForm.value = true;
};

const closeModal = () => {
  showStoreForm.value = false;
};

const nextStep = () => {
  if (storeName.value.trim() && storeAddress.value.trim()) {
    currentStep.value = 2;
  }
};

const prevStep = () => {
  currentStep.value = 1;
};

const handleFileUpload = (event, field) => {
  const file = event.target.files[0];
  if (field === "ktp") ktpFile.value = file;
  if (field === "npwp") npwpFile.value = file;
};

// === Submit Request Seller ===
const submitSellerRequest = async () => {
  if (!agreed.value || !ktpFile.value || !npwpFile.value) {
    toast.error("Harap lengkapi semua persyaratan.");
    return;
  }

  loadingSeller.value = true;

  try {
    const formData = new FormData();
    formData.append("store_name", storeName.value);
    formData.append("address", storeAddress.value);
    formData.append("description", storeDescription.value);
    formData.append("ktp", ktpFile.value);
    formData.append("npwp", npwpFile.value);

    const res = await axios.post("http://127.0.0.1:8000/api/manage/become-seller", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    user.value.role = "penjual_pending";
    localStorage.setItem("userRole", "penjual_pending");
    toast.success(res.data?.message || "Permintaan penjual berhasil dikirim!");
    closeModal();
  } catch (error) {
    console.error("Submit seller failed:", error);
    const msg = error.response?.data?.message || "Gagal mengirim permintaan.";
    toast.error(msg);
  } finally {
    loadingSeller.value = false;
  }
};

// === Upload Foto Profil ===
const handlePhotoUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    toast.error("Ukuran foto maksimal 2MB");
    return;
  }

  const formData = new FormData();
  formData.append("profile_photo", file);

  try {
    const res = await axios.post("http://127.0.0.1:8000/api/profile/update", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    user.value = res.data.user;
    toast.success("Foto profil berhasil diperbarui!");
    window.dispatchEvent(new Event("user-profile-updated"));
  } catch (error) {
    console.error("Upload failed:", error);
    toast.error("Gagal mengupload foto profil.");
  }
};
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap");

.font-ubuntu {
  font-family: "Ubuntu", sans-serif;
}

/* Animasi Modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-slide-enter-active,
.modal-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.2s ease;
}
.modal-slide-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}
.modal-slide-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
.step-fade-enter-active,
.step-fade-leave-active {
  transition: opacity 0.25s ease;
}
.step-fade-enter-from,
.step-fade-leave-to {
  opacity: 0;
}
</style>