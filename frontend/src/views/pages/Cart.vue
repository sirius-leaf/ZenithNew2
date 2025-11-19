<!-- src/components/user/Keranjang.vue -->
<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in max-w-6xl mx-auto">
      <!-- Header -->
      <h1 class="text-2xl font-bold text-pink-600 mb-6">KERANJANG</h1>

      <!-- Layout 2 Kolom -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Daftar Produk -->
        <div class="lg:col-span-2">
          <!-- Pilih Semua -->
          <div class="flex items-center mb-5">
            <input
              v-model="selectAll"
              type="checkbox"
              class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-2 focus:ring-pink-500"
            />
            <label class="ml-2 text-base font-medium text-gray-800">Pilih Semua</label>
          </div>

          <!-- Daftar Produk -->
          <div class="space-y-4">
            <div
              v-for="(item, index) in cartItems"
              :key="index"
              class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-pink-50 transition-colors"
            >
              <!-- Checkbox -->
              <input
                v-model="item.selected"
                type="checkbox"
                class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-2 focus:ring-pink-500 mr-4 flex-shrink-0"
              />

              <!-- Gambar -->
              <img
                :src="item.image"
                :alt="item.name"
                class="w-16 h-16 object-cover rounded-md mr-4 flex-shrink-0"
              />

              <!-- Detail -->
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-blue-900 text-sm">{{ item.storeName }}</h3>
                <p class="text-gray-800 font-medium mt-1 text-sm">{{ item.name }}</p>
                <p class="text-gray-500 text-xs mt-1">{{ item.color }}</p>
              </div>

              <!-- Kolom Kanan: Harga, Quantity & Hapus (Sejajar) -->
              <div class="flex items-center gap-4 ml-4 flex-shrink-0">
                <!-- Harga -->
                <p class="font-bold text-gray-800 text-sm">{{ formatCurrency(item.price) }}</p>
                
                <!-- Quantity Controls -->
                <div class="flex items-center gap-1">
                  <button
                    @click="decreaseQuantity(index)"
                    class="w-7 h-7 flex items-center justify-center bg-blue-900 text-white text-xs rounded hover:bg-blue-800 transition-colors"
                  >
                    -
                  </button>
                  <span class="w-8 text-center text-xs font-medium text-gray-800">{{ item.quantity }}</span>
                  <button
                    @click="increaseQuantity(index)"
                    class="w-7 h-7 flex items-center justify-center bg-blue-900 text-white text-xs rounded hover:bg-blue-800 transition-colors"
                  >
                    +
                  </button>
                </div>
                
                <!-- Tombol Hapus -->
                <button
                  @click="removeItem(index)"
                  class="w-7 h-7 flex items-center justify-center text-red-600 hover:text-red-800 transition-colors"
                  title="Hapus item"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Ringkasan Belanja -->
        <div class="bg-pink-50 rounded-lg border border-pink-200 p-5 h-fit lg:sticky lg:top-4">
          <h2 class="text-lg font-bold text-blue-900 mb-4">Ringkasan Order</h2>

          <!-- Item dalam ringkasan -->
          <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
            <div
              v-for="(item, index) in cartItems.filter(i => i.selected)"
              :key="index"
              class="border-b border-pink-200 pb-3"
            >
              <div class="flex justify-between text-sm">
                <div class="font-medium text-gray-800">{{ item.name }}</div>
                <div class="text-gray-600">{{ formatCurrency(item.price) }}</div>
              </div>
              <div class="text-xs text-gray-600 mt-1">Jumlah: {{ item.quantity }}</div>
              <div class="text-sm font-bold text-blue-900 mt-1">
                {{ formatCurrency(item.price * item.quantity) }}
              </div>
            </div>
          </div>

          <!-- Total -->
          <div class="border-t border-pink-300 pt-4 mt-4">
            <div class="flex justify-between mb-2 text-sm font-medium text-gray-700">
              <span>Subtotal</span>
              <span>{{ formatCurrency(subtotal) }}</span>
            </div>
            <div class="flex justify-between mb-4 text-lg font-bold text-blue-900">
              <span>Total</span>
              <span>{{ formatCurrency(totalPrice) }}</span>
            </div>

            <!-- Tombol Buat Pesanan -->
            <button
              @click="checkout"
              :disabled="cartItems.filter(i => i.selected).length === 0"
              class="w-full py-2.5 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition-colors text-sm disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              Buat Pesanan ({{ cartItems.filter(i => i.selected).length }})
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const cartItems = ref([
  {
    id: 1,
    selected: true,
    storeName: 'Samsung Indonesia',
    name: 'Samsung Galaxy S25 Edge 256GB',
    color: '12GB 256GB, Brown',
    price: 7299000,
    quantity: 1,
    image: 'https://placehold.co/144x161?text=Samsung'
  },
  {
    id: 2,
    selected: true,
    storeName: 'Samsung Indonesia',
    name: 'Samsung Galaxy S25 Edge 256GB',
    color: '12GB 256GB, Pink',
    price: 7299000,
    quantity: 1,
    image: 'https://placehold.co/144x161?text=Samsung'
  }
])

const selectAll = ref(false)

watch(selectAll, (newVal) => {
  cartItems.value.forEach(item => item.selected = newVal)
})

const subtotal = computed(() => {
  return cartItems.value
    .filter(item => item.selected)
    .reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const totalPrice = computed(() => subtotal.value)

watch(cartItems, () => {
  selectAll.value = cartItems.value.every(item => item.selected)
}, { deep: true })

const decreaseQuantity = (index) => {
  if (cartItems.value[index].quantity > 1) {
    cartItems.value[index].quantity--
  }
}

const increaseQuantity = (index) => {
  cartItems.value[index].quantity++
}

const removeItem = (index) => {
  cartItems.value.splice(index, 1)
}

const checkout = () => {
  const selectedItems = cartItems.value.filter(item => item.selected)
  if (selectedItems.length === 0) {
    alert('Pilih minimal 1 produk untuk checkout')
    return
  }
  console.log('Checkout:', selectedItems)
  // Lanjutkan ke halaman checkout
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>