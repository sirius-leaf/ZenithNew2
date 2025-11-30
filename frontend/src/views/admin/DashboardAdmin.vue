<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Toggle Switch -->
    <div class="flex justify-center mb-10">
      <div
        class="bg-white p-1.5 rounded-full inline-flex shadow-md border border-gray-100"
      >
        <button
          @click="activeTab = 'user'"
          :class="[
            'px-8 py-3 rounded-full text-base font-bold transition-all duration-300 transform',
            activeTab === 'user'
              ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg scale-105'
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50',
          ]"
        >
          Kelola User
        </button>
        <button
          @click="activeTab = 'seller'"
          :class="[
            'px-8 py-3 rounded-full text-base font-bold transition-all duration-300 transform',
            activeTab === 'seller'
              ? 'bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-lg scale-105'
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50',
          ]"
        >
          Kelola Seller
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="transition-opacity duration-300">
      <KelolaUser v-if="activeTab === 'user'" />
      <KelolaSeller v-if="activeTab === 'seller'" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const activeTab = ref("user");

import KelolaUser from "./KelolaUser.vue";
import KelolaSeller from "./KelolaSeller.vue";

onMounted(() => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  if (localStorage.getItem("userRole") !== "admin") {
    alert("Akses ditolak, Anda bukan admin");
    router.push("/");
  }
});
</script>
