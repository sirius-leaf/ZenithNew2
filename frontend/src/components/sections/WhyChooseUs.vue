<script setup>
const features = [
  {
    icon: "why1.png",
    title: "Endless Choices",
    desc: "Discover collections from many brands, all in one marketplace.",
  },
  {
    icon: "why2.png",
    title: "Shop with Confidence",
    desc: "Smooth checkout, secure payment, and fast delivery.",
  },
  {
    icon: "why3.png",
    title: "Trusted by Shoppers",
    desc: "Real reviews and ratings guide your best purchase.",
  },
];
const getIcon = (icon) => new URL(`../assets/${icon}`, import.meta.url).href;
</script>

<template>
  <section
    data-aos="fade-up"
    data-aos-duration="1300"
    class="bg-pink-500 text-white py-16 px-6"
  >
    <h3 class="text-3xl font-bold text-center my-[-4] mb-14">WHY CHOOSE US?</h3>

    <div
      class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 mx-auto justify-center gap-10"
    >
      <div
        v-for="feature in features"
        :key="feature.id"
        class="text-center max-w-xs mx-auto"
      >
        <img
          class="mx-auto mb-4"
          :src="getIcon(feature.icon)"
          :alt="feature.title"
        />
        <h4 class="mx-auto font-bold text-lg mb-2">
          {{ feature.title }}
        </h4>
        <p>{{ feature.desc }}</p>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* img {
    width: 100px;
} */
</style>
