<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Toggle Switch -->
    <div class="flex justify-center mb-8">
      <div class="bg-gray-100 p-1 rounded-full inline-flex">
        <button
          @click="activeTab = 'user'"
          :class="[
            'px-6 py-2 rounded-full text-sm font-medium transition-all duration-200',
            activeTab === 'user'
              ? 'bg-white text-blue-600 shadow-sm'
              : 'text-gray-500 hover:text-gray-700',
          ]"
        >
          Kelola User
        </button>
        <button
          @click="activeTab = 'seller'"
          :class="[
            'px-6 py-2 rounded-full text-sm font-medium transition-all duration-200',
            activeTab === 'seller'
              ? 'bg-white text-pink-600 shadow-sm'
              : 'text-gray-500 hover:text-gray-700',
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
