<script setup>
import { ref, onMounted, computed } from 'vue';
import { useCartStore } from '@/stores/cartStore'; 
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const { clearCart } = useCartStore(); // Hanya butuh fungsi clearCart

const loading = ref(true);
const loadingCheckout = ref(false);
const apiError = ref(null);
const cartSummary = ref([]); // Hasil dari API Preview
const totalPrice = ref(0);
const successMessage = ref(null);

// Form & User State
const userDetails = ref({
    name: '',
    email: '',
});
const form = ref({
    alamat_pengiriman: '',
});

const totalItems = computed(() => cartSummary.value.length);

// 1. Ambil data ringkasan & user
const fetchCheckoutData = async () => {
    loading.value = true;
    apiError.value = null;
    
    // --- 1.1 Ambil data item yang DIPILIH dari Cart.vue ---
    const selectedItemsJson = localStorage.getItem('checkout_selection');
    if (!selectedItemsJson) {
        apiError.value = "Keranjang kosong atau belum memilih item. Silakan kembali ke keranjang.";
        loading.value = false;
        return;
    }
    const cartDataForApi = JSON.parse(selectedItemsJson);

    if (cartDataForApi.length === 0) {
        apiError.value = "Tidak ada item yang dipilih untuk checkout.";
        loading.value = false;
        return;
    }

    // --- 1.2 Ambil data user (untuk display) ---
    const token = localStorage.getItem('authToken');
    try {
        const userRes = await axios.get('http://127.0.0.1:8000/api/user', {
            headers: { Authorization: `Bearer ${token}` }
        });
        userDetails.value.name = userRes.data.name;
        userDetails.value.email = userRes.data.email;
    } catch (e) {
        router.push({ name: 'login' });
        return;
    }

    // --- 1.3 Ambil preview cart untuk validasi akhir & ringkasan ---
    try {
        const response = await axios.post('http://127.0.0.1:8000/api/order/preview', {
            cartItems: cartDataForApi
        }, { headers: { Authorization: `Bearer ${token}` } });

        cartSummary.value = response.data.cartItems;
        totalPrice.value = response.data.totalPrice;

    } catch (error) {
        apiError.value = error.response?.data?.message || 'Gagal memuat ringkasan keranjang. Cek stok Anda.';
    } finally {
        loading.value = false;
    }
};

// 2. Proses Checkout
const finalizeCheckout = async () => {
    loadingCheckout.value = true;
    apiError.value = null;

    if (form.value.alamat_pengiriman.trim() === '') {
        apiError.value = "Alamat pengiriman wajib diisi.";
        loadingCheckout.value = false;
        return;
    }
    
    // Ambil data dari LocalStorage selection
    const selectedItemsJson = localStorage.getItem('checkout_selection');
    const cartItemsPayload = JSON.parse(selectedItemsJson);

    const payload = {
        alamat_pengiriman: form.value.alamat_pengiriman,
        cartItems: cartItemsPayload // Data yang sudah difilter
    };
    
    const token = localStorage.getItem('authToken');

    try {
        // Panggil API store yang sekarang mengembalikan order_ids
        const response = await axios.post('http://127.0.0.1:8000/api/order/store', payload, {
            headers: { Authorization: `Bearer ${token}` }
        });

        // Sukses!
        const firstOrderId = response.data.order_ids[0]; // Ambil ID pesanan pertama
        
        clearCart(); // Kosongkan keranjang (global store)
        localStorage.removeItem('checkout_selection'); // Hapus seleksi lokal
        
        // Redirect ke halaman sukses dengan ID pesanan yang valid
        router.push({ name: 'checkout.success', query: { id: firstOrderId } });

    } catch (error) {
        console.error("Checkout Gagal:", error);
        apiError.value = error.response?.data?.message || 'Gagal memproses pesanan. Coba lagi.';
    } finally {
        loadingCheckout.value = false;
    }
};


onMounted(() => {
    fetchCheckoutData();
});
</script>

<template>
    <div class="container mx-auto p-4 md:p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Proses Checkout</h1>

        <div v-if="loading" class="text-center py-10 text-gray-500">Memuat data checkout...</div>

        <div v-else-if="apiError && totalItems === 0" class="p-6 bg-red-50 border border-red-300 rounded-lg">
            <p class="text-red-700 font-medium">{{ apiError }}</p>
            <router-link :to="{ name: 'product-list' }" class="mt-2 inline-block text-blue-600 hover:underline">Kembali ke Produk</router-link>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow border border-gray-100">
                <h2 class="text-xl font-bold mb-4 border-b pb-3">Detail Pengiriman</h2>
                
                <form @submit.prevent="finalizeCheckout" class="space-y-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                        <input type="text" :value="userDetails.name" readonly class="w-full px-3 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-700" />
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" :value="userDetails.email" readonly class="w-full px-3 py-2 border border-gray-300 bg-gray-50 rounded-lg text-gray-700" />
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat Pengiriman Lengkap <span class="text-red-500">*</span></label>
                        <textarea v-model="form.alamat_pengiriman" id="alamat" rows="4" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"></textarea>
                    </div>

                    <div v-if="apiError" class="p-3 bg-red-100 text-red-700 rounded-lg">
                        {{ apiError }}
                    </div>
                    
                    <hr class="mt-6 border-gray-200">
                    <p class="text-md font-semibold text-gray-800">Metode Pembayaran: Transfer Bank (Default)</p>
                    
                    <button type="submit" :disabled="loadingCheckout || apiError"
                        class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-lg transition disabled:bg-gray-400">
                        {{ loadingCheckout ? 'Membuat Pesanan...' : 'Buat Pesanan Sekarang' }}
                    </button>
                </form>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-gray-50 p-6 rounded-lg shadow-inner border border-gray-200 sticky top-4">
                    <h2 class="text-xl font-bold mb-4 border-b pb-3">Ringkasan Pesanan ({{ totalItems }} Item)</h2>
                    
                    <div v-for="item in cartSummary" :key="item.variant.id_varian" class="flex justify-between text-sm py-2 border-b border-gray-100">
                        <span class="text-gray-700">{{ item.variant.product.nama_produk }} (x{{ item.kuantitas }})</span>
                        <span class="font-medium">Rp {{(item.variant.harga * item.kuantitas).toLocaleString('id-ID')}}</span>
                    </div>

                    <div class="flex justify-between text-xl font-bold mt-4 pt-3 border-t border-gray-300">
                        <span>Total Bayar</span>
                        <span class="text-pink-600">Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
