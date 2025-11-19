<template>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Tambah Produk Baru</h2>

    <!-- Success Message -->
    <div
      v-if="successMessage"
      class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg"
    >
      {{ successMessage }}
    </div>

    <!-- Error Message -->
    <div
      v-if="errorMessage"
      class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg"
    >
      {{ errorMessage }}
    </div>

    <form @submit.prevent="submitProduct" class="space-y-6">
      <input type="hidden" name="id_toko" :value="store.id" />

      <!-- Nama Produk -->
      <div>
        <label
          for="nama_produk"
          class="block text-sm font-medium text-gray-700 mb-1"
          >Nama Produk</label
        >
        <input
          type="text"
          id="nama_produk"
          v-model="form.nama_produk"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          required
        />
        <div v-if="errors.nama_produk" class="text-red-500 text-sm mt-1">
          {{ errors.nama_produk[0] }}
        </div>
      </div>

      <!-- Merek -->
      <div>
        <label for="merek" class="block text-sm font-medium text-gray-700 mb-1"
          >Merek</label
        >
        <input
          type="text"
          id="merek"
          v-model="form.merek"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          required
        />
        <div v-if="errors.merek" class="text-red-500 text-sm mt-1">
          {{ errors.merek[0] }}
        </div>
      </div>

      <!-- Deskripsi -->
      <div>
        <label
          for="deskripsi"
          class="block text-sm font-medium text-gray-700 mb-1"
          >Deskripsi</label
        >
        <textarea
          id="deskripsi"
          v-model="form.deskripsi"
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        ></textarea>
        <div v-if="errors.deskripsi" class="text-red-500 text-sm mt-1">
          {{ errors.deskripsi[0] }}
        </div>
      </div>

      <!-- Kategori -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <label class="block text-sm font-medium text-gray-700"
            >Kategori</label
          >
          <button
            type="button"
            @click="addKategori"
            class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded"
          >
            + Tambah Kategori
          </button>
        </div>

        <div
          v-for="(item, index) in form.kategori"
          :key="index"
          class="mb-3 p-3 border rounded-md"
        >
          <div class="flex items-center gap-3">
            <select
              v-model="item.id_kategori"
              class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="">-- Pilih Kategori --</option>
              <option
                v-for="k in categories"
                :key="k.id_kategori"
                :value="k.id_kategori"
              >
                {{ k.nama_kategori }}
              </option>
            </select>
            <button
              type="button"
              @click="removeKategori(index)"
              class="text-red-500 hover:text-red-700"
            >
              Hapus
            </button>
          </div>
        </div>
        <div v-if="errors.kategori" class="text-red-500 text-sm mt-1">
          {{ errors.kategori[0] }}
        </div>
      </div>

      <!-- Varian -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <label class="block text-sm font-medium text-gray-700">Varian</label>
          <button
            type="button"
            @click="addVarian"
            class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded"
          >
            + Tambah Varian
          </button>
        </div>

        <div
          v-for="(item, index) in form.varian"
          :key="index"
          class="mb-4 p-4 border rounded-md bg-gray-50"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Gambar Varian</label
              >
              <input
                type="file"
                @change="handleFileChange(index, $event)"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                accept="image/*"
                required
              />
              <div
                v-if="errors[`varian.${index}.gambar_varian`]"
                class="text-red-500 text-sm mt-1"
              >
                {{ errors[`varian.${index}.gambar_varian`][0] }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Nama Varian</label
              >
              <input
                type="text"
                v-model="item.nama_varian"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <div
                v-if="errors[`varian.${index}.nama_varian`]"
                class="text-red-500 text-sm mt-1"
              >
                {{ errors[`varian.${index}.nama_varian`][0] }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Harga</label
              >
              <input
                type="number"
                min="0"
                v-model.number="item.harga"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <div
                v-if="errors[`varian.${index}.harga`]"
                class="text-red-500 text-sm mt-1"
              >
                {{ errors[`varian.${index}.harga`][0] }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Stok</label
              >
              <input
                type="number"
                min="0"
                v-model.number="item.stok"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <div
                v-if="errors[`varian.${index}.stok`]"
                class="text-red-500 text-sm mt-1"
              >
                {{ errors[`varian.${index}.stok`][0] }}
              </div>
            </div>
          </div>
          <div class="mt-2">
            <button
              type="button"
              @click="removeVarian(index)"
              class="text-red-500 hover:text-red-700"
            >
              Hapus Varian
            </button>
          </div>
        </div>
        <div v-if="errors.varian" class="text-red-500 text-sm mt-1">
          {{ errors.varian[0] }}
        </div>
      </div>

      <div class="flex gap-3">
        <button
          type="submit"
          class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
        </button>
        <RouterLink
          :to="{ name: 'produk.index' }"
          class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg inline-block"
        >
          Kembali
        </RouterLink>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

// Form state
const form = ref({
  nama_produk: "",
  merek: "",
  deskripsi: "",
  kategori: [{ id_kategori: "" }],
  varian: [
    {
      nama_varian: "",
      harga: 0,
      stok: 0,
      gambar_varian: null,
    },
  ],
});

const categories = ref([]);
const store = ref({});
const errors = ref({});
const successMessage = ref("");
const errorMessage = ref("");
const isSubmitting = ref(false);

// Fetch initial data (categories & store)
const fetchData = async () => {
  try {
    const response = await axios.get(
      "http://127.0.0.1:8000/api/manage/productToko"
    );
    categories.value = response.data.data.categories;
    store.value = response.data.data.store;
  } catch (err) {
    errorMessage.value = "Gagal memuat data.";
    console.error(err);
  }
};

// Add new kategori field
const addKategori = () => {
  form.value.kategori.push({ id_kategori: "" });
};

// Remove kategori field
const removeKategori = (index) => {
  if (form.value.kategori.length > 1) {
    form.value.kategori.splice(index, 1);
  }
};

// Add new varian field
const addVarian = () => {
  form.value.varian.push({
    nama_varian: "",
    harga: 0,
    stok: 0,
    gambar_varian: null,
  });
};

// Remove varian field
const removeVarian = (index) => {
  if (form.value.varian.length > 1) {
    form.value.varian.splice(index, 1);
  }
};

// Handle file change for varian image
const handleFileChange = (index, event) => {
  const file = event.target.files[0];
  form.value.varian[index].gambar_varian = file;
};

// Submit product
const submitProduct = async () => {
  isSubmitting.value = true;
  errors.value = {};
  successMessage.value = "";
  errorMessage.value = "";

  const formData = new FormData();
  formData.append("nama_produk", form.value.nama_produk);
  formData.append("merek", form.value.merek);
  formData.append("deskripsi", form.value.deskripsi);
  form.value.kategori.forEach((k, i) => {
    formData.append(`kategori[${i}]`, k.id_kategori);
  });

  form.value.varian.forEach((v, i) => {
    if (v.gambar_varian) {
      formData.append(`varian[${i}][gambar_varian]`, v.gambar_varian);
    }
    formData.append(`varian[${i}][nama_varian]`, v.nama_varian);
    formData.append(`varian[${i}][harga]`, v.harga);
    formData.append(`varian[${i}][stok]`, v.stok);
  });

  try {
    const response = await axios.post(
      "http://127.0.0.1:8000/api/manage/product",
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
      }
    );

    successMessage.value = response.data.message;
    // Reset form
    form.value = {
      nama_produk: "",
      merek: "",
      deskripsi: "",
      kategori: [{ id_kategori: "" }],
      varian: [
        {
          nama_varian: "",
          harga: 0,
          stok: 0,
          gambar_varian: null,
        },
      ],
    };
    // Redirect after success
    setTimeout(() => {
      router.push({ name: "produk.index" });
    }, 1500);
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors;
    } else {
      errorMessage.value = "Gagal menyimpan produk.";
    }
    console.error(err);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
