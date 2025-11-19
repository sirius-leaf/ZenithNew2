<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

// Ambil Order ID dari URL query param
const orderId = ref(route.query.id); 
const orderStatus = ref('pending'); // Status awal
const simulationLoading = ref(false);
const simulationMessage = ref('Pesanan menunggu pembayaran.');
const orderDetails = ref(null); // Untuk menampung detail pesanan jika diambil

// Fungsi Simulasi Pembayaran
const simulatePayment = async () => {
    if (!orderId.value) return; // Jangan lakukan jika ID tidak ada

    simulationLoading.value = true;
    try {
        const token = localStorage.getItem('authToken');
        
        // Panggil API simulasi
        const response = await axios.post(`http://127.0.0.1:8000/api/payment/simulate/${orderId.value}`, {}, {
            headers: { Authorization: `Bearer ${token}` }
        });

        simulationMessage.value = response.data.message;
        orderStatus.value = 'paid'; // Update status di UI

    } catch (error) {
        simulationMessage.value = error.response?.data?.message || 'Gagal memproses pembayaran.';
        console.error("Payment error:", error);
    } finally {
        simulationLoading.value = false;
    }
};

onMounted(() => {
    // Verifikasi ID di URL
    if (!orderId.value) {
        alert("Error: ID Pesanan tidak ditemukan di URL.");
        router.push({ name: 'home' });
    }
    
    // Di sini Anda bisa memanggil API lain untuk mengambil detail pesanan (harga, alamat, dll)
    // Beri nama route API baru: /api/orders/{id}
});
</script>

<template>
    <div class="container mx-auto p-4 md:p-8">
        <div class="max-w-xl mx-auto bg-white shadow-xl rounded-xl p-8 text-center">
            
            <div :class="{'bg-green-100 text-green-700': orderStatus === 'paid', 'bg-yellow-100 text-yellow-700': orderStatus === 'pending'}" 
                 class="p-4 rounded-lg mb-6 text-lg font-semibold transition-colors duration-500">
                Status Pesanan #{{ orderId }} Saat Ini: 
                <span class="uppercase font-bold">{{ orderStatus }}</span>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-4">Pesanan Berhasil Dibuat!</h1>
            <p class="text-gray-600 mb-8">Pesanan Anda telah dicatat. Mohon segera selesaikan pembayaran.</p>

            <div v-if="orderStatus === 'pending'">
                <p class="text-red-500 mb-4 font-semibold">Instruksi Pembayaran (Simulasi): Transfer ke Rekening 123456</p>
                
                <button 
                    @click="simulatePayment" 
                    :disabled="simulationLoading"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition disabled:bg-gray-400"
                >
                    {{ simulationLoading ? 'Mengkonfirmasi...' : 'Konfirmasi Pembayaran (Simulasi)' }}
                </button>
            </div>
            
            <div v-else class="text-green-600 font-bold text-xl my-4">
                🎉 Pembayaran Anda Berhasil Dikonfirmasi!
            </div>

            <p class="mt-4 text-sm text-gray-500">{{ simulationMessage }}</p>
            
            <router-link to="/dashboard" class="mt-6 inline-block text-blue-500 hover:underline">
                Lihat Riwayat Pesanan
            </router-link>
        </div>
    </div>
</template>