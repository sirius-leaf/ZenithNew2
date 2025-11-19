// src/main.js
import { createApp } from "vue";
import "./style.css";
import App from "./App.vue";
import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";

// 🌐 Halaman publik (user biasa)
import HomePage from "./views/pages/Home.vue";
import ProductPage from "./views/pages/Product.vue";
import CategoryPage from "./views/pages/Category.vue";
import AboutPage from "./views/pages/About.vue";
import TestimonialPage from "./views/pages/Testimonials.vue";
import Login from "./views/pages/Login.vue";
import ProductDetail from "./views/pages/ProductDetail.vue";
import Register from "./views/pages/Register.vue";
import Profile from "./views/pages/ProfileUser.vue";
import Dashboard from "./views/pages/Dashboard.vue";
import Cart from "./views/pages/Cart.vue"

// 🔐 Halaman admin — SESUAI STRUKTUR FOLDER ANDA
import AdminDashboard from "./views/admin/DashboardAdmin.vue";
import KelolaProduk from "./views/admin/KelolaProduk.vue"; // ✅ BARU: tambahkan ini
import Konfirmasi from "./views/admin/Konfirmasi.vue"; // ✅ BARU: tambahkan ini

// 🧱 Layout admin
import AdminLayout from "./layout/AdminLayout.vue"; // pastikan file ini ada di src/AdminLayout.vue

// 🛠 Halaman manage (PC Build, dll)
import PcBuildIndex from "./views/manage_pages/pc_build/PcBuildIndex.vue";
import PcBuildCreate from "./views/manage_pages/pc_build/PcBuildCreate.vue";
import PcBuildEdit from "./views/manage_pages/pc_build/PcBuildEdit.vue";

// 🌿 AOS
import "aos/dist/aos.css";
import AOS from "aos";

// 📡 Axios setup
//axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.withCredentials = true;

axios.interceptors.response.use(null, error => {
  if (error.response?.status === 403 && error.response.data?.banned) {
    localStorage.removeItem('auth_token');
    router.push('/login');
    alert('🔒 ' + error.response.data.message);
  }
  return Promise.reject(error);
});

// 🧭 Router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    // 🔐 Admin Routes
    {
      path: "/admin",
      component: AdminLayout,
      meta: { hideLayout: true },
      children: [
        { path: "", component: AdminDashboard }, // ← ini jadi halaman utama admin
        { path: "kelolaproduk", component: KelolaProduk },
        { path: "konfirmasi", component: Konfirmasi },
      ],
    },

    // 🏠 Public Routes
    { path: "/", name: "home", component: HomePage },
    { path: "/product", name: "product-list", component: ProductPage },
    {
      path: "/product/:id",
      name: "product-detail",
      component: ProductDetail,
      props: true,
    },
    {
      path: "/categories/:category",
      name: "category",
      component: CategoryPage,
      props: true,
    },
    { path: "/about", name: "about", component: AboutPage },
    { path: "/testimonial", name: "testimonial", component: TestimonialPage },
    { path: "/profile", name: "profile", component: Profile },
    { path: "/cart", name: "cart", component: Cart },
    {
      path: "/dashboard",
      name: "dashboard",
      component: Dashboard,
      children: [
        {
          path: "manage/pcBuild",
          name: "pc-build.index",
          component: PcBuildIndex,
        },
        {
          path: "manage/pcBuild/create",
          name: "pc-build.create",
          component: PcBuildCreate,
        },
        {
          path: "manage/pcBuild/:id/edit",
          name: "pc-build.edit",
          component: PcBuildEdit,
          props: true,
        },
      ],
    },
    {
      path: "/register",
      name: "register",
      component: Register,
      meta: { hideLayout: true },
    },
    {
      path: "/login",
      name: "login",
      component: Login,
      meta: { hideLayout: true },
    },

    // ❓ Fallback
    { path: "/:pathMatch(.*)*", redirect: "/" },
  ],
});

AOS.init();

createApp(App).use(router).mount("#app");
