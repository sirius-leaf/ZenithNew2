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
    cooler: { id: null, produk: null },
    "video-card": { id: null, produk: null },
    case: { id: null, produk: null },
    monitor: { id: null, produk: null },
    mouse: { id: null, produk: null },
    keyboard: { id: null, produk: null },
  },
});

const totalHarga = ref(0);

const modal = ref({
  motherboard: false,
  cpu: false,
  ram: false,
  psu: false,
  storage: false,
  cooler: false,
  "video-card": false,
  case: false,
  monitor: false,
  mouse: false,
  keyboard: false,
});

const componentList = [
  { key: 'motherboard', label: 'Motherboard' },
  { key: 'cpu', label: 'CPU' },
  { key: 'ram', label: 'RAM' },
  { key: 'psu', label: 'Power Supply' },
  { key: 'storage', label: 'Storage' },
  { key: 'cooler', label: 'CPU Cooler' },
  { key: 'video-card', label: 'Video Card' },
  { key: 'case', label: 'Case' },
  { key: 'monitor', label: 'Monitor' },
  { key: 'mouse', label: 'Mouse' },
  { key: 'keyboard', label: 'Keyboard' },
];

const errorMessage = ref(null);
const successMessage = ref(null);

function updateHarga() {
  let tempTotal = 0;

  Object.values(form.value.komponen).forEach((element) => {
    tempTotal += Number(
      variants.value.find((p) => p.id_varian === element.produk)?.harga || "0"
    );
  });

  totalHarga.value = tempTotal;
}

function getVariantDetails(id) {
  if (!id) return null;
  const varian = variants.value.find((v) => v.id_varian === id);
  if (!varian) return null;
  
  const product = products.value.find((p) => p.id_produk === varian.id_produk);
  
  return {
    image: varian.gambar_varian,
    name: varian.nama_varian,
    productName: product?.nama_produk || '-',
    price: varian.harga,
    stock: varian.stok
  };
}

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  await loadProducts();
  await loadBuildData();
});

const loadProducts = async () => {
  const res = await axios.get("http://127.0.0.1:8000/api/productAll");
  products.value = res.data.data;
  variants.value = res.data.variants;
};

const loadBuildData = async () => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/manage/pcBuild/${id}`);
    form.value.nama_build = res.data.data.nama_build;

    const komponen = res.data.data.build_detail;
    komponen.forEach((e) => {
      if (form.value.komponen[e.bagian_komponen]) {
         form.value.komponen[e.bagian_komponen] = { id: e.id, produk: e.id_varian };
      }
    });
    
    updateHarga();
  } catch (err) {
    console.error("Failed to load build data", err);
    errorMessage.value = "Gagal memuat data build.";
  }
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
  <div class="max-w-5xl mx-auto my-10 p-6 bg-white rounded-xl shadow-lg">
    <h2 class="text-3xl font-bold mb-8 text-center text-gray-800">Edit PC Build</h2>

    <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      {{ errorMessage }}
    </div>

    <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
      {{ successMessage }}
    </div>

    <!-- Nama Build Input -->
    <div class="mb-8">
      <label class="block mb-2 font-semibold text-gray-700">Nama Build</label>
      <input 
        v-model="form.nama_build" 
        type="text" 
        class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
        placeholder="Contoh: PC Gaming 2024"
        required 
      />
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b-2 border-gray-200 text-left">
            <th class="py-4 px-4 font-semibold text-gray-600">Komponen</th>
            <th class="py-4 px-4 font-semibold text-gray-600">Gambar</th>
            <th class="py-4 px-4 font-semibold text-gray-600">Nama Produk</th>
            <th class="py-4 px-4 font-semibold text-gray-600">Harga</th>
            <th class="py-4 px-4 font-semibold text-gray-600 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="comp in componentList" :key="comp.key" class="border-b border-gray-100 hover:bg-gray-50 transition">
            <td class="py-4 px-4 font-medium text-gray-800">{{ comp.label }}</td>
            
            <!-- Gambar -->
            <td class="py-4 px-4">
              <div v-if="form.komponen[comp.key].produk" class="w-20 h-20 bg-gray-100 rounded-md overflow-hidden border border-gray-200 flex items-center justify-center">
                 <img 
                  :src="`http://127.0.0.1:8000/storage/${getVariantDetails(form.komponen[comp.key].produk)?.image}`" 
                  class="w-full h-full object-cover"
                  alt="Component"
                 />
              </div>
              <div v-else class="w-20 h-20 bg-gray-50 rounded-md border border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs">
                No Image
              </div>
            </td>

            <!-- Nama Produk -->
            <td class="py-4 px-4">
              <div v-if="form.komponen[comp.key].produk">
                <p class="font-bold text-pink-600 text-lg">
                  {{ getVariantDetails(form.komponen[comp.key].produk)?.productName }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ getVariantDetails(form.komponen[comp.key].produk)?.name }}
                </p>
              </div>
              <span v-else class="text-gray-400 italic">Belum dipilih</span>
            </td>

            <!-- Harga -->
            <td class="py-4 px-4 font-medium text-gray-700">
              {{ form.komponen[comp.key].produk ? 'Rp ' + Number(getVariantDetails(form.komponen[comp.key].produk)?.price).toLocaleString('id-ID') : '-' }}
            </td>

            <!-- Aksi -->
            <td class="py-4 px-4 text-center">
              <button 
                type="button"
                @click="modal[comp.key] = true"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition shadow-sm hover:shadow-md whitespace-nowrap"
              >
                {{ form.komponen[comp.key].produk ? 'Ubah' : 'Pilih' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer Actions -->
    <div class="mt-10 border-t pt-6 flex flex-col md:flex-row justify-between items-center gap-6">
      <div class="text-center md:text-left">
        <p class="text-gray-500 text-sm mb-1">Total Estimasi Harga</p>
        <p class="text-3xl font-bold text-gray-900">Rp {{ totalHarga.toLocaleString('id-ID') }}</p>
      </div>

      <div class="flex gap-4">
         <button 
          type="button" 
          @click="$router.back()"
          class="px-8 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition"
        >
          Batal
        </button>
        <button 
          @click="updateBuild"
          :disabled="isLoading"
          class="px-8 py-3 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl disabled:opacity-70 disabled:cursor-not-allowed"
        >
          {{ isLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </div>
    </div>

    <!-- Modals -->
    <ProductPicker
      v-for="comp in componentList"
      :key="'modal-' + comp.key"
      :open="modal[comp.key]"
      :label="comp.label"
      :products="products"
      @close="modal[comp.key] = false"
      @select="(id) => { form.komponen[comp.key].produk = id; updateHarga(); }"
    />
  </div>
</template>
