<template>
  <div class="w-full bg-gray-50 p-4 sm:p-6">

    <!-- Header -->
    <div class="mb-8 flex items-center gap-2">
      <button @click="$router.back()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
        ← Kembali ke Dashboard
      </button>
    </div>

    <!-- Loading -->
    <div v-if="!user" class="max-w-2xl mx-auto text-center py-10">
      <p class="text-gray-600">Memuat data akun...</p>
    </div>

    <!-- Profile Card -->
    <div v-else class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-6">
      <h2 class="text-lg font-semibold text-neutral-950 mb-6">Informasi Akun</h2>

      <div class="space-y-6">

        <!-- Username -->
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-pink-50 rounded-lg flex justify-center items-center">
            <svg class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 14c3.866 0 7 1.791 7 4v1H5v-1c0-2.209 3.134-4 7-4zm0-2a4 4 0 100-8 4 4 0 000 8z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Username</p>
            <p class="text-base font-medium">{{ user.name }}</p>
          </div>
        </div>

        <!-- Email -->
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-50 rounded-lg flex justify-center items-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Email</p>
            <p class="text-base font-medium">{{ user.email }}</p>
          </div>
        </div>

        <!-- Role -->
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-50 rounded-lg flex justify-center items-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 2l7 4v6c0 5.25-3.5 10-7 10S5 17.25 5 12V6l7-4zm-2 10l2 2 4-4" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Role</p>
            <p class="text-base font-medium">{{ user.role }}</p>
          </div>
        </div>

      </div>

      <!-- Tombol Edit -->
      <button
        @click="$router.push('/profile/edit')"
        class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700"
      >
        Edit Profil
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const user = ref(null);

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) return router.push("/login");

  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

  const res = await axios.get("http://127.0.0.1:8000/api/profile");
  user.value = res.data;
});
</script>