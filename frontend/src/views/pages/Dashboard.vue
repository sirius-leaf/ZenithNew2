<!-- src/views/pages/Dashboard.vue -->
<script setup>
/* =============================
   IMPORT
============================= */
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "vue-toastification";

/* =============================
   STATE & INITIAL
============================= */
const router = useRouter();
const toast = useToast();
const user = ref(null);
const loadingSeller = ref(false);
let role = "";

/* =============================
   LOAD USER SAAT HALAMAN DIBUKA
============================= */
onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  try {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    const res = await axios.get("http://127.0.0.1:8000/api/user");

    user.value = res.data;

    if (res.data.role) {
      localStorage.setItem("userRole", res.data.role);
      role = res.data.role;
    }
  } catch (error) {
    console.error("Gagal mengambil data user:", error);
    localStorage.removeItem("authToken");
    localStorage.removeItem("userRole");
    router.push("/login");
  }
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
   LOGOUT HANDLER
============================= */
const handleLogout = () => {
  localStorage.removeItem("authToken");
  localStorage.removeItem("userRole");
  delete axios.defaults.headers.common["Authorization"];
  router.push("/login");
};
</script>

<template>
  <div v-if="user" class="min-h-screen bg-gray-50">
    <main class="p-4 md:p-6">
      <!-- Welcome -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">
          Selamat datang, {{ user.name }}
        </h2>
        <p class="text-gray-600">Email: {{ user.email }}</p>
      </div>

      <!-- =======================
        STATUS MENJADI PENJUAL
      ======================== -->
      <section class="mb-8">
        <!-- A. User Biasa -->
        <div
          v-if="user.role === 'user'"
          class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg"
        >
          <h3 class="text-lg font-bold mb-2">Ingin mulai berjualan?</h3>

          <p class="mb-4 opacity-90">
            Daftarkan akun Anda menjadi penjual untuk membuka toko dan menjual
            produk rakitan PC.
          </p>

          <button
            @click="requestSeller"
            :disabled="loadingSeller"
            class="bg-white text-blue-600 px-5 py-2 rounded-lg font-semibold hover:bg-gray-100 transition disabled:opacity-70"
          >
            {{ loadingSeller ? "Memproses..." : "Daftar Menjadi Penjual" }}
          </button>
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
          <div class="flex justify-between items-center">
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
              class="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700 transition shadow"
            >
              Kelola / Buka Toko
            </router-link>
          </div>
        </div>
      </section>

      <!-- Placeholder fitur lain -->
      <section class="grid grid-cols-1 lg:grid-cols-1 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-bold text-gray-800 mb-4">Tag Populer</h3>
          <div
            class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-100 rounded-lg"
          >
            <p class="text-gray-400 text-sm">Belum ada data tag.</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-bold text-gray-800 mb-4">Rekomendasi</h3>
          <div
            class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-100 rounded-lg"
          >
            <p class="text-gray-400 text-sm">Belum ada rekomendasi.</p>
          </div>
        </div>
      </section>

      <!-- Router View -->
      <div
        v-if="$route.path !== '/dashboard'"
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <router-view />
      </div>
    </main>
  </div>
</template>

<style scoped></style>
