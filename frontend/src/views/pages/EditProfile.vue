<template>
  <div class="max-w-xl mx-auto p-6 bg-white rounded-xl shadow mt-16 mb-16">
    <h2 class="text-xl font-semibold mb-4 text-[#203f9a]">Edit Profil</h2>

    <!-- Ubah Biodata Diri -->
    <div class="mb-6">
      <h3 class="text-lg font-medium mb-3 text-[#203f9a]">Ubah Biodata Diri</h3>
      <div class="space-y-4">
        <div>
          <label class="text-sm text-[#203f9a] font-medium">Nama</label>
          <input
            v-model="editName"
            type="text"
            class="w-full px-3 py-2 border border-gray-200 rounded-lg mt-1 focus:outline-none focus:ring-2 focus:ring-[#e84797] transition"
          />
        </div>
      </div>
    </div>

    <!-- Ubah Kontak -->
    <div class="mb-6">
      <h3 class="text-lg font-medium mb-3 text-[#203f9a]">Ubah Kontak</h3>
      <div class="space-y-4">
        <div>
          <label class="text-sm text-[#203f9a] font-medium">Email</label>
          <div
            class="w-full px-3 py-2 border border-gray-200 rounded-lg mt-1 bg-white flex items-center justify-between"
          >
            <span>{{ userEmail }}</span>
            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded"
              >Terverifikasi</span
            >
          </div>
        </div>

        <div>
          <label class="text-sm text-[#203f9a] font-medium">Nomor HP</label>
          <input
            v-model="editPhone"
            type="text"
            class="w-full px-3 py-2 border border-gray-200 rounded-lg mt-1 focus:outline-none focus:ring-2 focus:ring-[#e84797] transition"
          />
        </div>

        <div>
          <label class="text-sm text-[#203f9a] font-medium">Alamat</label>
          <textarea
            v-model="editAddress"
            class="w-full px-3 py-2 border border-gray-200 rounded-lg mt-1 focus:outline-none focus:ring-2 focus:ring-[#e84797] transition"
            rows="2"
          ></textarea>
        </div>
      </div>
    </div>

    <div class="space-y-3">
      <button
        @click="saveChanges"
        :disabled="loading"
        class="w-full py-2 bg-[#203f9a] text-white rounded-lg hover:bg-[#94c2da] disabled:opacity-50 transition"
      >
        {{ loading ? "Menyimpan..." : "Simpan Perubahan" }}
      </button>

      <button
        @click="$router.back()"
        class="w-full py-2 bg-[#e84797] text-white rounded-lg hover:bg-[#d03a84] transition"
      >
        Batal
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

// State
const editName = ref("");
const editPhone = ref("");
const editAddress = ref("");
const userEmail = ref("");
const loading = ref(false);

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    alert("Sesi Anda telah habis. Silakan login kembali.");
    router.push("/login");
    return;
  }

  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  try {
    const res = await axios.get("http://127.0.0.1:8000/api/profile");

    editName.value = res.data.name || "";
    editPhone.value = res.data.no_telpon || "";
    editAddress.value = res.data.alamat || "";
    userEmail.value = res.data.email || "";
  } catch (err) {
    console.error("Error fetching profile:", err);
    alert("Gagal memuat data profil.");
    router.back();
  }
});

const saveChanges = async () => {
  loading.value = true;

  try {
    await axios.post("http://127.0.0.1:8000/api/profile/update", {
      name: editName.value,
      no_telpon: editPhone.value,
      alamat: editAddress.value,
    });

    alert("Profil berhasil diperbarui!");
    router.back();
  } catch (err) {
    console.error("Error saving profile:", err);
    alert("Gagal menyimpan perubahan.");
  } finally {
    loading.value = false;
  }
};
</script>
