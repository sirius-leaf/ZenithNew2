div
<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";

import ProductPicker from "../../../components/ui/ProductPicker.vue";

const route = useRoute();
const router = useRouter();

const id = route.params.id;

const isLoading = ref(false);
const products = ref([]);
const variants = ref([]);
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

const modal = ref({
  motherboard: false,
  cpu: false,
  ram: false,
  psu: false,
  storage: false,
});

const errorMessage = ref(null);
const successMessage = ref(null);

function getProductName(id) {
  const varian = variants.value.find((p) => p.id_varian === id);
  const idProduk = varian?.id_produk || -1;
  const namaVarian = varian?.nama_varian || "-";
  const namaProduk =
    products.value.find((p) => p.id_produk === idProduk)?.nama_produk || "-";

  return namaProduk + " (" + namaVarian + ")";
}

onMounted(async () => {
  await loadProducts();
  await loadBuildData();
});

const loadProducts = async () => {
  const res = await axios.get("http://127.0.0.1:8000/api/productAll");
  products.value = res.data.data;
  variants.value = res.data.variants;
};

const loadBuildData = async () => {
  const res = await axios.get(`http://127.0.0.1:8000/api/manage/pcBuild/${id}`);
  form.value.nama_build = res.data.data.nama_build;

  const komponen = res.data.data.build_detail;
  komponen.forEach((e) => {
    form.value.komponen[e.bagian_komponen] = { id: e.id, produk: e.id_varian };
  });

  //form.value.komponen = res.data.data.build_detail;
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

      <div>
        <label class="block mb-1 font-medium">Motherboard</label>

        <button
          type="button"
          @click="modal.motherboard = true"
          class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
        >
          {{
            form.komponen.motherboard.produk
              ? getProductName(form.komponen.motherboard.produk)
              : "Pilih Motherboard"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.motherboard']" class="text-red-500 text-sm">
          {{ errors["komponen.motherboard"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.motherboard"
          label="Motherboard"
          :products="products"
          @close="modal.motherboard = false"
          @select="(id) => (form.komponen.motherboard.produk = id)"
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
            form.komponen.cpu.produk
              ? getProductName(form.komponen.cpu.produk)
              : "Pilih CPU"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.cpu']" class="text-red-500 text-sm">
          {{ errors["komponen.cpu"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.cpu"
          label="CPU"
          :products="products"
          @close="modal.cpu = false"
          @select="(id) => (form.komponen.cpu.produk = id)"
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
            form.komponen.ram.produk
              ? getProductName(form.komponen.ram.produk)
              : "Pilih RAM"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.ram']" class="text-red-500 text-sm">
          {{ errors["komponen.ram"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.ram"
          label="RAM"
          :products="products"
          @close="modal.ram = false"
          @select="(id) => (form.komponen.ram.produk = id)"
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
            form.komponen.psu.produk
              ? getProductName(form.komponen.psu.produk)
              : "Pilih Power Supply"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.psu']" class="text-red-500 text-sm">
          {{ errors["komponen.psu"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.psu"
          label="Power Supply"
          :products="products"
          @close="modal.psu = false"
          @select="(id) => (form.komponen.psu.produk = id)"
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
            form.komponen.storage.produk
              ? getProductName(form.komponen.storage.produk)
              : "Pilih Storage"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.storage']" class="text-red-500 text-sm">
          {{ errors["komponen.storage"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.storage"
          label="Storage"
          :products="products"
          @close="modal.storage = false"
          @select="(id) => (form.komponen.storage.produk = id)"
        />
      </div>

      <!-- COMBO SELECT COMPONENT 
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
      </template> -->

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
