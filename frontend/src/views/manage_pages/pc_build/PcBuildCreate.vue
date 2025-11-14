<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

import ComponentSelect from "../../../components/ComponentSelect.vue";

const router = useRouter();

// State
const products = ref([]);
const form = ref({
  id_user: null,
  nama_build: "",
  komponen: {
    motherboard: "",
    cpu: "",
    ram: "",
    psu: "",
    storage: "",
  },
});

const errors = ref({});
const isLoading = ref(false);

// Ambil token
const token = localStorage.getItem("authToken");

// Ambil daftar produk + user login
onMounted(async () => {
  try {
    const userRes = await axios.get("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${token}` },
    });

    form.value.id_user = userRes.data.id;

    const productRes = await axios.get("http://127.0.0.1:8000/api/productAll");

    products.value = productRes.data.data;

    console.log(products);
  } catch (err) {
    console.error("Gagal memuat data:", err);
    if (err.response?.status === 401) router.push("/login");
  }
});

// Submit form ke API
const submitForm = async () => {
  isLoading.value = true;
  errors.value = {};

  try {
    await axios.post("http://127.0.0.1:8000/api/manage/pcBuild", form.value);

    router.push("/dashboard/manage/pcBuild");
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors;
    } else {
      alert("Terjadi kesalahan pada server.");
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="max-w-3xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Tambah PC Build Baru</h2>

    <form
      @submit.prevent="submitForm"
      class="space-y-6 bg-white p-6 rounded-xl shadow"
    >
      <!-- Nama Build -->
      <div>
        <label class="block mb-1 font-medium">Nama Build</label>
        <input
          v-model="form.nama_build"
          type="text"
          class="w-full border p-2 rounded"
          required
        />
        <p v-if="errors.nama_build" class="text-red-500 text-sm">
          {{ errors.nama_build[0] }}
        </p>
      </div>

      <!-- Dropdown Komponen -->
      <div>
        <h3 class="text-lg font-semibold mb-2">Komponen</h3>

        <ComponentSelect
          label="Motherboard"
          v-model="form.komponen.motherboard"
          :products="products"
          :error="errors['komponen.motherboard']"
        />

        <ComponentSelect
          label="CPU"
          v-model="form.komponen.cpu"
          :products="products"
          :error="errors['komponen.cpu']"
        />

        <ComponentSelect
          label="RAM"
          v-model="form.komponen.ram"
          :products="products"
          :error="errors['komponen.ram']"
        />

        <ComponentSelect
          label="Power Supply"
          v-model="form.komponen.psu"
          :products="products"
          :error="errors['komponen.psu']"
        />

        <ComponentSelect
          label="Storage"
          v-model="form.komponen.storage"
          :products="products"
          :error="errors['komponen.storage']"
        />
      </div>

      <!-- Tombol -->
      <div class="flex gap-3">
        <button
          type="submit"
          class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
          :disabled="isLoading"
        >
          {{ isLoading ? "Menyimpan..." : "Simpan" }}
        </button>

        <button
          type="button"
          class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
          @click="router.push('/dashboard/manage/pcBuild')"
        >
          Kembali
        </button>
      </div>
    </form>
  </div>
</template>

<!-- Sub-component untuk select pilihan (biar rapi) -->
<!-- <script>
export default {
  name: "ComponentSelect",
  props: {
    label: String,
    modelValue: String,
    products: Array,
    error: Array,
  },
  emits: ["update:modelValue"],
  template: `
    <div class="mb-4">
      <label class="block mb-1 font-medium">{{ label }}</label>

      <select
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-full border p-2 rounded"
        required
      >
        <option value="">-- Pilih Komponen --</option>
        <option
          v-for="p in products"
          :key="p.id_produk"
          :value="p.id_produk"
        >
          {{ p.nama_produk }} ({{ p.merek }})
        </option>
      </select>

      <p v-if="error" class="text-red-500 text-sm">{{ error[0] }}</p>
    </div>
  `,
};
</script> -->
