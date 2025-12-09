<template>
  <div class="bg-white rounded-xl border-2 border-blue-500 p-6 h-fit">
    <h3 class="font-bold text-gray-800 text-lg mb-4">Kategori</h3>
    <div class="flex flex-col space-y-2">
      <button
        v-for="category in categories"
        :key="category.id_kategori"
        @click="toggleCategory(category.nama_kategori)"
        :class="[
          'text-left px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 cursor-pointer',
          selectedCategory === category.nama_kategori
            ? 'bg-pink-500 text-white'
            : 'text-gray-600 hover:bg-pink-500 hover:text-white'
        ]"
      >
        {{ category.nama_kategori }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  initialCategory: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['category-selected']);

const categories = ref([]);
const selectedCategory = ref(props.initialCategory);

const fetchCategories = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/categories');
    if (response.data && response.data.data) {
      categories.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
};

onMounted(() => {
  fetchCategories();
});

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
