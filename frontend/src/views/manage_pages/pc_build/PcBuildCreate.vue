<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

import ProductPicker from "../../../components/ui/ProductPicker.vue";

const router = useRouter();

// State
const products = ref([]);
const variants = ref([]);
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

const modal = ref({
  motherboard: false,
  cpu: false,
  ram: false,
  psu: false,
  storage: false,
});

const errors = ref({});
const isLoading = ref(false);

// Ambil token
const token = localStorage.getItem("authToken");

function getProductName(id) {
  const varian = variants.value.find((p) => p.id_varian === id);
  const idProduk = varian?.id_produk || -1;
  const namaVarian = varian?.nama_varian || "-";
  const namaProduk =
    products.value.find((p) => p.id_produk === idProduk)?.nama_produk || "-";

  return namaProduk + " (" + namaVarian + ")";
}

// Ambil daftar produk + user login
onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  try {
    const userRes = await axios.get("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${token}` },
    });

    form.value.id_user = userRes.data.id;

    const productRes = await axios.get("http://127.0.0.1:8000/api/productAll");

    products.value = productRes.data.data;
    variants.value = productRes.data.variants;
    console.log(variants.value);
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
    console.error("Gagal memuat data:", err);
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

        <div>
          <label class="block mb-1 font-medium">Motherboard</label>

          <button
            type="button"
            @click="modal.motherboard = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.motherboard
                ? getProductName(form.komponen.motherboard)
                : "Pilih Motherboard"
            }}
          </button>

          <!-- Error -->
          <p v-if="errors['komponen.motherboard']" class="text-red-500 text-sm">
            {{ errors["komponen.motherboard"][0] }}
          </p>

          <!-- Modal -->
          <ProductPicker
            :open="modal.motherboard"
            label="Motherboard"
            :products="products"
            @close="modal.motherboard = false"
            @select="(id) => (form.komponen.motherboard = id)"
          />
        </div>

        <div>
          <label class="block mb-1 font-medium">CPU</label>

          <button
            type="button"
            @click="modal.cpu = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.cpu
                ? getProductName(form.komponen.cpu)
                : "Pilih CPU"
            }}
          </button>

          <!-- Error -->
          <p v-if="errors['komponen.cpu']" class="text-red-500 text-sm">
            {{ errors["komponen.cpu"][0] }}
          </p>

          <!-- Modal -->
          <ProductPicker
            :open="modal.cpu"
            label="CPU"
            :products="products"
            @close="modal.cpu = false"
            @select="(id) => (form.komponen.cpu = id)"
          />
        </div>

        <div>
          <label class="block mb-1 font-medium">RAM</label>

          <button
            type="button"
            @click="modal.ram = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.ram
                ? getProductName(form.komponen.ram)
                : "Pilih RAM"
            }}
          </button>

          <!-- Error -->
          <p v-if="errors['komponen.ram']" class="text-red-500 text-sm">
            {{ errors["komponen.ram"][0] }}
          </p>

          <!-- Modal -->
          <ProductPicker
            :open="modal.ram"
            label="RAM"
            :products="products"
            @close="modal.ram = false"
            @select="(id) => (form.komponen.ram = id)"
          />
        </div>

        <div>
          <label class="block mb-1 font-medium">Power Supply</label>

          <button
            type="button"
            @click="modal.psu = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.psu
                ? getProductName(form.komponen.psu)
                : "Pilih Power Supply"
            }}
          </button>

          <!-- Error -->
          <p v-if="errors['komponen.psu']" class="text-red-500 text-sm">
            {{ errors["komponen.psu"][0] }}
          </p>

          <!-- Modal -->
          <ProductPicker
            :open="modal.psu"
            label="Power Supply"
            :products="products"
            @close="modal.psu = false"
            @select="(id) => (form.komponen.psu = id)"
          />
        </div>

        <div>
          <label class="block mb-1 font-medium">Storage</label>

          <button
            type="button"
            @click="modal.storage = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.storage
                ? getProductName(form.komponen.storage)
                : "Pilih Storage"
            }}
          </button>

          <!-- Error -->
          <p v-if="errors['komponen.storage']" class="text-red-500 text-sm">
            {{ errors["komponen.storage"][0] }}
          </p>

          <!-- Modal -->
          <ProductPicker
            :open="modal.storage"
            label="Storage"
            :products="products"
            @close="modal.storage = false"
            @select="(id) => (form.komponen.storage = id)"
          />
        </div>
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
