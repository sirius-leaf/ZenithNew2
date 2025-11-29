<template>
  <div class="w-full min-h-screen relative bg-pink-500 overflow-hidden flex flex-col">

    <!-- BACKGROUND -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-0 w-full" style="height: calc(100% - 3.5rem);">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 1440 526" preserveAspectRatio="none">
          <path fill="#E7A0CC"
            d="M0 455.714 L60 435 C120 410 240 365 360 340 C480 320 600 315 
               720 308 C840 300 960 280 1080 250 C1200 220 1320 175 1380 150 
               L1440 130 V646 H0 Z" />
        </svg>
      </div>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="absolute inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="text-white text-lg font-semibold animate-pulse">Processing...</div>
    </div>

    <!-- MAIN -->
    <div class="flex-1 flex items-center justify-center relative px-4 py-8 sm:py-0">

      <!-- CARD -->
      <div class="w-full max-w-[330px] px-5 sm:px-7 py-6 bg-blue-900/20
                  rounded-[5px] shadow-xl backdrop-blur-3xl">

        <form @submit.prevent="registerUser"
              class="w-full flex flex-col items-center gap-8">

          <img :src="zenith" class="w-12 h-12 sm:w-16 sm:h-16 object-contain" />

          <div class="w-full flex flex-col gap-5">

            <div class="flex flex-col gap-[5px] w-full">
              <input v-model="form.name" type="text" placeholder="Username"
                     :disabled="loading"
                     class="w-full bg-transparent text-white placeholder-white/70 pb-1 outline-none" />
              <div class="w-full h-0 outline outline-1 outline-white"></div>
            </div>

            <div class="flex flex-col gap-[5px] w-full">
              <input v-model="form.email" type="email" placeholder="Email"
                     :disabled="loading"
                     class="w-full bg-transparent text-white placeholder-white/70 pb-1 outline-none" />
              <div class="w-full h-0 outline outline-1 outline-white"></div>
            </div>

            <div class="flex flex-col gap-1.5 w-full">
              <input v-model="form.password" type="password" placeholder="Password"
                     :disabled="loading"
                     class="w-full bg-transparent text-white placeholder-white/70 pb-1 outline-none" />
              <div class="w-full h-0 outline outline-1 outline-white"></div>
            </div>
          </div>

          <!-- RECAPTCHA -->
          <div id="recaptcha-box" class="mt-2"></div>

          <button type="submit" :disabled="loading"
            class="w-full max-w-[208px] h-8 bg-white rounded-2xl
                   flex justify-center items-center">
            <span class="text-blue-900">
              {{ loading ? "Loading..." : "Register" }}
            </span>
          </button>

          <div class="mt-2 flex flex-col items-center gap-1">
            <span class="text-white/80 text-xs">Sudah punya akun?</span>
            <router-link to="/login" class="text-white underline">
              Masuk sekarang
            </router-link>
          </div>

        </form>
      </div>
    </div>

    <div class="w-full h-14 bg-white flex items-center">
      <div class="text-blue-900/70 text-xs px-4">
        © 2025 Zenith. All rights reserved.
      </div>
    </div>

  </div>
</template>

<script setup>
import zenith from "@/assets/zenith.png"
import axios from "axios"
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"

const router = useRouter()

const form = ref({
  name: "",
  email: "",
  password: "",
  recaptcha: ""
})

const loading = ref(false)

onMounted(() => {
  const interval = setInterval(() => {
    if (window.grecaptcha) {
      window.grecaptcha.render("recaptcha-box", {
        sitekey: "6Leq3hssAAAAAOk8okP2kiWL72mmw_9wfxQQrZLK",
        callback: "onCaptchaSuccess"
      })
      clearInterval(interval)
    }
  }, 300)
})

const registerUser = async () => {
  try {
    loading.value = true

    form.value.recaptcha = window.__captchaToken || ""

    const res = await axios.post("http://127.0.0.1:8000/api/register", form.value)

    alert("Registrasi berhasil! Silakan cek email untuk verifikasi.")

    form.value = { name: "", email: "", password: "", recaptcha: "" }
    window.__captchaToken = null

    router.push("/login")

  } catch (err) {
    console.error(err.response?.data)
    alert("Registrasi gagal!")
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-text-fill-color: white;
  -webkit-box-shadow: 0 0 0px 1000px transparent inset;
}
</style>
