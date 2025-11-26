<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  reviews: {
    type: Array,
    default: () => [],
  },
  averageRating: {
    type: Number,
    default: 0,
  },
  totalReviews: {
    type: Number,
    default: 0,
  },
});

const currentPage = ref(1);
const reviewsPerPage = 5;

// Mock data for distribution (since backend might not provide it yet)
const ratingDistribution = computed(() => {
  const dist = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
  props.reviews.forEach((review) => {
    const rating = Math.round(review.rating);
    if (dist[rating] !== undefined) {
      dist[rating]++;
    }
  });
  return dist;
});

const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * reviewsPerPage;
  const end = start + reviewsPerPage;
  return props.reviews.slice(start, end);
});

const totalPages = computed(() => Math.ceil(props.reviews.length / reviewsPerPage));

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Helper to format date (mock "8 bulan lalu" style if real date is standard ISO)
const formatDate = (dateString) => {
  // Simple mock for "X bulan lalu" or just return date
  return "8 bulan lalu"; // Static for now as requested in design match, or use real logic
};
</script>

<template>
  <div class="mt-8">
    <h2 class="text-lg font-bold text-gray-900 mb-4 uppercase">Ulasan Pembeli</h2>

    <!-- Summary Card -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-8 flex flex-col md:flex-row gap-8 items-center">
      <!-- Left: Big Star -->
      <div class="flex flex-col items-center md:items-start min-w-[200px]">
        <div class="flex items-end gap-2 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z" />
          </svg>
          <span class="text-5xl font-bold text-gray-900">{{ averageRating.toFixed(1) }}</span>
          <span class="text-gray-400 text-xl mb-1">/ 5.0</span>
        </div>
        <p class="font-medium text-gray-900 mb-1">100% pembeli merasa puas</p>
        <p class="text-sm text-gray-500">{{ totalReviews }} rating • {{ reviews.length }} ulasan</p>
      </div>

      <!-- Right: Distribution -->
      <div class="flex-1 w-full max-w-md">
        <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="flex items-center gap-2 mb-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z" />
          </svg>
          <span class="text-sm text-gray-500 w-3">{{ star }}</span>
          <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
            <div
              class="h-full bg-green-500 rounded-full"
              :style="{ width: totalReviews > 0 ? (ratingDistribution[star] / totalReviews) * 100 + '%' : '0%' }"
            ></div>
          </div>
          <span class="text-sm text-gray-500 w-6 text-right">({{ ratingDistribution[star] }})</span>
        </div>
      </div>
    </div>

    <!-- Review List -->
    <div class="space-y-6">
      <div v-for="review in paginatedReviews" :key="review.id" class="border-b border-gray-100 pb-6 last:border-0">
        <!-- Stars & Time -->
        <div class="flex items-center gap-2 mb-2">
          <div class="flex">
            <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 111.838.616l-1.07 3.292a1 1 0 00.919.592l3.181.45a1 1 0 01-.736 1.715l-1.07 3.292a1 1 0 101.838.616l1.07-3.292a1 1 0 01.919.592l1.07 3.292a1 1 0 01-1.028.684H9.049a1 1 0 01-1.028-.684l-1.07-3.292a1 1 0 00-1.028-.684l-3.181-.45a1 1 0 01-.736-1.715l1.07-3.292a1 1 0 10-1.838-.616l1.07 3.292a1 1 0 01-.919.592l-3.181.45z" />
            </svg>
          </div>
          <span class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</span>
        </div>

        <!-- User Info -->
        <div class="flex items-center gap-3 mb-2">
          <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden">
             <!-- Placeholder Avatar -->
             <svg class="w-full h-full text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
          </div>
          <span class="font-semibold text-sm text-gray-900">{{ review.user.name }}</span>
        </div>

        <!-- Variant Info -->
        <p class="text-xs text-gray-500 mb-3">Varian: T490 i5-8th - 8GB / 256GB</p>

        <!-- Comment -->
        <p class="text-sm text-gray-800 leading-relaxed mb-3">
          {{ review.komentar }}
        </p>
        <button class="text-green-600 text-sm font-medium hover:underline mb-3">Selengkapnya</button>

        <!-- Media (Mocked) -->
        <div class="flex gap-2">
          <div class="w-16 h-16 rounded-md bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden relative">
             <div class="absolute inset-0 flex items-center justify-center bg-black/20">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
             </div>
             <!-- Mock Image -->
             <div class="w-full h-full bg-gray-300"></div>
          </div>
          <div v-for="i in 3" :key="i" class="w-16 h-16 rounded-md bg-gray-100 border border-gray-200 overflow-hidden">
             <div class="w-full h-full bg-gray-300"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center items-center gap-4 mt-8">
      <button
        @click="prevPage"
        :disabled="currentPage === 1"
        class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Previous
      </button>
      <span class="text-sm text-gray-600">Page {{ currentPage }} of {{ totalPages }}</span>
      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Next
      </button>
    </div>
  </div>
</template>
