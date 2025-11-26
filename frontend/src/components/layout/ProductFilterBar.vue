<!-- src/components/ProductFilterBar.vue -->
<template>
  <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-col md:flex-row gap-4 items-center">
    <!-- Judul Filter -->
    <h3 class="font-bold text-gray-800 md:text-lg">Filter Kategori</h3>

    <!-- Container Kategori (Flex Wrap) -->
    <div class="flex flex-wrap gap-2 flex-grow">
      <button
        v-for="tag in popularTags"
        :key="tag.id"
        @click="toggleCategory(tag.name)"
        :class="[
          'px-3 py-2 rounded-md text-sm font-medium transition',
          selectedCategory === tag.name
            ? 'bg-pink-600 text-white'
            : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200'
        ]"
      >
        {{ tag.name }}
      </button>
    </div>

    <!-- Dropdown Urutkan Harga -->
    <div class="ml-auto">
      <label class="block text-xs font-medium text-gray-700 mb-1">Urutkan Harga:</label>
      <select
        v-model="sortByPrice"
        @change="sortProducts"
        class="w-full md:w-40 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm"
      >
        <option value="">Default</option>
        <option value="asc">Terendah → Tertinggi</option>
        <option value="desc">Tertinggi → Terendah</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Data statis berdasarkan gambar "Tag Populer"
const popularTags = ref([
  { id: 1, name: 'Iphone' },
  { id: 2, name: 'CPU' },
  { id: 3, name: 'Tablet' },
  { id: 4, name: 'Keyboard' },
  { id: 5, name: 'Monitor' },
  { id: 6, name: 'Sound' },
  { id: 7, name: 'Motherboard' },
  { id: 8, name: 'Storage' },
  { id: 9, name: 'Handphone' }
]);

const selectedCategory = ref(null);
const sortByPrice = ref('');

// Toggle category: jika sudah dipilih, hapus filter; jika belum, set filter.
const toggleCategory = (categoryName) => {
  if (selectedCategory.value === categoryName) {
    // Jika sudah dipilih, hapus filter (set null)
    selectedCategory.value = null;
  } else {
    // Jika belum, set sebagai filter
    selectedCategory.value = categoryName;
  }
  emit('category-selected', selectedCategory.value);
};

const sortProducts = () => {
  emit('sort-changed', sortByPrice.value);
};

const emit = defineEmits(['category-selected', 'sort-changed']);
</script>