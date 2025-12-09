<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 relative overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
      <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-purple-400/30 rounded-full blur-[120px]"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-400/30 rounded-full blur-[120px]"></div>
    </div>

    <!-- Content Card -->
    <div class="relative z-10 text-center px-6">
      <div class="mb-8 relative inline-block">
        <h1 class="text-[40px] md:text-[120px] font-black leading-none tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 animate-pulse-slow">
          #404
        </h1>
        <div class="absolute -bottom-4 left-1/2 transform -translate-x-1/2 w-full h-4 bg-purple-500/20 blur-xl rounded-[100%]"></div>
      </div>

      <h2 class="text-xl md:text-4xl font-bold text-gray-900 mb-4 tracking-wide">
        Halaman Tidak Ditemukan
      </h2>
      
      <p class="text-gray-600 text-md md:text-lg max-w-2xl mx-auto mb-10 leading-relaxed">
        Oops! Sepertinya kamu tersesat di antah berantah. Halaman yang kamu cari mungkin sudah dipindahkan atau tidak pernah ada.
      </p>

      <div class="flex justify-center items-center">
        <button
          @click="goBack"
          class="group px-8 py-3.5 bg-white border border-gray-200 text-gray-800 rounded-xl font-bold shadow-lg shadow-gray-200/50 hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center gap-2 cursor-pointer"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-1 transition-transform"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
          Kembali
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

const goBack = () => {
  router.back();
};
</script>

<style scoped>
.animate-pulse-slow {
  animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .8;
  }
}
</style>
