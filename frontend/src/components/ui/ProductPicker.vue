<template>
  <div
    v-if="open"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white w-11/12 max-w-3xl p-6 rounded-xl shadow-lg relative">
      <!-- Tombol Close X -->
      <button
        class="absolute top-2 right-2 text-xl font-bold"
        @click="$emit('close')"
      >
        ✕
      </button>

      <h2 class="text-2xl font-bold mb-4">Pilih {{ label }}</h2>

      <!-- Input Search -->
      <input
        v-model="search"
        type="text"
        placeholder="Cari produk..."
        class="w-full border p-2 rounded mb-4"
      />

      <!-- List Produk -->
      <div
        class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto"
      >
        <div
          v-for="p in filteredProducts"
          :key="p.id_produk"
          @click="chooseProduct(p)"
          class="p-3 border rounded-lg cursor-pointer hover:bg-gray-100"
        >
          <p class="font-semibold">{{ p.nama_produk }}</p>
          <p class="text-sm text-gray-600">{{ p.merek }}</p>
          <p class="text-sm text-gray-500">Rp {{ p.harga }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  open: Boolean,
  label: String,
  products: Array,
  //filterBy: String, // kategori filter, ex: "cpu", "ram"
});

const emit = defineEmits(["close", "select"]);

const search = ref("");

// Filter produk sesuai kategori + search
const filteredProducts = computed(() => {
  return props.products.filter((p) => {
    //const cocokTipe = props.filterBy ? p.kategori === props.filterBy : true;

    const cocokNama = p.nama_produk
      .toLowerCase()
      .includes(search.value.toLowerCase());

    return cocokNama;
    //return cocokTipe && cocokNama;
  });
});

// Ketika user memilih produk
function chooseProduct(product) {
  emit("select", product.id_produk);
  emit("close");
}
</script>
