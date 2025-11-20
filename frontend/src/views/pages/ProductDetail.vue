<script setup>
import { ref, onMounted, computed } from "vue"; // <-- Tambahkan computed
import axios from "axios";
import { useRoute } from "vue-router";
import { useCartStore } from "@/stores/cartStore";

const route = useRoute();
const { updateCartItem } = useCartStore();

const product = ref(null);
const loading = ref(true);
const error = ref(null);
const successMessage = ref(null);
const quantities = ref({});

// 1. COMPUTED PROPERTY untuk mendapatkan URL Gambar Utama
const mainImage = computed(() => {
  // Cek apakah produk dan varian pertama ada
  if (
    product.value &&
    product.value.variant &&
    product.value.variant.length > 0
  ) {
    const imagePath = product.value.variant[0].gambar_varian;
    // Kita konstruksi URL storage lengkap:
    if (imagePath) {
      return `http://127.0.0.1:8000/storage/${imagePath}`;
    }
  }
  // Default gambar jika tidak ada varian atau gambar
  return "https://via.placeholder.com/400x300/CCCCCC?text=No+Image";
});

const initializeQuantities = (variants) => {
  const q = {};
  variants.forEach((variant) => {
    q[variant.id_varian] = 1;
  });
  quantities.value = q;
};

const handleAddToCart = (variant) => {
  const kuantitas = quantities.value[variant.id_varian];

  if (kuantitas <= 0 || kuantitas > variant.stok) {
    alert("Kuantitas tidak valid atau melebihi stok.");
    return;
  }

  updateCartItem(variant.id_varian, kuantitas, {
    nama_varian: variant.nama_varian,
    harga: variant.harga,
    stok: variant.stok,
    product_name: product.value.nama_produk,
  });

  successMessage.value = `Berhasil menambahkan ${kuantitas}x ${variant.nama_variant} ke keranjang!`;
  setTimeout(() => (successMessage.value = null), 3000);
};

