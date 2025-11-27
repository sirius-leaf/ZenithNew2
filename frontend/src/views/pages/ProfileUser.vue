<template>
  <div class="w-full bg-gray-50 p-4 sm:p-4">

    <!-- Header -->
    <div class="mb-8 flex items-center gap-2">
      <button @click="$router.back()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
        ← Kembali ke Dashboard
      </button>
    </div>

    <!-- Loading -->
    <div v-if="!user" class="max-w-2xl mx-auto text-center py-10">
      <p class="text-gray-600">Memuat data akun...</p>
    </div>

    <!-- Profile Card -->
    <div v-else class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-6">
      <div class="flex flex-col md:flex-row gap-8">
        <!-- Left: Profile Photo -->
        <div class="flex flex-col items-center gap-4 w-full md:w-1/3 border-b md:border-b-0 md:border-r border-gray-100 pb-6 md:pb-0 md:pr-6">
          <div class="relative w-48 h-48 bg-gray-100 rounded-full overflow-hidden shadow-sm border border-gray-200">
            <img 
              :src="user.profile_photo ? `http://127.0.0.1:8000/storage/${user.profile_photo}` : 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'" 
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
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ubah Biodata Diri</h3>
            <div class="space-y-4">
              <!-- Nama -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                <span class="text-gray-500 text-sm">Nama</span>
                <div class="sm:col-span-2 flex items-center gap-2">
                  <span class="text-gray-900 font-medium">{{ user.name }}</span>
                  <button @click="$router.push('/profile/edit')" class="text-pink-600 text-sm font-medium hover:underline">Ubah</button>
                </div>
              </div>
              
              <!-- Tanggal Lahir (Placeholder) -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                <span class="text-gray-500 text-sm">Tanggal Lahir</span>
                <div class="sm:col-span-2 flex items-center gap-2">
                  <span class="text-pink-600 text-sm font-medium cursor-pointer hover:underline">Tambah Tanggal Lahir</span>
                </div>
              </div>

              <!-- Jenis Kelamin (Placeholder) -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                <span class="text-gray-500 text-sm">Jenis Kelamin</span>
                <div class="sm:col-span-2 flex items-center gap-2">
                  <span class="text-pink-600 text-sm font-medium cursor-pointer hover:underline">Tambah Jenis Kelamin</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: Ubah Kontak -->
          <div>
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ubah Kontak</h3>
            <div class="space-y-4">
              <!-- Email -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                <span class="text-gray-500 text-sm">Email</span>
                <div class="sm:col-span-2 flex items-center gap-2 flex-wrap">
                  <span class="text-gray-900 font-medium">{{ user.email }}</span>
                  <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Terverifikasi</span>
                  <!-- <button class="text-pink-600 text-sm font-medium hover:underline">Ubah</button> -->
                </div>
              </div>

              <!-- Nomor HP -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                <span class="text-gray-500 text-sm">Nomor HP</span>
                <div class="sm:col-span-2 flex items-center gap-2 flex-wrap">
                  <span v-if="user.no_telpon" class="text-gray-900 font-medium">{{ user.no_telpon }}</span>
                  <span v-else class="text-gray-400 italic">Belum diatur</span>
                  
                  <span v-if="user.no_telpon" class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Terverifikasi</span>
                  <button @click="$router.push('/profile/edit')" class="text-pink-600 text-sm font-medium hover:underline">Ubah</button>
                </div>
              </div>

              <!-- Alamat -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                <span class="text-gray-500 text-sm">Alamat</span>
                <div class="sm:col-span-2 flex items-center gap-2 flex-wrap">
                  <span v-if="user.alamat" class="text-gray-900 font-medium">{{ user.alamat }}</span>
                  <span v-else class="text-gray-400 italic">Belum diatur</span>
                  <button @click="$router.push('/profile/edit')" class="text-pink-600 text-sm font-medium hover:underline">Ubah</button>
                </div>
              </div>
            </div>
          </div>

          <!-- =======================
            STATUS MENJADI PENJUAL
          ======================== -->
          <section class="mt-8 border-t border-pink-200 pt-6">
            <h3 class="text-lg font-semibold text-neutral-950 mb-4">Status Toko</h3>
            
            <!-- A. User Biasa -->
            <div
              v-if="user.role === 'user'"
              class="bg-pink-50 border border-pink-200 rounded-xl p-6"
            >
              <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                  <h3 class="text-lg font-bold text-pink-800 mb-2">Ingin mulai berjualan?</h3>
                  <p class="text-pink-700 mb-4 opacity-90">
                    Daftarkan akun Anda menjadi penjual untuk membuka toko dan menjual
                    produk rakitan PC.
                  </p>
                </div>

                <button
                  @click="requestSeller"
                  :disabled="loadingSeller"
                  class="bg-pink-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-pink-700 transition disabled:opacity-70 whitespace-nowrap"
                >
                  {{ loadingSeller ? "Memproses..." : "Daftar Menjadi Penjual" }}
                </button>
              </div>
            </div>

            <!-- B. Status Pending -->
            <div
              v-else-if="user.role === 'penjual_pending'"
              class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 flex items-center gap-4"
            >
              <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">⏳</div>

              <div>
                <h3 class="text-lg font-bold text-yellow-800">Menunggu Konfirmasi</h3>
                <p class="text-yellow-700">
                  Permintaan Anda sedang ditinjau oleh Admin.
                </p>
              </div>
            </div>

            <!-- C. Penjual -->
            <div
              v-else-if="user.role === 'penjual'"
              class="bg-green-50 border border-green-200 rounded-xl p-6"
            >
              <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                  <h3 class="text-lg font-bold text-green-800">
                    Panel Penjual Aktif
                  </h3>
                  <p class="text-green-700">
                    Anda memiliki akses penuh untuk mengelola toko.
                  </p>
                </div>

                <router-link
                  to="/dashboard/manage/create-toko"
                  class="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700 transition shadow whitespace-nowrap"
                >
                  Kelola / Buka Toko
                </router-link>
              </div>
            </div>
          </section>

        </div>
      </div>

      <!-- Tombol Edit -->
      <button
        @click="$router.push('/profile/edit')"
        class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700"
      >
        Edit Profil
      </button>
    </div>
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

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) return router.push("/login");

  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  const res = await axios.get("http://127.0.0.1:8000/api/profile");
  user.value = res.data;
});

