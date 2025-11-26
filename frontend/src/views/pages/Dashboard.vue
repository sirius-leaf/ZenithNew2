<!-- src/views/pages/Dashboard.vue -->
<script setup>
/* =============================
   IMPORT
============================= */
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "vue-toastification";

/* =============================
   STATE & INITIAL
============================= */
const router = useRouter();
const toast = useToast();
const user = ref(null);
let role = "";

/* =============================
   DUMMY DATA FOR TAG POPULER & REKOMENDASI
============================= */
// Data Tag Populer
const popularTags = ref([
  { id: 1, name: 'Handphones', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>' },
  { id: 2, name: 'Laptops', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>' },
  { id: 3, name: 'CPU', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>' },
  { id: 4, name: 'Monitors', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>' },
  { id: 5, name: 'Headphones', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>' },
]);

// Data Rekomendasi Produk
const recommendedProducts = ref([
  {
    id: 1,
    name: 'Headphone Rapa',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'RAPA AIR',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Headphone'
  },
  {
    id: 2,
    name: 'Samsung Galaxy A55',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'Samsung Indonesia',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Galaxy+A55'
  },
  {
    id: 3,
    name: 'Samsung Galaxy A55',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'Samsung Indonesia',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Galaxy+A55'
  },
  {
    id: 4,
    name: 'Samsung Galaxy A55',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'Samsung Indonesia',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Galaxy+A55'
  },
  {
    id: 5,
    name: 'Headphone Rapa',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'RAPA AIR',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Headphone'
  },
  {
    id: 6,
    name: 'Samsung Galaxy A55',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'Samsung Indonesia',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Galaxy+A55'
  },
  {
    id: 7,
    name: 'Samsung Galaxy A55',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'Samsung Indonesia',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Galaxy+A55'
  },
  {
    id: 8,
    name: 'Samsung Galaxy A55',
    price: 'Rp 4.599.000',
    rating: 4.8,
    brand: 'Samsung Indonesia',
    image: 'https://via.placeholder.com/200/FFFFFF/000000?text=Galaxy+A55'
  },
]);

/* =============================
   PAGINATION LOGIC
============================= */
const currentPage = ref(1);
const itemsPerPage = 8;

const totalPages = computed(() => Math.ceil(recommendedProducts.value.length / itemsPerPage));

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return recommendedProducts.value.slice(start, end);
});

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

/* =============================
   LOAD USER SAAT HALAMAN DIBUKA
============================= */
onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  try {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    const res = await axios.get("http://127.0.0.1:8000/api/user");

    user.value = res.data;

    if (res.data.role) {
      localStorage.setItem("userRole", res.data.role);
      role = res.data.role;
    }

    // Fetch Products
    const productRes = await axios.get("http://127.0.0.1:8000/api/products");
    if (productRes.data && productRes.data.data) {
      recommendedProducts.value = productRes.data.data.map((product) => {
        const variant = product.variant && product.variant.length > 0 ? product.variant[0] : null;
        let image = "https://via.placeholder.com/200/FFFFFF/000000?text=No+Image";
        let price = "Rp 0";

        if (variant) {
          // Handle image path
          if (variant.gambar_varian) {
             // Check if it starts with http or https
             if (variant.gambar_varian.startsWith('http')) {
                 image = variant.gambar_varian;
             } else {
                 // Remove leading ../ if present and prepend base URL
                 const cleanPath = variant.gambar_varian.replace(/^\.\.\//, '');
                 image = `http://127.0.0.1:8000/${cleanPath}`;
             }
          }
          price = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
          }).format(variant.harga);
        }

        return {
          id: product.id_produk,
          name: product.nama_produk,
          price: price,
          rating: 0, // Default rating as it's not in the main list API
          brand: product.merek,
          image: image,
        };
      });
    }

  } catch (error) {
    console.error("Gagal mengambil data:", error);
    // Don't force logout on product fetch fail, only on user fetch fail if 401
    if (error.response && error.response.status === 401) {
        localStorage.removeItem("authToken");
        localStorage.removeItem("userRole");
        router.push("/login");
    }
  }
});

