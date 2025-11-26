<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
    <h3 class="font-bold text-gray-800 text-lg mb-4">Kategori</h3>
    <div class="flex flex-col space-y-2">
      <button
        v-for="tag in popularTags"
        :key="tag.id"
        @click="toggleCategory(tag.name)"
        :class="[
          'text-left px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200',
          selectedCategory === tag.name
            ? 'bg-pink-50 text-pink-600'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
        ]"
      >
        {{ tag.name }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  initialCategory: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['category-selected']);

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

const selectedCategory = ref(props.initialCategory);

// Toggle category
const toggleCategory = (categoryName) => {
  if (selectedCategory.value === categoryName) {
    selectedCategory.value = null;
  } else {
    selectedCategory.value = categoryName;
  }
  emit('category-selected', selectedCategory.value);
};
</script>
