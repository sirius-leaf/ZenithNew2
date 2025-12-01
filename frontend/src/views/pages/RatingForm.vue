<template>
  <main class="px-4 py-6 md:px-6 lg:px-8 max-w-4xl mx-auto w-full">
    <button
      @click="$router.back()"
      class="inline-block mb-4 text-gray-500 hover:text-pink-600 transition duration-200 text-sm font-medium"
    >
      &larr; Kembali
    </button>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 overflow-hidden flex items-center">
          <img :src="contextImage" alt="context" class="w-full h-full object-cover" />
        </div>
        <div>
          <h1 class="text-lg font-bold text-gray-900">{{ contextTitle }}</h1>
          <p class="text-sm text-gray-500">Tulis penilaian Anda untuk {{ contextType }}</p>
        </div>
      </div>

      <form @submit.prevent="submitReview" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
          <div class="flex items-center gap-2">
            <button
              v-for="i in 5"
              :key="i"
              type="button"
              class="p-2 rounded-md transition-colors"
              :class="{
                'bg-yellow-50 text-yellow-400': rating >= i,
                'text-gray-400 bg-transparent hover:bg-gray-50': rating < i
              }"
              @click="rating = i"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.5L14.8 9.2L21.5 12L14.8 14.8L12 21.5L9.2 14.8L2.5 12L9.2 9.2L12 2.5Z" />
              </svg>
            </button>
          </div>
        </div>

        <div>
          <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Komentar (opsional)</label>
          <textarea id="comment" v-model="comment" class="w-full border border-gray-200 rounded-md p-3 text-sm text-gray-700 min-h-[120px]" placeholder="Tulis pengalaman Anda..."></textarea>
        </div>

        <div v-if="type === 'product'" class="">
          <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Varian (opsional)</label>
          <select v-model="selectedVariant" class="w-full border border-gray-200 rounded-md p-2 text-sm">
            <option value="">- Tidak memilih varian -</option>
            <option v-for="v in variants" :key="v.id_varian" :value="v.id_varian">{{ v.nama_varian }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Gambar (opsional)</label>
          <div class="flex gap-3 items-center">
            <input type="file" @change="handleFileUpload" class="hidden" ref="fileInput" />
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600" @click="$refs.fileInput.click()">Unggah Gambar</button>
            <div v-if="previewImage" class="w-16 h-16 rounded-md overflow-hidden border border-gray-200">
              <img :src="previewImage" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
          <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50" @click="$router.back()">Batal</button>
          <button type="submit" :disabled="submitting || rating === 0 || !canSubmit" :class="['px-4 py-2 rounded-lg text-sm font-bold', submitting || rating === 0 || !canSubmit ? 'bg-gray-300 text-gray-600 cursor-not-allowed' : 'bg-pink-600 text-white hover:bg-pink-700']">Kirim Penilaian</button>
        </div>
        <div v-if="!canSubmit" class="mt-2 text-sm text-gray-500">
          Untuk membuat penilaian produk, Anda harus menuliskannya dari halaman detail pesanan terkait. <a class="text-pink-600 hover:underline" href="/riwayat">Buka Riwayat Pesanan</a>
        </div>
      </form>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const type = route.params.type || 'product';
const id = route.params.id;

const rating = ref(0);
const comment = ref('');
const selectedVariant = ref('');
const variants = ref([]);
const previewImage = ref(null);
const file = ref(null);
const submitting = ref(false);
const contextTitle = ref('Loading...');
const contextImage = ref('https://via.placeholder.com/150?text=No+Image');
const contextType = computed(() => (type === 'toko' ? 'Toko' : 'Produk'));

onMounted(async () => {
  if (type === 'product') {
    try {
      const res = await axios.get(`/products/${id}`);
      const p = res.data.data;
      contextTitle.value = p.nama_produk || 'Produk';
      if (p.variant) variants.value = p.variant;
      if (p.variant && p.variant.length > 0) {
        const firstVariant = p.variant[0];
        contextImage.value = firstVariant.gambar_varian && firstVariant.gambar_varian.startsWith('http')
          ? firstVariant.gambar_varian
          : `http://127.0.0.1:8000/storage/${firstVariant.gambar_varian}`;
      } else if (p.gambar_produk) {
        contextImage.value = p.gambar_produk.startsWith('http') ? p.gambar_produk : `http://127.0.0.1:8000/storage/${p.gambar_produk}`;
      }
    } catch (err) {
      console.error(err);
    }
  } else if (type === 'toko') {
    try {
      const res = await axios.get(`/toko/${id}`);
      if (res.data.data && res.data.data.length > 0) {
        const toko = res.data.data[0];
        contextTitle.value = toko.user?.store_name || toko.toko_name || 'Toko';
        if (toko.user?.store_photo) {
          contextImage.value = toko.user.store_photo.startsWith('http') ? toko.user.store_photo : `http://127.0.0.1:8000/storage/${toko.user.store_photo}`;
        }
      }
    } catch (err) {
      console.error(err);
    }
  }
});

const handleFileUpload = (e) => {
  const f = e.target.files[0];
  if (!f) return;
  file.value = f;
  previewImage.value = URL.createObjectURL(f);
};

const orderIdParam = computed(() => route.query.orderId || route.query.id_pesanan);

const canSubmit = computed(() => {
  if (type === 'product') return !!orderIdParam.value;
  // we block toko submissions
  if (type === 'toko') return false;
  return false;
});

const submitReview = async () => {
  if (rating.value === 0) {
    alert('Silakan pilih rating antara 1-5.');
    return;
  }
  if (type === 'toko') {
    alert('Maaf, memberikan rating untuk toko langsung saat ini tidak didukung. Silakan beri rating pada produk yang Anda beli dari toko ini.');
    return;
  }
  submitting.value = true;
  try {
    const orderId = route.query.orderId || route.query.id_pesanan;
    if (type === 'product' && !orderId) {
      alert('Penilaian produk hanya dapat dikirim jika Anda telah membeli produk tersebut (dari halaman detail pesanan).');
      return;
    }
    const form = new FormData();
    form.append('rating', rating.value);
    form.append('komentar', comment.value || '');
    if (selectedVariant.value) form.append('id_variant', selectedVariant.value);
    if (file.value) form.append('image', file.value);
    if (type === 'product') {
      form.append('id_produk', id);
      const orderId = route.query.orderId || route.query.id_pesanan;
      if (orderId) form.append('id_pesanan', orderId);
      if (file.value) {
        form.delete('image');
        form.append('images[0]', file.value);
      }

      const token = localStorage.getItem('authToken');
      await axios.post('/reviews', form, {
        headers: { 'Content-Type': 'multipart/form-data', Authorization: token ? `Bearer ${token}` : '' },
      });
    } else {
      alert('Rating untuk toko tidak tersedia.');
    }
    alert('Terima kasih! Penilaian berhasil dikirim.');
    router.push('/');
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || (err.response?.data?.errors ? Object.values(err.response?.data?.errors).flat().join('\n') : err.message);
    if (err.response?.status === 401) {
      alert('Anda harus login terlebih dahulu untuk memberi penilaian.');
    } else {
      alert('Gagal mengirim penilaian: ' + msg);
    }
    alert('Gagal mengirim penilaian: ' + msg);
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
</style>
