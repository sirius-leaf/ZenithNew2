<template>
  <div
    v-if="open"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[100] p-4 transition-all duration-300"
  >
    <div class="bg-white w-full max-w-6xl rounded-2xl shadow-2xl flex flex-col h-[90vh] animate-scale-in">
      <!-- Header -->
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white rounded-t-2xl sticky top-0 z-10">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Pilih {{ label }}</h2>
          <p class="text-sm text-gray-500 mt-1">Pilih komponen terbaik untuk rakitanmu</p>
        </div>
        <button
          class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-colors"
          @click="$emit('close')"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Search -->
      <div class="p-4 bg-gray-50 border-b border-gray-100">
        <div class="relative max-w-2xl mx-auto">
          <input
            v-model="search"
            type="text"
            placeholder="Cari nama produk atau merek..."
            class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none transition-all shadow-sm"
          />
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- List Produk (Grid) -->
      <div class="overflow-y-auto p-6 bg-gray-50 flex-1 custom-scrollbar">
        <div v-if="filteredVariants.length === 0" class="text-center py-12">
          <div class="bg-white p-4 rounded-full inline-block mb-3 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-gray-500 font-medium">Tidak ada produk ditemukan</p>
        </div>

        <div
          v-else
          class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
        >
          <div
            v-for="item in filteredVariants"
            :key="item.variant.id_varian"
            class="bg-white rounded-2xl p-3 hover:shadow-xl transition-all duration-300 cursor-pointer group flex flex-col h-full border border-gray-200 hover:border-pink-100 relative"
            @click="chooseProduct(item.variant)"
          >
            <!-- Image Container -->
            <div class="relative overflow-hidden rounded-xl mb-3 aspect-square bg-gray-50">
              <img
                :src="`http://127.0.0.1:8000/storage/${item.variant.gambar_varian}`"
                :alt="item.product.nama_produk"
                class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                @error="(e) => (e.target.src = 'https://via.placeholder.com/200/FFFFFF/000000?text=No+Image')"
              />
              <!-- Overlay -->
              <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              
              <!-- Select Button Overlay -->
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                <span class="bg-pink-600 text-white px-4 py-2 rounded-lg font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">
                  Pilih
                </span>
              </div>
            </div>

            <!-- Content -->
            <div class="flex flex-col flex-grow px-1">
              <!-- Product Name -->
              <h4 class="font-bold text-gray-900 text-sm mb-1 line-clamp-2 leading-snug group-hover:text-pink-600 transition-colors">
                {{ item.product.nama_produk }}
              </h4>
              
              <!-- Variant Name -->
              <p class="text-xs text-gray-500 mb-2 line-clamp-1">
                {{ item.variant.nama_varian }}
              </p>

              <!-- Price -->
              <div class="mt-auto pt-2 border-t border-gray-50 flex justify-between items-center">
                <p class="text-pink-600 font-bold text-base">
                  Rp {{ Number(item.variant.harga).toLocaleString('id-ID') }}
                </p>
                <div class="text-xs text-gray-400">
                  Stok: {{ item.variant.stok }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  open: Boolean,
  label: String,
  products: Array,
  category: String, // New prop for category filtering
});

const emit = defineEmits(["close", "select"]);

const search = ref("");

// Flatten products to variants for grid display
const filteredVariants = computed(() => {
  const query = search.value.toLowerCase();
  let variants = [];

  props.products.forEach((p) => {
    // Filter by category if provided
    if (props.category) {
       // Check if product belongs to the category
       // Assuming p.category_detail is array of { category: { nama_kategori: '...' } }
       const hasCategory = p.category_detail?.some(cd => 
         cd.category?.nama_kategori?.toLowerCase() === props.category.toLowerCase()
       );
       
       if (!hasCategory) return;
    }

    const cocokNamaProduk = p.nama_produk.toLowerCase().includes(query);
    const cocokMerek = p.merek ? p.merek.toLowerCase().includes(query) : false;

    p.variant.forEach((v) => {
      const cocokNamaVarian = v.nama_varian.toLowerCase().includes(query);
      
      if (cocokNamaProduk || cocokMerek || cocokNamaVarian) {
        variants.push({
          product: p,
          variant: v
        });
      }
    });
  });

  return variants;
});

// Ketika user memilih produk
function chooseProduct(variant) {
  emit("select", variant.id_varian);
  emit("close");
}
</script>

<style scoped>
.animate-scale-in {
  animation: scaleIn 0.2s ease-out;
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>


