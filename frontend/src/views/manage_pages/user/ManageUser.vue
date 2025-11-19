<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// State
const users = ref([]);
const loading = ref(true);
const error = ref(null);
const successMessage = ref(null);

// Ambil Token dari localStorage (asumsi Anda login pakai token)
const token = localStorage.getItem('authToken');

// Konfig Axios Header
const authConfig = {
    headers: {
        Authorization: `Bearer ${token}` 
    }
};

// 1. Fungsi Mengambil Data User
const fetchUsers = async () => {
    try {
        loading.value = true;
        // Sesuaikan URL dengan route API Anda
        const response = await axios.get('http://127.0.0.1:8000/api/manage/users', authConfig);
        users.value = response.data.data;
    } catch (err) {
        error.value = "Gagal memuat data user.";
        console.error(err);
    } finally {
        loading.value = false;
    }
};

// 2. Fungsi Update Role
const updateRole = async (user) => {
    // Simpan role lama untuk jaga-jaga jika update gagal
    const oldRole = user.role; 
    
    try {
        // Kirim request PUT
        await axios.put(`http://127.0.0.1:8000/api/manage/users/${user.id}`, {
            role: user.role // Nilai role baru dari dropdown
        }, authConfig);

        // Tampilkan notifikasi sukses sementara
        showSuccess('Role berhasil diperbarui!');
    } catch (err) {
        console.error(err);
        alert("Gagal mengubah role.");
        user.role = oldRole; // Kembalikan ke role lama jika gagal
    }
};

// 3. Fungsi Hapus User
const deleteUser = async (id) => {
    if (!confirm('Yakin ingin menghapus akun ini?')) return;

    try {
        await axios.delete(`http://127.0.0.1:8000/api/manage/users/${id}`, authConfig);
        
        // Hapus user dari list lokal agar tidak perlu refresh halaman
        users.value = users.value.filter(u => u.id !== id);
        showSuccess('User berhasil dihapus!');
    } catch (err) {
        console.error(err);
        alert("Gagal menghapus user.");
    }
};

// Helper untuk notifikasi sukses
const showSuccess = (msg) => {
    successMessage.value = msg;
    setTimeout(() => successMessage.value = null, 3000); // Hilang dalam 3 detik
};

// Jalankan saat komponen dimuat
onMounted(() => {
    fetchUsers();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Daftar User</h1>
        </div>

        <!-- Notifikasi Sukses -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border border-green-200">
            {{ successMessage }}
        </div>

        <!-- Notifikasi Error -->
        <div v-if="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border border-red-200">
            {{ error }}
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-10 text-gray-500">
            Memuat data...
        </div>

        <!-- Tabel User -->
        <div v-else class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No Telepon</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Alamat</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            {{ user.id }}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-medium text-gray-900">
                            {{ user.name }}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600">
                            {{ user.email }}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600">
                            {{ user.no_telpon ?? '-' }}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-gray-600">
                            {{ user.alamat ?? '-' }}
                        </td>
                        
                        <!-- Kolom Ubah Role -->
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <select 
                                v-model="user.role" 
                                @change="updateRole(user)"
                                class="block w-full bg-white border border-gray-300 text-gray-700 py-1 px-2 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                            >
                                <option value="user">User</option>
                                <option value="penjual">Penjual</option>
                                <option value="admin">Admin</option>
                            </select>
                        </td>

                        <!-- Kolom Aksi (Hapus) -->
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <button 
                                @click="deleteUser(user.id)"
                                class="text-red-600 hover:text-red-900 font-semibold transition-colors"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                    
                    <!-- Pesan jika data kosong -->
                    <tr v-if="users.length === 0">
                        <td colspan="7" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                            Belum ada user terdaftar.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>