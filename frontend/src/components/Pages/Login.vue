<template>
  <div class="w-full min-h-screen relative bg-pink-500 overflow-hidden flex flex-col">
    <!-- Background wave section -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-0 w-full" style="height: calc(100% - 3.5rem);">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 526" preserveAspectRatio="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M0 455.714 L60 435 C120 410 240 365 360 340 C480 320 600 315 720 308 C840 300 960 280 1080 250 C1200 220 1320 175 1380 150 L1440 130 V646 H0 Z" fill="#E7A0CC"/>
        </svg>
      </div>
    </div>
    
    <!-- Main Content - Flex grow untuk push footer ke bawah -->
    <div class="flex-1 flex items-center justify-center relative px-4 py-8 sm:py-0 z-10">
      <!-- Login Form Card -->
      <div class="w-full max-w-[288px] sm:w-72 px-5 sm:px-7 py-6 sm:py-7 bg-blue-900/20 rounded-[20px] shadow-[0px_5px_10px_0px_rgba(53,51,51,0.60)] backdrop-blur-md">
        
        <!-- =================================================================== -->
        <!--   Form diubah sedikit untuk menangani submit dan error               -->
        <!-- =================================================================== -->
        <form @submit.prevent="loginUser" class="w-full flex flex-col items-center gap-6 sm:gap-10">
          <!-- Logo/Avatar -->
          <img :src="zenith" alt="zenith" class="w-12 h-12 sm:w-16 sm:h-16 object-contain"/>
          
          <!-- Form Fields -->
          <div class="w-full flex flex-col gap-5 sm:gap-6">
            <!-- Username/Email Field -->
            <div class="flex flex-col gap-[5px] w-full">
              <input 
                v-model="form.email"
                type="text"
                placeholder="Username / Email"
                required
                class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1"
              />
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
            </div>
            
            <!-- Password Field -->
            <div class="flex flex-col gap-1.5 w-full">
              <input 
                v-model="form.password"
                type="password"
                placeholder="Password"
                required
                class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1"
              />
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
            </div>
          </div>

          <!-- =================================================================== -->
          <!--   Menampilkan pesan error jika ada                                  -->
          <!-- =================================================================== -->
          <div v-if="errorMessage" class="w-full p-2.5 bg-red-200 text-red-800 rounded-md text-center text-xs sm:text-sm font-medium font-['Ubuntu']">
            {{ errorMessage }}
          </div>

          <!-- =================================================================== -->
          <!--   Tombol diubah untuk menampilkan loading state                     -->
          <!-- =================================================================== -->
          <button 
            type="submit"
            :disabled="isLoading"
            class="w-full max-w-[208px] h-8 px-8 sm:px-16 py-2 bg-white rounded-2xl flex justify-center items-center hover:bg-gray-100 transition-colors disabled:opacity-75 disabled:cursor-not-allowed"
          >
            <span class="text-blue-900 text-sm sm:text-base font-medium font-['Ubuntu']">
              {{ isLoading ? 'Loading...' : 'Login' }}
            </span>
          </button>
        </form>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="w-full h-14 bg-white flex items-center justify-left mt-auto">
      <div class="text-center text-blue-900/70 text-xs sm:text-base font-normal font-['Ubuntu'] px-4">
        @ 2025 Zenith. All rights reserved.
      </div>
    </div>
  </div>
</template>

<!-- =================================================================== -->
<!--   SCRIPT SETUP DIPERBARUI                                         -->
<!-- =================================================================== -->
<script setup>
import zenith from '../../assets/zenith.png' 
import { ref } from 'vue'
import axios from 'axios' // <-- 1. Import axios
import { useRouter } from 'vue-router' // <-- 2. (Opsional) Import router jika ingin redirect

// (Opsional) Inisialisasi router
const router = useRouter()

// State untuk form
const form = ref({
  email: '',
  password: ''
})

// --- 3. Tambahkan state untuk loading dan error ---
const isLoading = ref(false)
const errorMessage = ref(null)

// --- 4. Ubah fungsi loginUser menjadi async dan panggil API ---
const loginUser = async () => {
  // Reset state
  isLoading.value = true
  errorMessage.value = null

  try {
    // Kirim request ke API Laravel
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: form.value.email,
      password: form.value.password
    });

    // Jika berhasil:
    console.log('Login berhasil:', response.data);
    
    // Simpan token di localStorage untuk request selanjutnya
    localStorage.setItem('authToken', response.data.token);
    
    // (Opsional) Arahkan ke halaman /
    router.push('/'); 
    
    // Ganti alert dengan pesan sukses (jika perlu) atau langsung redirect
    // Di sini saya akan reset form-nya saja
    form.value = { email: '', password: '' };
    // Anda bisa tambahkan state 'successMessage' jika mau

  } catch (error) {
    // Jika gagal:
    if (error.response && error.response.data) {
      // Tampilkan pesan error dari API (cth: "Email atau password salah.")
      errorMessage.value = error.response.data.message;
    } else {
      // Error jaringan atau server
      errorMessage.value = 'Terjadi kesalahan koneksi. Coba lagi nanti.';
    }
    console.error('Login gagal:', error);

  } finally {
    // Selesai (baik sukses atau gagal)
    isLoading.value = false
  }
}
</script>

<style scoped>
/* Style autofill Anda sudah bagus, biarkan saja */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-text-fill-color: white;
  -webkit-box-shadow: 0 0 0px 1000px transparent inset;
  transition: background-color 5000s ease-in-out 0s;
}
</style>