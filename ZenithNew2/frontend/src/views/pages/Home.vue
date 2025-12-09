<script setup>
import { onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

import CategorySection from "../../components/sections/CategorySection.vue";
import HeroSection from "../../components/sections/HeroSection.vue";
import Testimonials from "../../components/sections/Testimonials.vue";
import WhyChooseUs from "../../components/sections/WhyChooseUs.vue";

onMounted(() => {
  const token = localStorage.getItem("authToken");

  if (token) {
    if (localStorage.getItem("userRole") === "admin") router.push("/admin");
    else router.push("/dashboard");

    return;
  }
});
</script>

<template>
  <div>
    <HeroSection />
    <CategorySection />
    <WhyChooseUs />
    <Testimonials />
  </div>
</template>

<style scoped></style>
