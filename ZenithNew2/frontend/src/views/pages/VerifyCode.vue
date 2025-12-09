<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center">Verifikasi Email</h2>
      <p class="text-gray-600 mb-6 text-center">
        Masukkan 6 digit kode yang telah dikirim ke email Anda: <strong>{{ email }}</strong>
      </p>

      <form @submit.prevent="verifyCode">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
            Kode Verifikasi
          </label>
          <input
            v-model="code"
            type="text"
            id="code"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            placeholder="123456"
            maxlength="6"
            required
          />
        </div>

        <div v-if="errorMessage" class="mb-4 text-red-500 text-sm text-center">
          {{ errorMessage }}
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
        >
          {{ isLoading ? 'Memverifikasi...' : 'Verifikasi' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const email = ref('');
const code = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

onMounted(() => {
  email.value = route.query.email || '';
  if (!email.value) {
    toast.error("Email tidak ditemukan. Silakan registrasi ulang.");
    router.push('/register');
  }
});

const verifyCode = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    await axios.post('http://127.0.0.1:8000/api/verify-code', {
      email: email.value,
      code: code.value
    });

    toast.success("Email berhasil diverifikasi! Silakan login.");
    router.push('/login');
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = "Terjadi kesalahan. Silakan coba lagi.";
    }
  } finally {
    isLoading.value = false;
  }
};
</script>
