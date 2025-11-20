<template>
  <div class="max-w-md mx-auto mt-12 p-6 bg-white rounded-lg shadow-xl font-sans">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-2">Login</h2>
    <p class="text-center text-gray-600 mb-8 text-sm">
      Masukkan email dan password Anda.
    </p>

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
          :disabled="isLoading"
        />
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
          :disabled="isLoading"
        />
      </div>

      <button
        type="submit"
        :disabled="isLoading"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ isLoading ? 'Memuat...' : 'Login' }}
      </button>
    </form>

    <!-- ✅ Error Message (Biasa & Banned) -->
    <div
      v-if="errorMessage"
      class="mt-8 p-4 rounded relative"
      :class="{
        'bg-red-100 border border-red-400 text-red-700': !isBannedError,
        'bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700': isBannedError
      }"
      role="alert"
    >
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg
            v-if="!isBannedError"
            class="h-5 w-5 text-red-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-yellow-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
          >
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium">
            {{ isBannedError ? 'Akun Dibatasi' : 'Login Gagal' }}
          </h3>
          <div class="mt-2 text-sm">
            <p>{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Success Token (Opsional, untuk dev) -->
    <div v-if="token" class="mt-8 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
      <strong class="font-bold">✅ Login Berhasil!</strong>
      <p class="mt-1 text-sm">Token Anda:</p>
      <pre class="mt-2 bg-white p-2 rounded border text-xs break-all">{{ token }}</pre>
    </div>

    <!-- ✅ Link ke Register (Opsional) -->
    <div class="mt-6 text-center text-sm text-gray-600">
      Belum punya akun?
      <RouterLink to="/register" class="text-blue-600 hover:underline ml-1">Daftar di sini</RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';

// State
const email = ref('');
const password = ref('');
const token = ref(null);
const errorMessage = ref(null);
const isBannedError = ref(false);
const isLoading = ref(false);

// Login handler
const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = null;
  isBannedError.value = false;
  token.value = null;

  try {
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value
    });

    token.value = response.data.token;
    errorMessage.value = null;

    // Simpan token (untuk app nyata)
    localStorage.setItem('auth_token', response.data.token);

    // Redirect ke dashboard (opsional, sesuaikan route Anda)
    // router.push('/dashboard');

  } catch (error) {
    const err = error.response?.data;

    if (error.response?.status === 403 && err?.banned === true) {
      // 🔒 Banned case
      errorMessage.value = err.message;
      isBannedError.value = true;
    } else {
      // ❌ Error biasa
      errorMessage.value = err?.message || 'Terjadi kesalahan. Silakan coba lagi.';
      isBannedError.value = false;
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Opsional: animasi fade-in */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-in-up {
  animation: fadeInUp 0.4s ease-out;
}
</style>