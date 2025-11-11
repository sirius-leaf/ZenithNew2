<template>
  <div class="w-full min-h-screen relative bg-pink-500 overflow-hidden flex flex-col">
    <!-- Background bottom section -->
    <div class="w-full h-[526px] left-0 top-[214px] absolute bg-pink-300 border-black"></div>
    
    <!-- Main Content Area -->
    <div class="flex-1 relative"></div>
    
    <!-- Registration Form Card -->
    <div class="w-72 h-96 px-7 py-7 left-1/2 -translate-x-1/2 top-[161px] absolute bg-blue-900/20 rounded-[20px] shadow-[0px_5px_10px_0px_rgba(53,51,51,0.60)] backdrop-blur-md inline-flex justify-start items-center gap-2.5">
      <form @submit.prevent="registerUser" class="w-60 inline-flex flex-col justify-start items-center gap-14">
        <!-- Logo/Avatar -->
        <img class="w-16 h-16 shadow-[0px_2px_10px_0px_rgba(53,51,51,0.59)] rounded-full" src="https://placehold.co/62x62" alt="Logo" />
        
        <!-- Form Fields -->
        <div class="self-stretch flex flex-col justify-start items-start gap-6">
          <!-- Username Field -->
          <div class="flex flex-col justify-start items-start gap-[5px] w-full">
            <input 
              v-model="form.name"
              type="text"
              placeholder="Username"
              required
              class="w-full bg-transparent border-none outline-none text-white text-base font-normal font-['Ubuntu'] pb-1"
            />
            <div class="w-60 h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
          </div>
          
          <!-- Email Field -->
          <div class="flex flex-col justify-start items-start gap-[5px] w-full">
            <input 
              v-model="form.email"
              type="email"
              placeholder="Email"
              required
              class="w-full bg-transparent border-none outline-none text-white text-base font-normal font-['Ubuntu'] pb-1"
            />
            <div class="w-60 h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
          </div>
          
          <!-- Password Field -->
          <div class="flex flex-col justify-start items-start gap-1.5 w-full">
            <input 
              v-model="form.password"
              type="password"
              placeholder="Password"
              required
              class="w-full bg-transparent border-none outline-none text-white text-base font-normal font-['Ubuntu'] pb-1"
            />
            <div class="w-60 h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
          </div>
        </div>
        
        <!-- Register Button -->
        <button 
          type="submit"
          class="w-52 h-8 px-16 py-2 bg-white rounded-2xl inline-flex justify-center items-center gap-2.5 hover:bg-gray-100 transition-colors"
        >
          <span class="text-blue-900 text-base font-medium font-['Ubuntu']">Register</span>
        </button>
      </form>
    </div>
    
    <!-- Footer -->
    <div class="w-full h-14 bg-white flex items-center justify-center">
      <div class="text-center text-blue-900/70 text-base font-normal font-['Ubuntu']">
        @ 2025 Zenith. All rights reserved.
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref } from 'vue'

const form = ref({
  name: '',
  email: '',
  password: ''
})

const registerUser = async () => {
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/register', form.value)
    console.log(res.data)
    alert('Registrasi berhasil!')
    // Reset form setelah berhasil
    form.value = {
      name: '',
      email: '',
      password: ''
    }
  } catch (err) {
    console.error(err.response?.data)
    alert('Registrasi gagal!')
  }
}
</script>

<style scoped>
/* Menghilangkan autofill background color pada input */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-text-fill-color: white;
  -webkit-box-shadow: 0 0 0px 1000px transparent inset;
  transition: background-color 5000s ease-in-out 0s;
}
</style>