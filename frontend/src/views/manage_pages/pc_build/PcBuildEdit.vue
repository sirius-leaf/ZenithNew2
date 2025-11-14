<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const id = route.params.id;

const isLoading = ref(false);
const products = ref([]);
const form = ref({
  nama_build: "",
  komponen: {
    motherboard: { id: null, produk: null },
    cpu: { id: null, produk: null },
    ram: { id: null, produk: null },
    psu: { id: null, produk: null },
    storage: { id: null, produk: null },
  },
});

const errorMessage = ref(null);
const successMessage = ref(null);

onMounted(async () => {
  await loadProducts();
  await loadBuildData();
});

const loadProducts = async () => {
  const res = await axios.get("http://127.0.0.1:8000/api/productAll");
  products.value = res.data.data;
};

const loadBuildData = async () => {
  const res = await axios.get(`http://127.0.0.1:8000/api/manage/pcBuild/${id}`);
  form.value.nama_build = res.data.data.nama_build;

  const komponen = res.data.data.build_detail;
  komponen.forEach((e) => {
    form.value.komponen[e.bagian_komponen] = { id: e.id, produk: e.id_produk };
  });

  //form.value.komponen = res.data.data.build_detail;
  console.log(form);
};

const updateBuild = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = null;

    const res = await axios.put(
      `http://127.0.0.1:8000/api/manage/pcBuild/${id}`,
      form.value
    );

    successMessage.value = "Berhasil memperbarui build!";
    setTimeout(() => router.push("/dashboard/manage/pcBuild"), 1000);
  } catch (err) {
    errorMessage.value = err.response?.data?.message || "Gagal update data.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="max-w-xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Edit PC Build</h2>

    <div v-if="errorMessage" class="bg-red-200 text-red-700 p-3 rounded mb-3">
      {{ errorMessage }}
    </div>

    <div
      v-if="successMessage"
      class="bg-green-200 text-green-700 p-3 rounded mb-3"
    >
      {{ successMessage }}
    </div>

    <form @submit.prevent="updateBuild" class="space-y-4">
      <!-- Nama Build -->
      <div>
        <label class="block font-medium mb-1">Nama Build</label>
        <input
          type="text"
          v-model="form.nama_build"
          class="w-full border px-3 py-2 rounded"
          required
        />
      </div>

      <!-- COMBO SELECT COMPONENT -->
      <template v-for="(value, key) in form.komponen" :key="key">
        <div>
          <label class="block font-medium mb-1 capitalize">
            {{ key }}
          </label>

          <select
            v-model="form.komponen[key].produk"
            class="w-full border px-3 py-2 rounded"
          >
            <option disabled value="">Pilih {{ key }}</option>

            <option
              v-for="p in products"
              :key="p.id_produk"
              :value="p.id_produk"
            >
              {{ p.nama_produk }} ({{ p.merek }})
            </option>
          </select>
        </div>
      </template>

      <button
        type="submit"
        :disabled="isLoading"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        {{ isLoading ? "Menyimpan..." : "Simpan Perubahan" }}
      </button>
    </form>
  </div>
</template>
