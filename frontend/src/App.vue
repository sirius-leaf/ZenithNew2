<script setup>
import { useRoute } from 'vue-router'
import FooterSection from "./components/layout/FooterSection.vue";
import Navbar from "./components/layout/Navbar.vue";

const route = useRoute()
</script>

<template>
  <div class="font-sans text-gray-800">
    <Navbar v-if="!route.meta.hideLayout" />
    <RouterView />
    <FooterSection v-if="!route.meta.hideLayout" />
  </div>
</template>