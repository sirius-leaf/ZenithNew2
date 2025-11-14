<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();
const pcBuild = ref([]);
const loading = ref(true);
const message = ref(null);

const token = localStorage.getItem("token"); // jika pakai sanctum/token

// Ambil data dari API
const fetchBuild = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/manage/pcBuild");
    pcBuild.value = res.data.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const deleteBuild = async (id) => {
  if (!confirm("Hapus PC Build ini?")) return;

  try {
    await axios.delete(`http://127.0.0.1:8000/api/manage/pcBuild/${id}`);

    message.value = "PC Build berhasil dihapus!";
    pcBuild.value = pcBuild.value.filter((b) => b.id_build !== id);

    setTimeout(() => (message.value = null), 2000);
  } catch (err) {
    console.error(err);
  }
};

onMounted(() => {
  fetchBuild();
});
</script>

<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">Daftar PC Build</h2>

      <button
        @click="router.push('/dashboard/manage/pcBuild/create')"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        + Tambah PC Build
      </button>
    </div>

    <div
      v-if="message"
      class="bg-green-200 text-green-800 p-3 px-4 rounded mb-4"
    >
      {{ message }}
    </div>

    <div v-if="loading" class="text-gray-500">Memuat data...</div>

    <table
      v-else
      class="min-w-full bg-white border border-gray-300 rounded shadow"
    >
      <thead>
        <tr class="bg-gray-100 text-left border-b">
          <th class="py-2 px-3">Nama Build</th>
          <th class="py-2 px-3 w-48">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="b in pcBuild"
          :key="b.id_build"
          class="border-b hover:bg-gray-50"
        >
          <td class="py-2 px-3">{{ b.nama_build }}</td>

          <td class="py-2 px-3 flex gap-2">
            <button
              @click="
                router.push(`/dashboard/manage/pcBuild/${b.id_build}/edit`)
              "
              class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600"
            >
              Edit
            </button>

            <button
              @click="deleteBuild(b.id_build)"
              class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
            >
              Hapus
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
