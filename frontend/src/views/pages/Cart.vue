<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in max-w-6xl mx-auto">
      <!-- Header -->
      <h1 class="text-2xl font-bold text-pink-600 mb-6">KERANJANG</h1>

      <!-- Error Handling -->
      <div v-if="apiError" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
        <strong>Error:</strong> {{ apiError }}
        <p class="mt-1">Harap hapus atau kurangi item yang bermasalah.</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-10 text-gray-500">Memuat detail keranjang...</div>

      <!-- Keranjang Kosong -->
      <div v-else-if="!cartSummary || cartSummary.length === 0" class="text-center py-10">
        <p class="text-lg text-gray-600">Keranjang Anda masih kosong.</p>
        <router-link to="/product" class="mt-4 inline-block text-blue-600 hover:underline font-medium">
          Lihat Semua Produk
        </router-link>
      </div>

      <!-- Layout 2 Kolom (Hanya muncul jika ada item) -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
              v-for="item in cartSummary"
              :key="item.variant.id_varian"
              class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-pink-50 transition-colors"
            >
              <!-- Checkbox -->
              <input
                v-model="checkedItems[item.variant.id_varian]"
                type="checkbox"
                class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-2 focus:ring-pink-500 mr-4 flex-shrink-0"
              />

              <!-- Gambar (fallback jika tidak ada) -->
              <img
                :src="item.variant.product.gambar_utama || 'https://placehold.co/144x161?text=Produk'"
                :alt="item.variant.product.nama_produk"
                class="w-16 h-16 object-cover rounded-md mr-4 flex-shrink-0"
              />

              <!-- Detail -->
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-blue-900 text-sm">
                  {{ item.variant.product.toko?.toko_name || 'Toko Tidak Tersedia' }}
                </h3>
                <p class="text-gray-800 font-medium mt-1 text-sm line-clamp-2">
                  {{ item.variant.product.nama_produk }}
                </p>
                <p class="text-gray-500 text-xs mt-1">{{ item.variant.nama_varian }}</p>
              </div>

              <!-- Kolom Kanan: Harga, Quantity & Hapus -->
              <div class="flex items-center gap-3 ml-4 flex-shrink-0">
                <!-- Harga -->
                <p class="font-bold text-gray-800 text-sm">
                  {{ formatCurrency(item.variant.harga) }}
                </p>

                <!-- Quantity Controls -->
                <div class="flex items-center gap-1">
                  <button
                    @click="updateQuantity(item.variant.id_varian, -1)"
                    :disabled="item.kuantitas <= 1"
                    class="w-7 h-7 flex items-center justify-center bg-blue-900 text-white text-xs rounded hover:bg-blue-800 disabled:bg-gray-400 transition-colors"
                  >
                    -
                  </button>
                  <span class="w-8 text-center text-xs font-medium text-gray-800">
                    {{ item.kuantitas }}
                  </span>
                  <button
                    @click="updateQuantity(item.variant.id_varian, 1)"
                    class="w-7 h-7 flex items-center justify-center bg-blue-900 text-white text-xs rounded hover:bg-blue-800 transition-colors"
                  >
                    +
                  </button>
                </div>

                <!-- Tombol Hapus -->
                <button
                  @click="removeItem(item.variant.id_varian)"
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

          <!-- Item dipilih -->
          <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
            <div
              v-for="item in filteredCartForCheckout"
              :key="item.variant.id_varian"
              class="border-b border-pink-200 pb-3"
            >
              <div class="flex justify-between text-sm">
                <div class="font-medium text-gray-800">{{ item.variant.product.nama_produk }}</div>
                <div class="text-gray-600">{{ formatCurrency(item.variant.harga) }}</div>
              </div>
              <div class="text-xs text-gray-600 mt-1">Jumlah: {{ item.kuantitas }}</div>
              <div class="text-sm font-bold text-blue-900 mt-1">
                {{ formatCurrency(item.subtotal) }}
              </div>
            </div>
          </div>

          <!-- Total -->
          <div class="border-t border-pink-300 pt-4 mt-4">
            <div class="flex justify-between mb-2 text-sm font-medium text-gray-700">
              <span>Total ({{ totalCheckedItems }} item)</span>
              <span>{{ formatCurrency(filteredTotalPrice) }}</span>
            </div>

            <!-- Tombol Buat Pesanan -->
            <button
              @click="goToCheckout"
              :disabled="filteredCartForCheckout.length === 0 || loading || apiError"
              class="w-full py-2.5 bg-blue-900 text-white font-semibold rounded-lg hover:bg-blue-800 transition-colors text-sm disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              Buat Pesanan ({{ totalCheckedItems }})
            </button>

            <router-link
              to="/product"
              class="block mt-3 text-center text-sm text-blue-600 hover:underline"
            >
              Lanjutkan Belanja
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cartStore'
import axios from 'axios'

