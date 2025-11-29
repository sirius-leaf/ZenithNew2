<template>
  <div
    class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4 text-center"
  >
    <h1 class="text-9xl font-bold text-pink-500 mb-4">Error</h1>
    <p class="text-xl md:text-2xl text-gray-700 font-medium mb-8">
      Aduh! Tujuanmu nggak ada! Mungkin kamu salah jalan atau alamat. Ayo balik
      sebelum gelap!
    </p>
    <button
      @click="goBack"
      class="px-8 py-3 bg-pink-600 text-white rounded-full font-semibold hover:bg-pink-700 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
    >
      Kembali
    </button>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

const goBack = () => {
  router.back();
};
</script>
