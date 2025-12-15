<template>
  <div class="w-full min-h-screen relative bg-pink-500 overflow-hidden flex flex-col">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-0 w-full" style="height: calc(100% - 3.5rem)">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 526" preserveAspectRatio="none">
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M0 455.714 L60 435 C120 410 240 365 360 340 C480 320 600 315 720 308 C840 300 960 280 1080 250 C1200 220 1320 175 1380 150 L1440 130 V646 H0 Z"
            fill="#E7A0CC" />
        </svg>
      </div>
    </div>

    <button
      @click="$router.back()"
      class="absolute top-4 left-4 z-20 text-white hover:text-gray-200 transition-colors flex items-center gap-2 font-['Ubuntu']">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      <span class="text-sm font-medium">Kembali</span>
    </button>

    <div class="flex-1 flex items-center justify-center relative px-4 py-8 sm:py-0 z-10">
      <div
        class="w-full max-w-[400px] sm:w-[450px] px-8 sm:px-10 py-8 sm:py-10 bg-blue-900/20 rounded-[5px] shadow-xl backdrop-blur-3xl">
        <form @submit.prevent="loginUser" class="w-full flex flex-col items-center gap-6 sm:gap-10">
          <img :src="zenith" alt="zenith" class="w-12 h-12 sm:w-16 sm:h-16 object-contain" />

          <div class="w-full flex flex-col gap-5 sm:gap-6">
            <div class="flex flex-col gap-[5px] w-full">
              <input
                v-model="form.email"
                type="text"
                placeholder="Username / Email"
                required
                class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1" />
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
            </div>

            <div class="flex flex-col gap-1.5 w-full">
              <input
                v-model="form.password"
                type="password"
                placeholder="Password"
                required
                class="w-full bg-transparent border-none outline-none text-white text-sm sm:text-base font-normal font-['Ubuntu'] placeholder-white/70 pb-1" />
              <div class="w-full h-0 outline outline-1 outline-offset-[-0.50px] outline-white"></div>
            </div>
          </div>

          <div class="w-full flex justify-center min-h-[78px] mt-[-10px]">
             <div id="recaptcha-box"></div>
          </div>

          <div
            v-if="errorMessage"
            class="w-full p-2.5 bg-red-200 text-red-800 rounded-md text-center text-xs sm:text-sm font-medium font-['Ubuntu']">
            {{ errorMessage }}
          </div>

          <button
            type="submit"
            :disabled="isLoading"
            class="w-full max-w-[208px] h-8 px-8 sm:px-16 py-2 bg-white rounded-2xl flex justify-center items-center hover:bg-gray-100 transition-colors disabled:opacity-75 disabled:cursor-not-allowed">
            <span class="text-blue-900 text-sm sm:text-base font-medium font-['Ubuntu']">
              {{ isLoading ? "Loading..." : "Login" }}
            </span>
          </button>

          <div class="mt-4 flex flex-col items-center gap-1">
            <span class="text-white/80 text-xs sm:text-sm font-normal font-['Ubuntu']">Belom punya akun?</span>
            <router-link
              to="/register"
              class="text-white text-xs sm:text-sm font-medium font-['Ubuntu'] underline hover:text-white/90 transition-colors">
              Daftar sekarang
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
import zenith from "../../assets/zenith.png"; // Pastikan path gambar ini benar sesuai struktur folder Anda
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

// Key dari .env Anda
const RECAPTCHA_SITE_KEY = "6Leq3hssAAAAAOk8okP2kiWL72mmw_9wfxQQrZLK";

const form = ref({
  email: "",
  password: "",
  recaptcha: "",
});

const isLoading = ref(false);
const errorMessage = ref(null);

onMounted(async () => {
  axios.defaults.withCredentials = true;

  // 1. Ambil Cookie Sanctum
  try {
    await axios.get("http://127.0.0.1:8000/sanctum/csrf-cookie");
  } catch (e) {
    console.error("CSRF Init Error:", e);
  }

  // 2. Inject Script Google Recaptcha secara dinamis jika belum ada
  if (!document.getElementById("recaptcha-script")) {
    const script = document.createElement("script");
    script.id = "recaptcha-script";
    script.src = "https://www.google.com/recaptcha/api.js?render=explicit";
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
  }

  // 3. Callback Global saat User mencentang Recaptcha
  window.onCaptchaSuccess = (token) => {
    // console.log("Token received:", token);
    form.value.recaptcha = token;
    errorMessage.value = null; // Hapus error jika user sudah mencentang
  };

  // 4. Render Widget setelah script siap
  const interval = setInterval(() => {
    if (window.grecaptcha && window.grecaptcha.render) {
      try {
        window.grecaptcha.render("recaptcha-box", {
          sitekey: RECAPTCHA_SITE_KEY,
          callback: "onCaptchaSuccess",
          theme: "light",
        });
      } catch (e) {
        // Mencegah error jika render dipanggil dua kali (hot reload)
        // console.warn("Recaptcha already rendered");
      }
      clearInterval(interval);
    }
  }, 300);
});

const loginUser = async () => {
  // Validasi di sisi Client
  if (!form.value.recaptcha) {
    errorMessage.value = "Silakan centang 'I'm not a robot' terlebih dahulu.";
    return;
  }

  isLoading.value = true;
  errorMessage.value = null;

  try {
    // Ambil Token CSRF terbaru
    const res = await axios.get("http://127.0.0.1:8000/csrf-token");
    const token = res.data.token;

    const response = await axios.post(
      "http://127.0.0.1:8000/api/login",
      {
        email: form.value.email,
        password: form.value.password,
        recaptcha: form.value.recaptcha, // Mengirim token asli ke Laravel
      },
      {
        headers: {
          "X-CSRF-TOKEN": token,
          "Content-Type": "application/json",
        },
      }
    );

    const role = response.data.user.role;

    // Simpan token & role
    localStorage.setItem("authToken", response.data.token);
    localStorage.setItem("userRole", role);
    axios.defaults.headers.common["Authorization"] = `Bearer ${response.data.token}`;

    // Reset form
    form.value = { email: "", password: "", recaptcha: "" };
    
    // Redirect sesuai role
    if (role === "admin") router.push("/admin");
    else router.push("/dashboard");

  } catch (error) {
    // Reset Recaptcha jika gagal login (agar user bisa centang ulang)
    if (window.grecaptcha) {
        window.grecaptcha.reset();
        form.value.recaptcha = "";
    }

    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = "Terjadi kesalahan koneksi. Coba lagi nanti.";
    }
    console.error("Login gagal:", error);
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Styling autofill browser agar teks tetap putih */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-text-fill-color: white;
  -webkit-box-shadow: 0 0 0px 1000px transparent inset;
  transition: background-color 5000s ease-in-out 0s;
}
</style>