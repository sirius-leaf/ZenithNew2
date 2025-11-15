<script setup></script>
<script setup>
import { useRoute } from "vue-router";

const route = useRoute();
const category = route.params.category;

// Contoh: di sini kamu bisa fetch data produk berdasarkan kategori
// Misal ambil dari API atau data lokal
</script>

<template>
  <section class="py-16 px-6">
    <h2 class="text-3xl font-bold text-center mb-10">
      Category: {{ category.toUpperCase() }}
    </h2>

    <!-- contoh konten dummy -->
    <div class="text-center text-gray-700">
      <p>
        Daftar produk untuk kategori <b>{{ category }}</b> akan tampil di sini.
      </p>
    </div>
  </section>
</template>

<style scoped></style>
