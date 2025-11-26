<!-- src/views/pages/Dashboard.vue -->
<script setup>
/* =============================
   IMPORT
============================= */
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useToast } from "vue-toastification";

/* =============================
   STATE & INITIAL
============================= */
const router = useRouter();
const toast = useToast();
const user = ref(null);
const loadingSeller = ref(false);
let role = "";

/* =============================
   DUMMY DATA FOR TAG POPULER & REKOMENDASI
============================= */
// Data Tag Populer
const popularTags = ref([
  { id: 1, name: 'Handphones', image: 'https://via.placeholder.com/80/FFFFFF/000000?text=HP' },
  { id: 2, name: 'Laptops', image: 'https://via.placeholder.com/80/FFFFFF/000000?text=Laptop' },
  { id: 3, name: 'CPU', image: 'https://via.placeholder.com/80/FFFFFF/000000?text=CPU' },
  { id: 4, name: 'Monitors', image: 'https://via.placeholder.com/80/FFFFFF/000000?text=Monitor' },
  { id: 5, name: 'Headphones', image: 'https://via.placeholder.com/80/FFFFFF/000000?text=Headphone' },
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
   REQUEST MENJADI PENJUAL
============================= */
const requestSeller = async () => {
  // 🔥 Validasi profil lengkap dulu
  if (!user.value.no_telpon || !user.value.alamat) {
    toast.error("Isi nomor telepon & alamat terlebih dahulu di Profil.", {
      timeout: 3000,
      closeOnClick: true,
    });

    router.push("/profile/edit");
    return;
  }

  loadingSeller.value = true;

  try {
    const token = localStorage.getItem("authToken");
    if (token && !axios.defaults.headers.common["Authorization"]) {
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }

    // Kirim request menjadi penjual
    const res = await axios.post(
      "http://127.0.0.1:8000/api/manage/become-seller"
    );

    user.value.role = "penjual_pending";
    localStorage.setItem("userRole", "penjual_pending");

    toast.success(res.data?.message ?? "Permintaan berhasil dikirim!", {
      timeout: 2500,
    });
  } catch (error) {
    console.error("Request seller failed:", error);

    if (error.response) {
      const data = error.response.data;

      toast.error(data.message ?? "Terjadi kesalahan.", {
        timeout: 3000,
      });
    } else {
      toast.error("Kesalahan jaringan. Coba lagi.", { timeout: 3000 });
    }
  } finally {
    loadingSeller.value = false;
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
      <!-- Welcome -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">
          Selamat datang, {{ user.name }}
        </h2>
        <p class="text-gray-600">Email: {{ user.email }}</p>
      </div>

      <!-- =======================
        STATUS MENJADI PENJUAL
      ======================== -->
      <section class="mb-8">
        <!-- A. User Biasa -->
        <div
          v-if="user.role === 'user'"
          class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg"
        >
          <h3 class="text-lg font-bold mb-2">Ingin mulai berjualan?</h3>

          <p class="mb-4 opacity-90">
            Daftarkan akun Anda menjadi penjual untuk membuka toko dan menjual
            produk rakitan PC.
          </p>

          <button
            @click="requestSeller"
            :disabled="loadingSeller"
            class="bg-white text-blue-600 px-5 py-2 rounded-lg font-semibold hover:bg-gray-100 transition disabled:opacity-70"
          >
            {{ loadingSeller ? "Memproses..." : "Daftar Menjadi Penjual" }}
          </button>
        </div>

        <!-- B. Status Pending -->
        <div
          v-else-if="user.role === 'penjual_pending'"
          class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 flex items-center gap-4"
        >
          <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">⏳</div>

          <div>
            <h3 class="text-lg font-bold text-yellow-800">Menunggu Konfirmasi</h3>
            <p class="text-yellow-700">
              Permintaan Anda sedang ditinjau oleh Admin.
            </p>
          </div>
        </div>

        <!-- C. Penjual -->
        <div
          v-else-if="user.role === 'penjual'"
          class="bg-green-50 border border-green-200 rounded-xl p-6"
        >
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-lg font-bold text-green-800">
                Panel Penjual Aktif
              </h3>
              <p class="text-green-700">
                Anda memiliki akses penuh untuk mengelola toko.
              </p>
            </div>

            <router-link
              to="/dashboard/manage/create-toko"
              class="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700 transition shadow"
            >
              Kelola / Buka Toko
            </router-link>
          </div>
        </div>
      </section>

      <!-- Modern Section: Tag Populer & Rekomendasi -->
      <section class="mb-8">
        <!-- Tag Populer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
          <h3 class="font-bold text-gray-800 mb-4 text-lg">Tag Populer</h3>
          <div class="relative">
            <!-- Slider Controls -->
            <button class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white p-2 rounded-full shadow-md hover:bg-gray-100 transition" @click="scrollLeft('tagSlider')">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white p-2 rounded-full shadow-md hover:bg-gray-100 transition" @click="scrollRight('tagSlider')">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>

            <!-- Slider Content -->
            <div id="tagSlider" class="flex space-x-6 overflow-x-auto scrollbar-hide py-4 px-8">
              <div v-for="tag in popularTags" :key="tag.id" class="flex-shrink-0 flex flex-col items-center">
                <img :src="tag.image" :alt="tag.name" class="w-16 h-16 object-contain mb-2 rounded-lg" />
                <span class="text-xs font-medium text-gray-700">{{ tag.name }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Rekomendasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-bold text-pink-600 mb-4 text-lg text-center uppercase tracking-wider border-b-2 border-pink-300 pb-2">Rekomendasi</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="product in recommendedProducts" :key="product.id" class="bg-gray-50 rounded-lg p-3 shadow-sm hover:shadow-md transition cursor-pointer">
              <img :src="product.image" :alt="product.name" class="w-full h-32 object-cover rounded-md mb-3" />
              <h4 class="font-semibold text-gray-800 text-sm mb-1 line-clamp-1">{{ product.name }}</h4>
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
