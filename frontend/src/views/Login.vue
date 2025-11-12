<template>
  <div class="max-w-md mx-auto mt-12 p-6 bg-white rounded-lg shadow-xl font-sans">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Login</h2>
    <p class="text-center text-gray-600 mb-8 text-sm">Silakan login untuk mendapatkan token API Anda.</p>
    
    <form @submit.prevent="handleLogin">
      
      <div class="mb-5">
        <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email:</label>
        <input 
          type="email" 
          id="email" 
          v-model="email" 
          required 
          class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
          placeholder="email@example.com"
        >
      </div>
      
      <div class="mb-6">
        <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password:</label>
        <input 
          type="password" 
          id="password" 
          v-model="password" 
          required 
          class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
          placeholder="********"
        >
      </div>
      
      <button 
        type="submit" 
        :disabled="isLoading"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ isLoading ? 'Memuat...' : 'Login' }}
      </button>
    </form>
    
    <div v-if="errorMessage" class="mt-8 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline ml-2">{{ errorMessage }}</span>
    </div>
    
    <div v-if="token" class="mt-8 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative" role="alert">
      <strong class="font-bold">Login Berhasil!</strong>
      <p class="block mt-2">Token Anda (simpan ini untuk request selanjutnya):</p>
      <pre class="bg-green-50 p-3 rounded text-green-800 text-sm break-all whitespace-pre-wrap mt-2">{{ token }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

// --- State Reaktif ---
const email = ref('');
const password = ref('');
const token = ref(null);
const errorMessage = ref(null);
const isLoading = ref(false);

// --- Fungsi ---
const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = null;
  token.value = null;

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: email.value,
      password: password.value
    });

    token.value = response.data.token;
    
    // Simpan token di localStorage atau Vuex/Pinia di aplikasi nyata
    // localStorage.setItem('authToken', response.data.token);
    // router.push('/dashboard');

  } catch (error) {
    if (error.response && error.response.data) {
      errorMessage.value = error.response.data.message || 'Terjadi kesalahan saat login.';
    } else {
      errorMessage.value = 'Terjadi kesalahan jaringan atau server tidak merespons.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Tidak perlu banyak CSS custom lagi, semua ditangani Tailwind */
/* Anda bisa menambahkan sedikit global CSS di main.css jika perlu */
</style>