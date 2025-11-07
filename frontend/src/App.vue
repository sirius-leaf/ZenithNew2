<script setup>
import FooterSection from "./components/FooterSection.vue";
import Navbar from "./components/Navbar.vue";
</script>

<template>
  <div class="font-sans text-gray-800">
    <Navbar />
    <RouterView />
    <FooterSection />
  </div>
</template>

<style scoped>
div {
  overflow-x: hidden;
}
</style>
