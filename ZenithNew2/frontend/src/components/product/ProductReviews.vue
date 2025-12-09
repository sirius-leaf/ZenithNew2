<script setup>
import { ref, computed } from "vue";

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

const totalPages = computed(() =>
  Math.ceil(props.reviews.length / reviewsPerPage)
);

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
// Helper to format date
const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const openImage = (path) => {
  const url = path.startsWith("http")
    ? path
    : `http://127.0.0.1:8000/storage/${path}`;
  window.open(url, "_blank");
};

const censorName = (name) => {
  if (!name || name.length <= 2) return name;
  const firstChar = name.charAt(0);
  const lastChar = name.charAt(name.length - 1);
  return `${firstChar}******${lastChar}`;
};
</script>

<template>
  <div class="mt-8">
    <h2 class="text-lg font-bold text-gray-900 mb-4 uppercase">
      Ulasan Pembeli
    </h2>

    <!-- Summary Card -->
    <div
      class="bg-white border border-gray-200 rounded-xl p-6 mb-8 flex flex-col md:flex-row gap-8 items-center"
    >
      <!-- Left: Big Star -->
      <div class="flex flex-col items-center md:items-start min-w-[200px]">
        <div class="flex items-end gap-2 mb-2">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-10 w-10 text-yellow-400"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path
              d="M12 2.5L14.8 9.2L21.5 12L14.8 14.8L12 21.5L9.2 14.8L2.5 12L9.2 9.2L12 2.5Z"
            />
          </svg>
          <span class="text-5xl font-bold text-gray-900">{{
            averageRating.toFixed(1)
          }}</span>
          <span class="text-gray-400 text-xl mb-1">/ 5.0</span>
        </div>
        <p class="font-medium text-gray-900 mb-1">
          {{
            totalReviews > 0
              ? Math.round(
                  ((ratingDistribution[5] + ratingDistribution[4]) /
                    totalReviews) *
                    100
                )
              : 0
          }}% pembeli merasa puas
        </p>
        <p class="text-sm text-gray-500">
          {{ totalReviews }} rating • {{ reviews.length }} ulasan
        </p>
      </div>

      <!-- Right: Distribution -->
      <div class="flex-1 w-full max-w-md">
        <div
          v-for="star in [5, 4, 3, 2, 1]"
          :key="star"
          class="flex items-center gap-2 mb-1"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4 text-yellow-400"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path
              d="M12 2.5L14.8 9.2L21.5 12L14.8 14.8L12 21.5L9.2 14.8L2.5 12L9.2 9.2L12 2.5Z"
            />
          </svg>
          <span class="text-sm text-gray-500 w-3">{{ star }}</span>
          <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
            <div
              class="h-full bg-green-500 rounded-full"
              :style="{
                width:
                  totalReviews > 0
                    ? (ratingDistribution[star] / totalReviews) * 100 + '%'
                    : '0%',
              }"
            ></div>
          </div>
          <span class="text-sm text-gray-500 w-6 text-right"
            >({{ ratingDistribution[star] }})</span
          >
        </div>
      </div>
    </div>

    <!-- Review List -->
    <div class="space-y-6">
      <div
        v-for="review in paginatedReviews"
        :key="review.id_ulasan"
        class="border-b border-gray-100 pb-6 last:border-0"
      >
        <!-- Stars & Time -->
        <div class="flex items-center gap-2 mb-2">
          <div class="flex">
            <svg
              v-for="i in 5"
              :key="i"
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path
                d="M12 2.5L14.8 9.2L21.5 12L14.8 14.8L12 21.5L9.2 14.8L2.5 12L9.2 9.2L12 2.5Z"
              />
            </svg>
          </div>
          <span class="text-xs text-gray-400">{{
            formatDate(review.created_at)
          }}</span>
        </div>

        <!-- User Info -->
        <div class="flex items-center gap-3 mb-2">
          <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden">
            <img
              v-if="review.user?.profile_photo_path"
              :src="
                review.user.profile_photo_path.startsWith('http')
                  ? review.user.profile_photo_path
                  : `http://127.0.0.1:8000/storage/${review.user.profile_photo_path}`
              "
              class="w-full h-full object-cover"
            />
            <svg
              v-else
              class="w-full h-full text-gray-400"
              fill="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
          </div>
          <span class="font-semibold text-sm text-gray-900">{{
            censorName(review.user?.name || "User")
          }}</span>
        </div>

        <!-- Variant Info -->
        <p v-if="review.variant" class="text-xs text-gray-500 mb-3">
          Varian: {{ review.variant.nama_varian }}
        </p>

        <!-- Comment -->
        <p class="text-sm text-gray-800 leading-relaxed mb-3">
          {{ review.komentar }}
        </p>

        <!-- Images -->
        <div
          v-if="review.images && review.images.length > 0"
          class="flex gap-2 overflow-x-auto pb-2"
        >
          <div
            v-for="img in review.images"
            :key="img.id"
            class="w-16 h-16 rounded-md bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0 cursor-pointer hover:opacity-90"
          >
            <img
              :src="
                img.image_path.startsWith('http')
                  ? img.image_path
                  : `http://127.0.0.1:8000/storage/${img.image_path}`
              "
              class="w-full h-full object-cover"
              @click="openImage(img.image_path)"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1"
      class="flex justify-center items-center gap-4 mt-8"
    >
      <button
        @click="prevPage"
        :disabled="currentPage === 1"
        class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Previous
      </button>
      <span class="text-sm text-gray-600"
        >Page {{ currentPage }} of {{ totalPages }}</span
      >
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
