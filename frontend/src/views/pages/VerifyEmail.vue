<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="text-center">
      <h2 class="text-2xl font-semibold mb-4">Verifying your email...</h2>
      <p class="text-gray-600">
        Please wait while we verify your email address.
      </p>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

onMounted(() => {
  const verifyUrl = route.query.verify_url;

  if (verifyUrl) {
    // Redirect to the backend verification URL
    window.location.href = verifyUrl;
  } else {
    // If no URL is provided, go to home or login
    router.push("/login");
  }
});
</script>