/* =============================
   SCROLL FUNCTIONS FOR TAG SLIDER
============================= */
const scrollLeft = (sliderId) => {
  const slider = document.getElementById(sliderId);
  if (slider) {
    slider.scrollBy({ left: -200, behavior: 'smooth' });
  }
};

const scrollRight = (sliderId) => {
  const slider = document.getElementById(sliderId);
  if (slider) {
    slider.scrollBy({ left: 200, behavior: 'smooth' });
  }
};



/* =============================
   LOGOUT HANDLER
============================= */
const handleLogout = () => {
  localStorage.removeItem("authToken");
  localStorage.removeItem("userRole");
  delete axios.defaults.headers.common["Authorization"];
  router.push("/login");
};
</script>

<template>
  <div v-if="user" class="min-h-screen bg-gray-50">
    <main class="px-4 py-6 md:px-6 lg:px-8 max-w-7xl mx-auto w-full">


      <!-- Modern Section: Tag Populer & Rekomendasi -->
      <section class="mb-8">
        <!-- Tag Populer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
          <h3 class="font-bold text-gray-800 mb-6 text-lg">Tag Populer</h3>
          <div class="flex flex-wrap justify-center gap-6">
            <div v-for="tag in popularTags" :key="tag.id" class="flex flex-col items-center group cursor-pointer">
              <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-2 group-hover:bg-pink-50 group-hover:scale-110 transition-all duration-300 shadow-sm" v-html="tag.icon">
              </div>
              <span class="text-sm font-medium text-gray-600 group-hover:text-pink-600 transition-colors">{{ tag.name }}</span>
            </div>
          </div>
        </div>

        <!-- Rekomendasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-bold text-pink-600 mb-4 text-lg text-center uppercase tracking-wider border-b-2 border-pink-300 pb-2">Rekomendasi</h3>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div v-for="product in paginatedProducts" :key="product.id" class="bg-gray-50 rounded-lg p-3 shadow-sm hover:shadow-md transition cursor-pointer group">
              <div class="relative overflow-hidden rounded-md mb-3">
                <img :src="product.image" :alt="product.name" class="w-full h-32 object-cover transform group-hover:scale-110 transition-transform duration-500" />
              </div>
              <h4 class="font-semibold text-gray-800 text-sm mb-1 line-clamp-1 group-hover:text-pink-600 transition-colors">{{ product.name }}</h4>
              <p class="text-pink-600 font-bold mb-1">{{ product.price }}</p>
              <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 001.028.684l3.181.45a1 1 0 00.919-.592l1.07-3.292a1 1 0 00-1.028-.684H9.049a1 1 0 00-1.028.684L7.95 6.316a1 1 0 00.919.592l3.181.45a1 1 0 001.028-.684l1.07-3.292a1 1 0 00-1.028-.684H9.049z" />
                </svg>
                <span class="text-xs text-gray-600 ml-1">{{ product.rating }}</span>
              </div>
              <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs text-gray-600 ml-1">{{ product.brand }}</span>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div class="flex justify-center items-center gap-4" v-if="totalPages > 1">
            <button 
              @click="prevPage" 
              :disabled="currentPage === 1"
              class="px-4 py-2 rounded-lg bg-white border border-pink-200 text-pink-600 hover:bg-pink-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium flex items-center gap-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Prev
            </button>
            
            <div class="flex gap-2">
              <button 
                v-for="page in totalPages" 
                :key="page"
                @click="currentPage = page"
                :class="[
                  'w-8 h-8 rounded-lg flex items-center justify-center font-medium transition-colors',
                  currentPage === page 
                    ? 'bg-gradient-to-r from-pink-500 to-blue-500 text-white shadow-md' 
                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
            </div>

            <button 
              @click="nextPage" 
              :disabled="currentPage === totalPages"
              class="px-4 py-2 rounded-lg bg-white border border-blue-200 text-blue-600 hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium flex items-center gap-2"
            >
              Next
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </section>

      <!-- Router View -->
      <div
        v-if="$route.path !== '/dashboard'"
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <router-view />
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
::-webkit-scrollbar {
  display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
html {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}

/* Optional: Add a subtle animation on hover for products */
.bg-gray-50:hover {
  background-color: #f9fafb;
}
</style>
