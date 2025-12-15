<template>
  <div class="w-full min-h-screen relative bg-pink-500 overflow-hidden flex flex-col">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-0 w-full" style="height: calc(100% - 3.5rem);">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 526" preserveAspectRatio="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d= "M0 455.714 L60 435 C120 410 240 365 360 340 C480 320 600 315 720 308 C840 300 960 280 1080 250 C1200 220 1320 175 1380 150 L1440 130 V646 H0 Z" fill="#E7A0CC"/>
        </svg>
      </div>
    </div>
    
    <div class="flex-1 flex items-center justify-center relative px-4 py-8 sm:py-0">
      <div class="w-full max-w-[400px] sm:w-[450px] px-8 sm:px-10 py-8 sm:py-10 bg-blue-900/20 rounded-[5px] shadow-xl backdrop-blur-3xl">
        <form @submit.prevent="registerUser" class="w-full flex flex-col items-center gap-8 sm:gap-14">
          <img :src="zenith" alt="zenith" class="w-12 h-12 sm:w-16 sm:h-16 object-contain"/>
          
          <div class="w-full flex flex-col gap-5 sm:gap-6">
            
            <div class="flex flex-col gap-[5px] w-full">
              <input 
                v-model="form.name"
                type="text"
                placeholder="Username"
                required
                class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1"
              />
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
            </div>
            
            <div class="flex flex-col gap-[5px] w-full">
              <input 
                v-model="form.email"
                type="email"
                placeholder="Email"
                required
                class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1"
              />
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
            </div>
            
            <div class="flex flex-col gap-1.5 w-full">
              <div class="relative w-full">
                <input 
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Password"
                  required
                  class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1 pr-8"
                />
                <button 
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-0 top-0 bottom-1 flex items-center justify-center text-white/70 hover:text-white transition-colors"
                >
                  <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                  </svg>
                </button>
              </div>
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
            </div>

            <div class="flex flex-col gap-1.5 w-full">
              <div class="relative w-full">
                <input 
                  v-model="form.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="Confirm Password"
                  required
                  class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1 pr-8"
                />
                 <button 
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-0 top-0 bottom-1 flex items-center justify-center text-white/70 hover:text-white transition-colors"
                >
                   <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                  </svg>
                </button>
              </div>
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
              
              <span v-if="errorMessage" class="text-red-800 bg-red-200 text-xs font-['Ubuntu'] mt-1 px-1 rounded">{{ errorMessage }}</span>
            </div>

          </div>
          
          <button 
            type="submit"
            :disabled="isLoading"
            class="w-full max-w-[208px] h-8 px-8 sm:px-16 py-2 bg-white rounded-2xl flex justify-center items-center hover:bg-gray-100 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <span class="text-blue-900 text-sm sm:text-base font-medium font-['Ubuntu']">{{ isLoading ? 'Loading...' : 'Register' }}</span>
          </button>

          <div class="mt-2 flex flex-col items-center gap-1">
            <span class="text-white/80 text-xs sm:text-sm font-normal font-['Ubuntu']">
              Sudah punya akun?
            </span>
            <router-link
              to="/login"
              class="text-white text-xs sm:text-sm font-medium font-['Ubuntu'] underline hover:text-white/90 transition-colors"
            >
              Masuk sekarang
            </router-link>
          </div>
        </form>
      </div>
    </div>
    
    <div class="w-full h-14 bg-white flex items-center justify-left mt-auto">
      <div class="text-center text-blue-900/70 text-xs sm:text-base font-normal font-['Ubuntu'] px-4">
        @ 2025 Zenith. All rights reserved.
      </div>
    </div>
  </div>
</template>

<script setup>
import zenith from '@/assets/zenith.png'
import axios from 'axios'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Menambahkan password_confirmation ke state form
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '' // Field wajib untuk validasi 'confirmed' Laravel
})

const errorMessage = ref('')
const isLoading = ref(false)

const registerUser = async () => {
  // Reset error
  errorMessage.value = ''

  // Validasi Client Side Sederhana
  if (form.value.password.length < 8) {
    errorMessage.value = 'Input tidak valid: Password minimal 8 karakter'
    return
  }

  // Cek apakah password dan confirm password sama sebelum kirim ke server
  if (form.value.password !== form.value.password_confirmation) {
    errorMessage.value = 'Password dan Konfirmasi Password tidak cocok'
    return
  }

  isLoading.value = true

  try {
    const res = await axios.post('http://127.0.0.1:8000/api/register', form.value)
    console.log(res.data)
    alert('Registrasi berhasil! Silakan cek email untuk kode verifikasi.')
    
    const email = form.value.email

    // Reset form setelah berhasil
    form.value = {
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    }

    router.push({ path: '/verify-code', query: { email: email } })

  } catch (err) {
    console.error(err.response?.data)
    // Menangkap pesan error spesifik dari Laravel jika ada
    if (err.response?.data?.errors?.password) {
        errorMessage.value = err.response.data.errors.password[0];
    } else {
        errorMessage.value = err.response?.data?.message || 'Registrasi gagal!';
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-text-fill-color: white;
  -webkit-box-shadow: 0 0 0px 1000px transparent inset;
  transition: background-color 5000s ease-in-out 0s;
}
</style>