/* =============================
   REQUEST MENJADI PENJUAL
============================= */
const requestSeller = async () => {
  // 🔥 Validasi profil lengkap dulu
  if (!user.value.no_telpon || !user.value.alamat) {
    toast.error("Isi nomor telepon & alamat terlebih dahulu di Profil.", {
      timeout: 3000,
      closeOnClick: true,
    });

    router.push("/profile/edit");
    return;
  }

  loadingSeller.value = true;

  try {
    const token = localStorage.getItem("authToken");
    if (token && !axios.defaults.headers.common["Authorization"]) {
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }

    // Kirim request menjadi penjual
    const res = await axios.post(
      "http://127.0.0.1:8000/api/manage/become-seller"
    );

    user.value.role = "penjual_pending";
    localStorage.setItem("userRole", "penjual_pending");

    toast.success(res.data?.message ?? "Permintaan berhasil dikirim!", {
      timeout: 2500,
    });
  } catch (error) {
    console.error("Request seller failed:", error);

    if (error.response) {
      const data = error.response.data;

      toast.error(data.message ?? "Terjadi kesalahan.", {
        timeout: 3000,
      });
    } else {
      toast.error("Kesalahan jaringan. Coba lagi.", { timeout: 3000 });
    }
  } finally {
    loadingSeller.value = false;
  }
};

/* =============================
   UPLOAD FOTO PROFIL
============================= */
const handlePhotoUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validasi ukuran (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error("Ukuran foto maksimal 2MB");
    return;
  }

  const formData = new FormData();
  formData.append("profile_photo", file);
  // Kirim field lain yang diperlukan updateProfile (karena validasi 'sometimes')
  // Tapi di backend kita pakai 'sometimes', jadi aman kalau cuma kirim foto.
  // Namun, updateProfile di backend me-return user baru, jadi kita bisa update state.

  try {
    const res = await axios.post("http://127.0.0.1:8000/api/profile/update", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    user.value = res.data.user;
    toast.success("Foto profil berhasil diperbarui!");
    
    // Emit event atau update global state jika perlu agar navbar berubah
    // Cara paling gampang: reload window atau pakai event bus. 
    // Tapi karena navbar fetch ulang saat route change, mungkin cukup.
    // Untuk update instan di navbar, kita bisa simpan di localStorage atau trigger custom event.
    window.dispatchEvent(new Event('user-profile-updated'));

  } catch (error) {
    console.error("Upload failed:", error);
    toast.error("Gagal mengupload foto profil.");
  }
};
</script>