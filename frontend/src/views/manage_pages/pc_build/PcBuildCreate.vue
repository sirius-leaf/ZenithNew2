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
    motherboard: null,
    cpu: null,
    ram: null,
    psu: null,
    storage: null,
    cooler: null,
    "video-card": null,
    case: null,
    monitor: null,
    mouse: null,
    keyboard: null,
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
  { key: 'motherboard', label: 'Motherboard', category: 'Motherboard' },
  { key: 'cpu', label: 'CPU', category: 'Processor' },
  { key: 'ram', label: 'RAM', category: 'RAM' },
  { key: 'psu', label: 'Power Supply', category: 'Power Supply' },
  { key: 'storage', label: 'Storage', category: 'Storage' },
  { key: 'cooler', label: 'CPU Cooler', category: 'Cooler' },
  { key: 'video-card', label: 'Video Card', category: 'VGA Card' },
  { key: 'case', label: 'Case', category: 'Casing PC' },
  { key: 'monitor', label: 'Monitor', category: 'Monitor' },
  { key: 'mouse', label: 'Mouse', category: 'Mouse' },
  { key: 'keyboard', label: 'Keyboard', category: 'Keyboard' },
];

const errors = ref({});
const isLoading = ref(false);

// Ambil token
const token = localStorage.getItem("authToken");

function updateHarga() {
  let tempTotal = 0;

  Object.values(form.value.komponen).forEach((element) => {
    tempTotal += Number(
      variants.value.find((p) => p.id_varian === element)?.harga || "0"
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

    router.push("/dashboard/manage/desktopLab");
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
  <div class="max-w-5xl mx-auto my-10 p-6 bg-white rounded-xl shadow-lg">
    <h2 class="text-3xl font-bold mb-8 text-center text-gray-800">Rakit PC Impianmu</h2>

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
      <p v-if="errors.nama_build" class="text-red-500 text-sm mt-1">
          {{ errors.nama_build[0] }}
      </p>
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
              <div v-if="form.komponen[comp.key]" class="w-20 h-20 bg-gray-100 rounded-md overflow-hidden border border-gray-200 flex items-center justify-center">
                 <img 
                  :src="`http://127.0.0.1:8000/storage/${getVariantDetails(form.komponen[comp.key])?.image}`" 
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
              <div v-if="form.komponen[comp.key]">
                <p class="font-bold text-pink-600 text-lg">
                  {{ getVariantDetails(form.komponen[comp.key])?.productName }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ getVariantDetails(form.komponen[comp.key])?.name }}
                </p>
              </div>
              <span v-else class="text-gray-400 italic">Belum dipilih</span>
              <p v-if="errors['komponen.' + comp.key]" class="text-red-500 text-xs mt-1">
                {{ errors["komponen." + comp.key][0] }}
              </p>
            </td>

            <!-- Harga -->
            <td class="py-4 px-4 font-medium text-gray-700">
              {{ form.komponen[comp.key] ? 'Rp ' + Number(getVariantDetails(form.komponen[comp.key])?.price).toLocaleString('id-ID') : '-' }}
            </td>

            <!-- Aksi -->
            <td class="py-4 px-4 text-center">
              <button 
                type="button"
                @click="modal[comp.key] = true"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition shadow-sm hover:shadow-md whitespace-nowrap"
              >
                {{ form.komponen[comp.key] ? 'Ubah' : 'Pilih' }}
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
          @click="submitForm"
          :disabled="isLoading"
          class="px-8 py-3 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl disabled:opacity-70 disabled:cursor-not-allowed"
        >
          {{ isLoading ? 'Menyimpan...' : 'Simpan Rakitan' }}
        </button>
      </div>
    </div>

    <!-- Modals -->
    <ProductPicker
      v-for="comp in componentList"
      :key="'modal-' + comp.key"
      :open="modal[comp.key]"
      :label="comp.label"
      :category="comp.category"
      :products="products"
      @close="modal[comp.key] = false"
      @select="(id) => { form.komponen[comp.key] = id; updateHarga(); }"
    />
  </div>
</template>
