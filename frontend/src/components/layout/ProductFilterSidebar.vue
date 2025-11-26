<!-- src/components/ProductFilterSidebar.vue -->
<template>
  <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <h3 class="font-bold text-gray-800 mb-4">Filter Kategori</h3>
    <div class="space-y-2">
      <button
        v-for="tag in popularTags"
        :key="tag.id"
        @click="selectCategory(tag.name)"
        :class="[
          'px-3 py-2 rounded-md text-sm font-medium transition',
          selectedCategory === tag.name
            ? 'bg-pink-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]"
      >
        {{ tag.name }}
      </button>
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

const selectCategory = (categoryName) => {
  selectedCategory.value = categoryName;
  // Emit event ke parent (Product.vue) jika ingin mengirim filter
  emit('category-selected', categoryName);
};

const emit = defineEmits(['category-selected']);
</script>