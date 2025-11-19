<script setup>
import { ref, onMounted, computed } from 'vue'; // <-- Tambahkan computed
import axios from 'axios';
import { useRoute } from 'vue-router';
import { useCartStore } from '@/stores/cartStore';

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
    if (product.value && product.value.variant && product.value.variant.length > 0) {
        const imagePath = product.value.variant[0].gambar_varian;
        // Kita konstruksi URL storage lengkap:
        if (imagePath) {
           return `http://127.0.0.1:8000/storage/${imagePath}`;
        }
    }
    // Default gambar jika tidak ada varian atau gambar
    return 'https://via.placeholder.com/400x300/CCCCCC?text=No+Image'; 
});

const initializeQuantities = (variants) => {
  const q = {};
  variants.forEach(variant => {
    q[variant.id_varian] = 1;
  });
  quantities.value = q;
};

const handleAddToCart = (variant) => {
    const kuantitas = quantities.value[variant.id_varian];
    
    if (kuantitas <= 0 || kuantitas > variant.stok) {
        alert('Kuantitas tidak valid atau melebihi stok.');
        return;
    }
    
    updateCartItem(variant.id_varian, kuantitas, { 
        nama_varian: variant.nama_varian,
        harga: variant.harga,
        stok: variant.stok,
        product_name: product.value.nama_produk,
    });
    
    successMessage.value = `Berhasil menambahkan ${kuantitas}x ${variant.nama_variant} ke keranjang!`;
    setTimeout(() => successMessage.value = null, 3000);
};

const fetchProductDetail = async () => {
    try {
        const productId = route.params.id;
        const response = await axios.get(`http://127.0.0.1:8000/api/products/${productId}`);
        
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
  <div class="container mx-auto p-4">
    <router-link to="/" class="inline-block mb-4 text-indigo-600 hover:text-indigo-800 transition duration-200">
      &larr; Kembali ke Daftar Produk
    </router-link>

    <div v-if="loading" class="text-center text-gray-600 py-12">
      Memuat detail produk...
    </div>

    <div v-else-if="error" class="text-center text-red-500 py-12">
      Terjadi kesalahan saat memuat detail produk: {{ error.message }}
    </div>

    <div v-else-if="product" class="bg-white rounded-lg shadow-lg p-8 grid grid-cols-1 md:grid-cols-12 gap-8">
      
      <div class="md:col-span-4">
          <img :src="mainImage" :alt="product.nama_produk" class="w-full rounded-lg shadow-md object-cover h-auto">
          <p class="text-sm text-gray-500 mt-2 text-center">Gambar dari varian pertama</p>
      </div>


      <div class="md:col-span-8">
          <h1 class="text-4xl font-bold mb-2 text-gray-900">{{ product.nama_produk }}</h1>
          <p class="text-muted mb-4">Merek: {{ product.merek }}</p>
          <p class="text-lg text-gray-700 mb-6">{{ product.deskripsi }}</p>

          <hr class="my-6 border-gray-300">
          <h2 class="text-2xl font-semibold mb-4 text-gray-800">Pilih Varian:</h2>
          
          <div v-if="successMessage" class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ successMessage }}
          </div>

          <div v-if="product.variant && product.variant.length > 0" class="space-y-4">
            <div v-for="variant in product.variant" :key="variant.id_varian"
                  class="bg-gray-50 p-4 rounded-md shadow-sm border border-gray-200 flex justify-between items-center">
              
              <div>
                <h3 class="text-xl font-medium mb-1 text-gray-800">{{ variant.nama_variant }}</h3>
                <p class="text-gray-600 mb-1">
                  Harga: <span class="font-bold text-green-700">Rp {{ variant.harga.toLocaleString('id-ID') }}</span>
                </p>
                <p class="text-sm text-red-500">Stok: {{ variant.stok }}</p>
              </div>

              <div class="flex items-center space-x-3">
                <div v-if="variant.stok > 0" class="flex items-center">
                  <input 
                    type="number" 
                    v-model.number="quantities[variant.id_varian]" 
                    :min="1" 
                    :max="variant.stok" 
                    class="w-20 text-center border border-gray-300 rounded-l-md py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                  >
                  <button 
                    @click="handleAddToCart(variant)"
                    class="bg-pink-600 text-white px-4 py-2 rounded-r-md hover:bg-pink-700 transition duration-150 text-sm font-medium"
                  >
                    + Keranjang
                  </button>
                </div>
                <div v-else>
                  <span class="text-sm font-semibold text-red-600 bg-red-100 px-3 py-1 rounded-full">Stok Habis</span>
                </div>
              </div>

            </div>
          </div>
          <div v-else>
            <p class="text-gray-600">Belum ada varian untuk produk ini.</p>
          </div>
          
      </div>
    </div>
    
    <div v-else class="text-center text-gray-600 py-12">
      Produk tidak ditemukan.
    </div>
  </div>
</template>