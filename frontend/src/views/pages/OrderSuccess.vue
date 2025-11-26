<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const orderId = ref(route.query.id); 
const orderData = ref(null);
const loading = ref(true);
const processingPayment = ref(false);
const error = ref(null);
const simulationMessage = ref("Pesanan menunggu pembayaran.");

// Fungsi mengambil data pesanan
const fetchOrderDetails = async () => {
    loading.value = true;
    try {
        const token = localStorage.getItem('authToken');
        if (!token || !orderId.value) {
            router.push({ name: 'home' });
            return;
        }

        const response = await axios.get(
            `http://127.0.0.1:8000/api/orders/${orderId.value}`,
            { headers: { Authorization: `Bearer ${token}` }}
        );

        orderData.value = response.data.data;
        simulationMessage.value =
            orderData.value.status === 'paid'
                ? "Pembayaran berhasil dikonfirmasi!"
                : "Pesanan menunggu pembayaran.";
    } catch (err) {
        console.error(err);
        error.value = "Gagal memuat data pesanan.";
    } finally {
        loading.value = false;
    }
};

// Fungsi Simulasi Pembayaran
const simulatePayment = async () => {
    if (!confirm("Simulasikan pembayaran sukses sekarang?")) return;

    processingPayment.value = true;
    try {
        const token = localStorage.getItem('authToken');

        await axios.post(
            `http://127.0.0.1:8000/api/payment/simulate/${orderId.value}`,
            {},
            { headers: { Authorization: `Bearer ${token}` }}
        );

        simulationMessage.value = "Pembayaran Anda berhasil dikonfirmasi!";
        alert("Pembayaran Berhasil (Simulasi)!");

        await fetchOrderDetails();
    } catch (err) {
        alert(err.response?.data?.message || "Gagal memproses pembayaran.");
    } finally {
        processingPayment.value = false;
    }
};

onMounted(() => {
    if (!orderId.value) {
        alert("ID Pesanan tidak ditemukan.");
        router.push({ name: 'home' });
        return;
    }

    fetchOrderDetails();
});
</script>

<template>
    <div class="container mx-auto p-4 md:p-8">
        <div 
            class="max-w-xl mx-auto bg-white shadow-xl rounded-xl p-8 text-center border-t-4"
            :class="orderData?.status === 'paid' ? 'border-green-500' : 'border-yellow-500'"
        >

            <div v-if="loading" class="py-10 text-gray-500">Memuat data...</div>
            <div v-else-if="error" class="py-10 text-red-500">{{ error }}</div>

            <div v-else>
                <!-- Icon -->
                <div class="mb-6 flex justify-center">
                    <div v-if="orderData.status === 'paid'" class="bg-green-100 p-4 rounded-full">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div v-else class="bg-yellow-100 p-4 rounded-full">
                        <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Judul -->
                <h1 class="text-2xl font-bold text-gray-800 mb-2">
                    {{ orderData.status === 'paid' ? 'Pembayaran Lunas!' : 'Menunggu Pembayaran' }}
                </h1>

                <p class="text-gray-500 mb-6">
                    ID Pesanan: #{{ orderData.id }}
                </p>

                <!-- Detail Pembayaran -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-100 text-left">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Metode</span>
                        <span class="font-medium uppercase">{{ orderData.payment_method }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Total Bayar</span>
                        <span class="font-bold text-green-600">
                            Rp {{ orderData.total_harga.toLocaleString('id-ID') }}
                        </span>
                    </div>
                </div>

                <!-- Simulasi Pembayaran -->
                <div class="space-y-3">
                    <div v-if="orderData.status === 'pending' && orderData.payment_method !== 'cod'">
                        <p class="text-sm text-gray-500 mb-2">Mode Simulasi Aktif:</p>

                        <button 
                            @click="simulatePayment" 
                            :disabled="processingPayment"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition disabled:opacity-50"
                        >
                            {{ processingPayment ? 'Memproses...' : 'Bayar Sekarang (Simulasi)' }}
                        </button>
                    </div>

                    <div v-else-if="orderData.status === 'pending' && orderData.payment_method === 'cod'" 
                        class="bg-blue-50 p-3 rounded text-blue-800 text-sm">
                        Silakan bayar tunai ke kurir saat barang sampai.
                    </div>

                    <div v-else class="bg-green-50 p-3 rounded text-green-800 text-sm font-medium">
                        Terima kasih! Pesanan Anda sedang diproses penjual.
                    </div>

                    <!-- Pesan simulasi -->
                    <p class="mt-3 text-sm text-gray-500">{{ simulationMessage }}</p>

                    <router-link 
                        to="/dashboard" 
                        class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 rounded-lg transition"
                    >
                        Kembali ke Dashboard
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
