<script setup>
const hero = "/src/assets/hero.png";
</script>

<template>
  <section
    class="flex flex-col md:flex-row items-center justify-between py-16 px-8 md:px-16 bg-gradient-to-b from-white to-pink-100"
  >
    <!-- Kiri: Teks -->
    <div class="md:w-1/2 text-center md:text-left space-y-6">
      <h2
        class="sm:text-2xl md:text-4xl lg:text-6xl font-extrabold text-blue-900 leading-tight"
      >
        Multi-Vendor Electronics Marketplace
      </h2>
      <p class="text-gray-600 text-lg md:text-xl">
        Browse Laptops, Smartphones, Electronic Devices, and Many More Directly
        from Trusted Sellers Worldwide
      </p>
      <div class="flex justify-center md:justify-start gap-4 mt-6">
        <button
          class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 transition"
        >
          Shop Now
        </button>
        <button
          class="border border-pink-600 text-pink-600 px-6 py-3 rounded-lg hover:bg-pink-600 hover:text-white transition"
        >
          Open Store Now
        </button>
      </div>
    </div>

    <!-- Kanan: Gambar -->
    <div class="md:w-1/2 lg:w-1/2 flex justify-center mt-10 md:mt-0">
      <img
        :src="hero"
        alt="Marketplace illustration"
        class="w-64 md:w-96 drop-shadow-lg"
      />
    </div>
  </section>
</template>

<style scoped></style>
