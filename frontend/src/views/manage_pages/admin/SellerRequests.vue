<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const requests = ref([]);
const loading = ref(false);

const token = localStorage.getItem('authToken');
const config = { headers: { Authorization: `Bearer ${token}` } };

// 1. Ambil Data Request
const fetchRequests = async () => {
    loading.value = true;
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/manage/admin/seller-requests', config);
        requests.value = response.data.data;
    } catch (error) {
        console.error("Gagal ambil data", error);
    } finally {
        loading.value = false;
    }
};

// 2. Approve User
const approveUser = async (id) => {
    if(!confirm("Setujui user ini menjadi penjual?")) return;

    try {
        await axios.post(`http://127.0.0.1:8000/api/manage/admin/seller-requests/${id}/approve`, {}, config);
        
        // Hapus dari list lokal
        requests.value = requests.value.filter(u => u.id !== id);
        alert("User berhasil disetujui!");
    } catch (error) {
        alert("Gagal menyetujui user.");
    }
};

onMounted(() => fetchRequests());
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Permintaan Menjadi Penjual</h1>

        <div v-if="loading" class="text-gray-500">Memuat data...</div>

        <div v-else class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="p-3 border-b">Nama</th>
                        <th class="p-3 border-b">Email</th>
                        <th class="p-3 border-b">Tanggal Request</th>
                        <th class="p-3 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="req in requests" :key="req.id" class="hover:bg-gray-50">
                        <td class="p-3 border-b">{{ req.name }}</td>
                        <td class="p-3 border-b">{{ req.email }}</td>
                        <td class="p-3 border-b">{{ new Date(req.created_at).toLocaleDateString() }}</td>
                        <td class="p-3 border-b">
                            <button 
                                @click="approveUser(req.id)"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm"
                            >
                                Approve
                            </button>
                        </td>
                    </tr>
                    <tr v-if="requests.length === 0">
                        <td colspan="4" class="p-4 text-center text-gray-500">Tidak ada permintaan baru.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>