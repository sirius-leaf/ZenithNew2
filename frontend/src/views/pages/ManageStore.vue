<template>
  <div class="min-h-screen bg-gray-50 font-ubuntu">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <h1 class="text-2xl font-bold text-gray-800">Kelola Toko Saya</h1>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <!-- Products Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
          <h2 class="text-lg font-semibold text-gray-800">Daftar Produk</h2>
          
          <div class="flex items-center gap-3 w-full sm:w-auto">
            <!-- Search Bar -->
            <div class="relative flex-grow sm:flex-grow-0">
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Cari produk..." 
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#e84797] focus:border-transparent w-full sm:w-64"
              >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Tambah Produk Button -->
            <button 
              @click="openAddModal"
              class="px-4 py-2 bg-[#e84797] text-white rounded-lg hover:bg-[#d03a84] transition shadow-sm flex items-center gap-2 whitespace-nowrap"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Tambah Produk
            </button>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 font-semibold uppercase tracking-wider">
              <tr>
                <th class="px-6 py-3">Produk</th>
                <th class="px-6 py-3">Harga</th>
                <th class="px-6 py-3">Stok</th>
                <th class="px-6 py-3">Terjual</th>
                <th class="px-6 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-if="loading">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">Memuat produk...</td>
              </tr>
              <tr v-else-if="filteredProducts.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada produk.</td>
              </tr>
              <tr 
                v-for="product in filteredProducts" 
                :key="product.id_produk"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                      <img :src="getProductImage(product)" alt="Product" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <div class="font-medium text-gray-900 line-clamp-1">{{ product.nama_produk }}</div>
                      <div class="text-xs text-gray-500">{{ product.merek }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 font-medium">
                  Rp {{ (product.variant?.[0]?.harga ?? 0).toLocaleString('id-ID') }}
                </td>
                <td class="px-6 py-4 font-medium text-gray-900">
                  {{ product.variant?.[0]?.stok ?? 0 }}
                </td>
                <td class="px-6 py-4">
                  {{ product.terjual || 0 }}
                </td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
                  <button 
                    @click="openEditModal(product)"
                    class="text-blue-600 hover:text-blue-800 font-medium mr-3"
                  >
                    Edit
                  </button>
                  <button 
                    @click="deleteProduct(product.id_produk)"
                    class="text-red-600 hover:text-red-800 font-medium"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination (Static for now, or implement if API supports it) -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center" v-if="filteredProducts.length > 0">
            <span class="text-sm text-gray-500">Menampilkan {{ filteredProducts.length }} produk</span>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Edit Produk' : 'Tambah Produk Baru' }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveProduct">
          <div class="space-y-4">
            <!-- Gambar -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
              <div class="flex items-center gap-4">
                <div v-if="form.gambar_preview" class="w-20 h-20 rounded-lg overflow-hidden border border-gray-300">
                  <img :src="form.gambar_preview" class="w-full h-full object-cover">
                </div>
                <input type="file" @change="handleFileChange" accept="image/*" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
              <input v-model="form.nama_produk" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
                <input v-model="form.merek" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select v-model="form.kategori" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition bg-white">
                  <option value="" disabled>Pilih Kategori</option>
                  <option v-for="cat in categories" :key="cat.id_kategori" :value="cat.id_kategori">
                    {{ cat.nama_kategori }}
                  </option>
                </select>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                <input v-model="form.harga" type="number" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                <input v-model="form.stok" type="number" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
              <textarea v-model="form.deskripsi" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition"></textarea>
            </div>
          </div>

          <div class="mt-8 flex gap-3">
            <button type="button" @click="closeModal" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="loadingSubmit"
              class="flex-1 px-4 py-2 bg-[#e84797] text-white font-medium rounded-lg hover:bg-[#d03a84] transition shadow-sm disabled:opacity-70 flex justify-center items-center"
            >
              <span v-if="loadingSubmit" class="mr-2 animate-spin">⌛</span>
              {{ isEditing ? 'Simpan Perubahan' : 'Tambah Produk' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();

// State
const products = ref([]);
const categories = ref([]);
const loading = ref(true);
const searchQuery = ref('');

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const loadingSubmit = ref(false);

// Form State
const form = ref({
  id: null,
  nama_produk: '',
  merek: '',
  deskripsi: '',
  kategori: '', // selected category ID
  nama_varian: 'Standard',
  harga: 0,
  stok: 0,
  gambar_varian: null,
  gambar_preview: null
});

// Fetch Data
const fetchData = async () => {
  loading.value = true;
  try {
    const [productsRes, categoriesRes] = await Promise.all([
      axios.get('/manage/product'),
      axios.get('/categories')
    ]);
    
    products.value = productsRes.data.data;
    categories.value = categoriesRes.data.data;
  } catch (error) {
    console.error('Error fetching data:', error);
    toast.error('Gagal memuat data produk');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

// Computed
const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value;
  const query = searchQuery.value.toLowerCase();
  return products.value.filter(p => 
    p.nama_produk.toLowerCase().includes(query) ||
    p.merek.toLowerCase().includes(query)
  );
});

// Modal Actions
const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    nama_produk: '',
    merek: '',
    deskripsi: '',
    kategori: '',
    nama_varian: 'Standard',
    harga: 0,
    stok: 0,
    gambar_varian: null,
    gambar_preview: null
  };
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditing.value = true;
  
  // Get first variant data
  const variant = product.variant && product.variant.length > 0 ? product.variant[0] : {};
  const category = product.category_detail && product.category_detail.length > 0 ? product.category_detail[0].id_kategori : '';

  form.value = {
    id: product.id_produk,
    nama_produk: product.nama_produk,
    merek: product.merek,
    deskripsi: product.deskripsi,
    kategori: category,
    nama_varian: variant.nama_varian || 'Standard',
    harga: variant.harga || 0,
    stok: variant.stok || 0,
    gambar_varian: null, // Don't prepopulate file input
    gambar_preview: variant.gambar_varian ? `http://127.0.0.1:8000/storage/${variant.gambar_varian}` : null,
    // Store IDs for updating
    variant_id: variant.id_varian,
    category_detail_id: product.category_detail?.[0]?.id
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.value.gambar_varian = file;
    form.value.gambar_preview = URL.createObjectURL(file);
  }
};

const saveProduct = async () => {
  if (!form.value.nama_produk || !form.value.kategori || !form.value.harga) {
    toast.warning('Mohon lengkapi data wajib (Nama, Kategori, Harga)');
    return;
  }

  loadingSubmit.value = true;
  try {
    const formData = new FormData();
    formData.append('nama_produk', form.value.nama_produk);
    formData.append('merek', form.value.merek);
    formData.append('deskripsi', form.value.deskripsi || '');
    
    // Handle Category (API expects array)
    formData.append('kategori[]', form.value.kategori);
    
    // Handle Variant (API expects array)
    formData.append('varian[0][nama_varian]', form.value.nama_varian);
    formData.append('varian[0][harga]', form.value.harga);
    formData.append('varian[0][stok]', form.value.stok);
    
    if (form.value.gambar_varian) {
      formData.append('varian[0][gambar_varian]', form.value.gambar_varian);
    }

    if (isEditing.value) {
      // For update, we need to handle structure slightly differently or use _method PUT
      // The API update method expects 'detail' for categories and 'varian' with ids
      
      // Let's re-construct for update
      const updateData = new FormData();
      updateData.append('_method', 'PUT'); // Method spoofing for Laravel
      updateData.append('nama_produk', form.value.nama_produk);
      updateData.append('merek', form.value.merek);
      updateData.append('deskripsi', form.value.deskripsi || '');
      
      // Category update
      if (form.value.category_detail_id) {
          updateData.append('detail[0][id]', form.value.category_detail_id);
          updateData.append('detail[0][kategori]', form.value.kategori);
      } else {
          // New category link if somehow missing
          updateData.append('detail[0][kategori]', form.value.kategori);
      }

      // Variant update
      if (form.value.variant_id) {
          updateData.append('varian[0][id_varian]', form.value.variant_id);
      }
      updateData.append('varian[0][nama_varian]', form.value.nama_varian);
      updateData.append('varian[0][harga]', form.value.harga);
      updateData.append('varian[0][stok]', form.value.stok);
      
      if (form.value.gambar_varian) {
        updateData.append('varian[0][gambar_varian]', form.value.gambar_varian);
      }

      await axios.post(`/manage/product/${form.value.id}`, updateData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Produk berhasil diperbarui');
    } else {
      await axios.post('/manage/product', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Produk berhasil ditambahkan');
    }

    closeModal();
    fetchData(); // Refresh list
  } catch (error) {
    console.error('Error saving product:', error);
    toast.error(error.response?.data?.message || 'Gagal menyimpan produk');
  } finally {
    loadingSubmit.value = false;
  }
};

const deleteProduct = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return;
  
  try {
    await axios.delete(`/manage/product/${id}`);
    toast.success('Produk berhasil dihapus');
    fetchData();
  } catch (error) {
    console.error('Error deleting product:', error);
    toast.error('Gagal menghapus produk');
  }
};

// Helper for image
const getProductImage = (product) => {
  if (product.variant && product.variant.length > 0 && product.variant[0].gambar_varian) {
    return `http://127.0.0.1:8000/storage/${product.variant[0].gambar_varian}`;
  }
  return 'https://via.placeholder.com/50';
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap");

.font-ubuntu {
  font-family: "Ubuntu", sans-serif;
}
</style>
