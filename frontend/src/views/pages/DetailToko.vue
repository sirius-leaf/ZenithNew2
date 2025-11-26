<template>
  <div class="container mx-auto p-4">
    <!-- Loading -->
    <div v-if="loading" class="text-center py-10">
      <p>Loading...</p>
    </div>

    <!-- Jika data toko sudah ada -->
    <div v-else>
      <!-- Header Toko -->
      <div class="flex justify-between border p-4 rounded mb-6">
        <div>
          <h1 class="text-2xl font-bold">{{ toko.toko_name }}</h1>
          <p class="text-gray-600">Alamat: {{ toko.user?.alamat }}</p>
          <p class="mt-2">{{ toko.deskripsi }}</p>
        </div>

        <div class="">
          <!-- Bintang Rating -->
          <div class="flex gap-2">
            <span
              v-for="star in stars"
              :key="star"
              class="text-yellow-400 text-xl"
            >
              <!-- bintang penuh jika rating >= star -->
              <span v-if="ratingToko['rata-rata'] >= star">★</span>

              <!-- bintang kosong -->
              <span v-else>☆</span>
            </span>
          </div>
          <h1>
            Rating Toko : {{ ratingToko["rata-rata"] }} ({{
              ratingToko["jumlah"]
            }}
            Ulasan)
          </h1>
        </div>
      </div>

      <!-- Daftar Produk -->
      <h2 class="text-xl font-semibold mb-3">Daftar Produk</h2>

      <div v-if="products.length === 0">
        <p>Toko belum memiliki produk.</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="p in products"
          :key="p.id_produk"
          class="border p-3 rounded"
        >
          <h3 class="font-bold">{{ p.nama_produk }}</h3>
          <p class="text-sm">{{ p.merek }}</p>
          <p class="text-xs mt-2 text-gray-500">{{ p.deskripsi }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";

const route = useRoute();
const tokoId = route.params.id;

const toko = ref(null);
const products = ref([]);
const loading = ref(true);
const ratingToko = ref({ "rata-rata": 0, jumlah: 0 });

const stars = [1, 2, 3, 4, 5];

onMounted(async () => {
  try {
    const res = await axios.get(`http://127.0.0.1:8000/api/toko/${tokoId}`);

    toko.value = res.data.data[0];
    products.value = res.data.products;
    ratingToko.value = res.data.ratingToko;
  } catch (error) {
    console.log(error);
  } finally {
    loading.value = false;
  }
});
</script>

<style>
/* Optional styling */
</style>
