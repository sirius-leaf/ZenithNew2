<!-- src/views/DashboardAdmin.vue -->
<template>
  <div>
    <KelolaUser />
    <KelolaSeller />
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

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
    router.push("/dashboard");
  }
});
</script>
