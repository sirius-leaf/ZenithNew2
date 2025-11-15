<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();
const user = ref(null);

// ambil data user dari localStorage atau API
onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  try {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    const res = await axios.get("http://127.0.0.1:8000/api/user");
    user.value = res.data;
  } catch (error) {
    // token tidak valid
    localStorage.removeItem("token");
    router.push("/login");
  }
});
</script>

<template>
  <div v-if="user">
    <div class="flex">
      <div class="w-64 flex-none border-r-1 p-4">
        <h2 class="text-l font-bold mb-4">Menu</h2>
        <ul class="list-none text-blue-600 underline">
          <li><a href="/dashboard">Dashboard</a></li>
          <li><a href="/dashboard/manage/pcBuild">Manage PC Build</a></li>
          <li><a href="#">Item</a></li>
          <li><a href="#">Item</a></li>
        </ul>
      </div>

      <div class="w-32 flex-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Selamat datang, {{ user.name }}</h2>
        <p>Email: {{ user.email }}</p>

        <router-view />
      </div>
    </div>
  </div>
</template>

<style scoped></style>
