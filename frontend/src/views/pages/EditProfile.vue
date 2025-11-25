<template>
  <div class="max-w-xl mx-auto p-6 bg-white rounded-xl shadow">
    <h2 class="text-xl font-semibold mb-4">Edit Profil</h2>

    <div class="space-y-4">
      <div>
        <label class="text-sm text-gray-700 font-medium">Nama</label>
        <input
          v-model="editName"
          type="text"
          class="w-full px-3 py-2 border rounded-lg mt-1"
        />
      </div>

      <div>
        <label class="text-sm text-gray-700 font-medium">No. Telepon</label>
        <input
          v-model="editPhone"
          type="text"
          class="w-full px-3 py-2 border rounded-lg mt-1"
        />
      </div>

      <div>
        <label class="text-sm text-gray-700 font-medium">Alamat</label>
        <textarea
          v-model="editAddress"
          class="w-full px-3 py-2 border rounded-lg mt-1"
          rows="2"
        ></textarea>
      </div>

      <button
        @click="saveChanges"
        :disabled="loading"
        class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        {{ loading ? "Menyimpan..." : "Simpan Perubahan" }}
      </button>

      <button
        @click="$router.back()"
        class="w-full py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
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

const editName = ref("");
const editPhone = ref("");
const editAddress = ref("");
const loading = ref(false);

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  const res = await axios.get("http://127.0.0.1:8000/api/profile");

  editName.value = res.data.name;
  editPhone.value = res.data.no_telpon;
  editAddress.value = res.data.alamat;
});

const saveChanges = async () => {
  loading.value = true;

  try {
    await axios.put("http://127.0.0.1:8000/api/profile/update", {
      name: editName.value,
      no_telpon: editPhone.value,
      alamat: editAddress.value,
    });

    alert("Profil berhasil diperbarui!");
    router.back();
  } catch (err) {
    alert("Gagal menyimpan perubahan.");
  } finally {
    loading.value = false;
  }
};
</script>
