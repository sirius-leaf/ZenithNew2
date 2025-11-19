// src/main.js
import { createApp } from "vue";
import "./style.css";
import App from "./App.vue";
import { createRouter, createWebHistory } from "vue-router";
import axios from "axios";

// ✅ Semua halaman di src/views/pages/
import HomePage from "./views/pages/Home.vue";
import ProductPage from "./views/pages/Product.vue";
import CategoryPage from "./views/pages/Category.vue";
import AboutPage from "./views/pages/About.vue";
import TestimonialPage from "./views/pages/Testimonials.vue";
import Login from "./views/pages/Login.vue";
import ProductDetail from "./views/pages/ProductDetail.vue";
import Register from "./views/pages/Register.vue"; // ✅ Fixed: sesuai struktur folder
import Dashboard from "./views/pages/Dashboard.vue"; // ✅ Fixed: sesuai struktur folder
import CartPage from "./views/pages/Cart.vue";
import CheckoutPage from "./views/pages/Checkout.vue";

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
import ProdukIndex from "./views/manage_pages/produk/ProdukIndex.vue";
import ProdukCreate from "./views/manage_pages/produk/ProdukCreate.vue";
import ProdukEdit from "./views/manage_pages/produk/ProdukEdit.vue";
import ManageUser from "./views/manage_pages/user/ManageUser.vue";
import CreateToko from "./views/manage_pages/toko/CreateToko.vue";
import SellerRequests from "./views/manage_pages/admin/SellerRequests.vue";

// Setup global axios
axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.withCredentials = true;

// 🌿 AOS
import "aos/dist/aos.css";
import AOS from "aos";

// 📡 Axios setup
axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.withCredentials = true;

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
      path: "/product",
      name: "product-list",
      component: ProductPage,
    },
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
      path: "/cart",
      name: "cart-index",
      component: CartPage,
      // meta: { requiresAuth: true }
    },
    {
      path: "/checkout",
      name: "checkout",
      component: CheckoutPage,
      // meta: { requiresAuth: true }
    },
    {
      path: "/about",
      name: "about",
      component: AboutPage,
    },
    {
      path: "/testimonial",
      name: "testimonial",
      component: TestimonialPage,
    },
    {
      path: "/dashboard",
      name: "dashboard",
      component: Dashboard,
      children: [
        {
          path: "manage/users",
          component: ManageUser,
          name: "manage-user",
        },
        {
          path: "manage/create-toko",
          component: CreateToko,
          name: "create-toko",
        },
        {
          path: "manage/seller-requests",
          component: SellerRequests,
          name: "seller-requests",
        },
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
        {
          path: "manage/produk",
          name: "produk.index",
          component: ProdukIndex,
        },
        {
          path: "manage/produk/create",
          name: "produk.create",
          component: ProdukCreate,
        },
        {
          path: "manage/produk/:id/edit",
          name: "produk.edit",
          component: ProdukEdit,
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
    // ✅ Fallback route (opsional tapi direkomendasikan)
    {
      path: "/:pathMatch(.*)*",
      redirect: "/",
    },
  ],
});

// ✅ Inisialisasi AOS
AOS.init();

// ✅ Mount aplikasi — ini HARUS baris terakhir!
createApp(App).use(router).mount("#app");