const router = useRouter()
const { cartItems, removeCartItem, updateCartItem } = useCartStore()

// State
const loading = ref(true)
const cartSummary = ref([]) // Data dari API
const apiError = ref(null)
const checkedItems = ref({})

// Computed
const filteredCartForCheckout = computed(() => {
  return cartSummary.value.filter(item => checkedItems.value[item.variant.id_varian])
})

const filteredTotalPrice = computed(() => {
  return filteredCartForCheckout.value.reduce((sum, item) => sum + item.subtotal, 0)
})

const totalCheckedItems = computed(() => {
  return filteredCartForCheckout.value.reduce((sum, item) => sum + item.kuantitas, 0)
})

const selectAll = computed({
  get: () => cartSummary.value.length > 0 && cartSummary.value.every(item => checkedItems.value[item.variant.id_varian]),
  set: (value) => {
    cartSummary.value.forEach(item => {
      checkedItems.value[item.variant.id_varian] = value
    })
  }
})

// Fungsi Helper
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

// API: Preview Keranjang
const fetchCartPreview = async () => {
  apiError.value = null
  loading.value = true

  const cartDataForApi = cartItems.value.map(item => ({
    id_varian: item.id_varian,
    kuantitas: item.kuantitas
  }))

  if (cartDataForApi.length === 0) {
    cartSummary.value = []
    loading.value = false
    return
  }

  try {
    const token = localStorage.getItem('authToken')
    const response = await axios.post(
      'http://127.0.0.1:8000/api/order/preview',
      { cartItems: cartDataForApi },
      { headers: { Authorization: `Bearer ${token}` } }
    )

    cartSummary.value = response.data.cartItems

    // Inisialisasi checkbox (default: semua dicentang)
    const newChecked = {}
    response.data.cartItems.forEach(item => {
      newChecked[item.variant.id_varian] = true
    })
    checkedItems.value = newChecked
  } catch (error) {
    console.error('Error preview cart:', error)
    apiError.value = error.response?.data?.message || 'Gagal memuat data keranjang.'
  } finally {
    loading.value = false
  }
}

// Aksi
const updateQuantity = (id_varian, delta) => {
  const item = cartItems.value.find(i => i.id_varian === id_varian)
  if (!item) return

  const newQty = item.kuantitas + delta
  if (newQty < 1) return

  updateCartItem(id_varian, delta) // Pinia store handle delta
  fetchCartPreview()
}

const removeItem = (id_varian) => {
  if (confirm('Yakin ingin menghapus item ini?')) {
    removeCartItem(id_varian)
    delete checkedItems.value[id_varian]
    fetchCartPreview()
  }
}

const goToCheckout = () => {
  if (filteredCartForCheckout.value.length === 0) {
    alert('Pilih minimal 1 produk untuk checkout')
    return
  }

  // Simpan item yang dipilih ke localStorage untuk halaman checkout
  const selectedItems = filteredCartForCheckout.value.map(item => ({
    id_varian: item.variant.id_varian,
    kuantitas: item.kuantitas
  }))

  localStorage.setItem('checkout_selection', JSON.stringify(selectedItems))
  router.push({ name: 'checkout' })
}

// Lifecycle
onMounted(() => {
  fetchCartPreview()
})
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