const fetchProductDetail = async () => {
  try {
    const productId = route.params.id;
    const response = await axios.get(
      `http://127.0.0.1:8000/api/products/${productId}`
    );

    product.value = response.data;
    initializeQuantities(response.data.variant);
  } catch (err) {
    error.value = err;
    console.error("Error fetching product detail:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchProductDetail();
});
</script>

<template>
  <div class="bg-white min-h-screen">
    <!-- Breadcrumb / Back Button -->
    <div class="container mx-auto px-6 py-4">
      <router-link
        to="/"
        class="text-pink-500 hover:text-pink-700 font-medium text-lg font-['Ubuntu']"
      >
        &larr; Kembali ke Daftar Produk
      </router-link>
    </div>

    <!-- Konten Utama -->
    <div class="container mx-auto px-6 pb-12 max-w-[1440px]">
      <!-- Header Produk (Gambar + Info) -->
      <div class="flex flex-col md:flex-row gap-10 mt-6">
        <!-- Gambar Produk -->
        <div class="w-full md:w-2/5">
          <div class="bg-gray-100 rounded-lg overflow-hidden mb-4">
            <img
              :src="product.gambar_utama"
              :alt="product.nama"
              class="w-full h-auto object-cover"
            />
          </div>
          <div class="flex gap-3">
            <img
              v-for="(thumb, idx) in product.thumbnails"
              :key="idx"
              :src="thumb"
              class="w-20 h-20 object-cover rounded-md border-2 border-transparent hover:border-pink-400 cursor-pointer"
            />
          </div>
        </div>

        <!-- Info Produk -->
        <div class="w-full md:w-3/5">
          <h1 class="text-4xl font-bold text-gray-900 mb-2 font-['Ubuntu']">
            {{ product.nama }}
          </h1>
          <p class="text-3xl font-bold text-gray-900 mb-4 font-['Ubuntu']">
            Rp {{ product.harga.toLocaleString("id-ID") }}
          </p>

          <!-- Rating -->
          <div class="flex items-center gap-2 mb-6">
            <span class="text-yellow-400 text-2xl">★★★★★</span>
            <span class="text-gray-600 font-['Ubuntu']"
              >{{ product.rating }} ({{ product.total_ulasan }} ulasan)</span
            >
          </div>

          <hr class="border-blue-900 my-4" />

          <!-- Penjual -->
          <div class="flex items-center gap-4 mb-8">
            <img
              :src="product.penjual.logo"
              class="w-14 h-14 rounded-full object-cover"
            />
            <div>
              <p class="text-xl font-medium font-['Ubuntu'] text-gray-900">
                {{ product.penjual.nama }}
              </p>
              <p class="text-sm text-gray-600 font-['Ubuntu']">
                All About {{ product.penjual.nama }}
              </p>
            </div>
            <div
              v-if="product.penjual.verified"
              class="w-5 h-5 rounded-full bg-pink-500 flex items-center justify-center"
            >
              <span class="text-white text-xs">✓</span>
            </div>
          </div>

          <!-- Pilih Warna -->
          <div class="mb-6">
            <p class="text-xl font-medium text-blue-900 font-['Ubuntu']">
              Pilih Warna:
            </p>
            <p class="text-xl text-pink-500/60 font-['Ubuntu']">
              {{ product.varian_warna.length }} Warna
            </p>
          </div>

          <!-- Pilih Kapasitas -->
          <div class="mb-6">
            <p class="text-xl font-medium text-gray-900 font-['Ubuntu']">
              Pilih Kapasitas:
            </p>
            <span class="text-xl text-gray-600 font-['Ubuntu']">{{
              product.varian_kapasitas.length
            }}</span>
            <span class="text-xl text-gray-500 font-['Ubuntu']"> </span>
            <span class="text-xl text-gray-600 font-['Ubuntu']">Kapasitas</span>
          </div>

          <!-- Kuantitas -->
          <div class="flex items-center gap-4 mb-6">
            <span class="text-xl font-medium text-gray-900 font-['Ubuntu']"
              >Kuantitas</span
            >
            <div class="flex items-center">
              <button
                class="w-10 h-10 bg-white border-2 border-pink-500 text-pink-500 font-bold rounded"
              >
                -
              </button>
              <span
                class="w-12 text-center text-xl font-bold font-['Ubuntu'] text-pink-500"
                >1</span
              >
              <button
                class="w-10 h-10 bg-white border-2 border-pink-500 text-pink-500 font-bold rounded"
              >
                +
              </button>
            </div>
            <span class="text-xl text-pink-500 font-medium font-['Ubuntu']"
              >Tersedia</span
            >
          </div>

          <!-- Tombol Aksi -->
          <div class="flex gap-4">
            <button
              class="px-8 py-4 bg-pink-500 text-white font-bold text-xl rounded-lg font-['Ubuntu']"
            >
              Beli Langsung
            </button>
            <button
              class="px-8 py-4 bg-rose-100 border-2 border-pink-500 text-pink-500 font-bold text-xl rounded-lg font-['Ubuntu']"
            >
              Keranjang
            </button>
          </div>
        </div>
      </div>

      <!-- Deskripsi Produk -->
      <div class="mt-16">
        <h2
          class="text-3xl font-medium text-gray-900 mb-6 font-['Ubuntu'] leading-7"
        >
          Deskripsi Produk
        </h2>
        <div
          class="bg-white p-6 rounded-sm font-['Ubuntu'] text-base text-gray-900 leading-7 whitespace-pre-line border"
        >
          {{ product.deskripsi }}
        </div>
      </div>

      <!-- Penilaian Produk -->
      <div class="mt-16">
        <h2
          class="text-3xl font-medium text-gray-900 mb-6 font-['Ubuntu'] leading-7"
        >
          Penilaian Produk
        </h2>
        <div class="space-y-8">
          <div
            v-for="(ulasan, idx) in product.ulasan"
            :key="idx"
            class="bg-white p-6 rounded-[3px] border"
          >
            <div class="flex items-start gap-4">
              <div
                class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-bold text-gray-700"
              >
                {{ ulasan.nama.charAt(0) }}
              </div>
              <div>
                <h3
                  class="text-2xl text-gray-900 font-normal font-['Ubuntu'] leading-7"
                >
                  {{ ulasan.nama }}
                </h3>
                <p
                  class="text-xs text-gray-600 font-light font-['Ubuntu'] mt-1"
                >
                  {{ ulasan.tanggal }} | {{ ulasan.jam }} | Variasi:
                  {{ ulasan.variasi }}
                </p>
                <div class="flex mt-2">
                  <!-- Bintang placeholder (visual tetap) -->
                  <span class="w-5 h-5 bg-yellow-400 inline-block mr-1"></span>
                  <span class="w-5 h-5 bg-yellow-400 inline-block mr-1"></span>
                  <span class="w-5 h-5 bg-yellow-400 inline-block mr-1"></span>
                  <span class="w-5 h-5 bg-yellow-400 inline-block mr-1"></span>
                  <span class="w-5 h-5 bg-yellow-400 inline-block"></span>
                </div>
                <p
                  class="text-2xl text-gray-900 font-light font-['Ubuntu'] mt-3"
                >
                  {{ ulasan.komentar }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ProductDetail",
  data() {
    return {
      product: {
        id: 1,
        nama: "Samsung Galaxy A07",
        harga: 1399000,
        rating: 4.8,
        total_ulasan: 5,
        gambar_utama: "https://placehold.co/489x513",
        thumbnails: [
          "https://placehold.co/136x118",
          "https://placehold.co/136x118",
          "https://placehold.co/136x118",
        ],
        penjual: {
          nama: "Samsung Indonesia",
          logo: "https://placehold.co/108x108",
          verified: true,
        },
        varian_warna: ["Orange", "Hitam", "Biru"],
        varian_kapasitas: ["8/256GB", "4/128GB"],
        deskripsi: `Samsung Galaxy A07 8/256GB Garansi Resmi Samsung
BELI SEKARANG DAN DAPATKAN 20 ITEM BONUS
READY STOK !!!
Garansi Resmi 1 TAHUN SAMSUNG INDONESIA
Barang terdaftar Resmi Di pusat SAMSUNG
(Garansi Bisa Di Claim Di Seluruh Service Center SAMSUNG INDONESIA)
-100% Produk Original
-100% Produk Baru
-100% Produk Segel
-100% IMEI Terdaftar di KEMENPERIN
GARANSI :
-Handphone : 1 Tahun
-Software: Seumur Hidup
-Kepala Charger : 6 Bulan
-Usb Cable : 6 Bulan
-Battery : 6 Bulan
Note: Claim Garansi Dan Asuransi Dapat Dilakukan Di Seluruh Service Center SAMSUNG Indonesia`,
        ulasan: [
          {
            nama: "Andi",
            tanggal: "23-10-2024",
            jam: "10.20",
            variasi: "Orange",
            komentar: "Semoga Awet",
          },
          {
            nama: "Ando0",
            tanggal: "23-10-2024",
            jam: "10.20",
            variasi: "Orange",
            komentar: "Lama banget nyampainya min",
          },
        ],
      },
    };
  },
};
</script>
