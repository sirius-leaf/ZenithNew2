<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = ref({
    toko_name: '',
    deskripsi: ''
});

const loading = ref(false);
const errorMessage = ref(null);
const hasShop = ref(false); // State untuk cek apakah sudah punya toko

// Ambil Token
const token = localStorage.getItem('authToken');
const authConfig = { headers: { Authorization: `Bearer ${token}` } };

// 1. Cek Status Toko saat halaman dimuat
const checkShopStatus = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/manage/toko', authConfig);
        
        if (response.data.status === 'exists') {
            hasShop.value = true;
            // Opsional: Redirect otomatis jika sudah punya toko
            // router.push('/manage/my-shop'); 
        }
    } catch (error) {
        console.error("Gagal mengecek status toko", error);
    }
};

// 2. Submit Form Toko Baru
const createToko = async () => {
    loading.value = true;
    errorMessage.value = null;

    try {
        await axios.post('http://127.0.0.1:8000/api/manage/toko', form.value, authConfig);
        
        alert('Toko berhasil dibuat!');
        // Redirect ke dashboard atau halaman detail toko
        router.push('/dashboard'); 

    } catch (error) {
        if (error.response) {
            // Tangkap pesan error dari controller (403, 409, atau 422 validation)
            errorMessage.value = error.response.data.message || 'Terjadi kesalahan validasi.';
        } else {
            errorMessage.value = 'Gagal terhubung ke server.';
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    checkShopStatus();
});
</script>

<template>
    <div class="container mx-auto px-4 py-10 flex justify-center">
        <div class="w-full max-w-lg bg-white shadow-lg rounded-xl p-8 border border-gray-100">
            
            <div v-if="hasShop" class="text-center py-6">
                <div class="text-green-500 text-5xl mb-4">✓</div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Anda Sudah Memiliki Toko</h2>
                <p class="text-gray-600 mb-6">Anda tidak dapat membuat toko baru.</p>
                <button @click="router.push('/dashboard')" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Kembali ke Dashboard
                </button>
            </div>

            <div v-else>
                <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">Buka Toko Baru</h1>
                <p class="text-gray-500 text-center mb-6">Isi detail di bawah untuk mulai berjualan</p>

                <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded-lg">
                    {{ errorMessage }}
                </div>

                <form @submit.prevent="createToko" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
                        <input 
                            v-model="form.toko_name"
                            type="text" 
                            required
                            placeholder="Contoh: Toko Komputer Jaya"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
                        <textarea 
                            v-model="form.deskripsi"
                            required
                            rows="3"
                            placeholder="Jelaskan apa yang toko Anda jual..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                        ></textarea>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="loading"
                        class="w-full bg-pink-600 hover:bg-pink-700 text-white font-semibold py-3 rounded-lg transition duration-300 flex justify-center items-center disabled:opacity-70"
                    >
                        <span v-if="loading">Memproses...</span>
                        <span v-else>Buat Toko Sekarang</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</template>