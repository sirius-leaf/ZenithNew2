/* =========================================================
   📌 IMPORT — SEMUA ADA DI BAGIAN PALING ATAS
========================================================= */

// Vue core
import { createApp } from "vue";
import { createRouter, createWebHistory } from "vue-router";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

// App utama
import App from "./App.vue";

// Axios
import axios from "axios";

// Global styles
import "./style.css";

// AOS animations
import "aos/dist/aos.css";
import AOS from "aos";

/* =========================================================
   📌 IMPORT HALAMAN PAGES (PUBLIC)
========================================================= */
import HomePage from "./views/pages/Home.vue";
import ProductPage from "./views/pages/Product.vue";
import CategoryPage from "./views/pages/Category.vue";
import AboutPage from "./views/pages/About.vue";
import TestimonialPage from "./views/pages/Testimonials.vue";
import Login from "./views/pages/Login.vue";
import Register from "./views/pages/Register.vue";
import ProductDetail from "./views/pages/ProductDetail.vue";
import CartPage from "./views/pages/Cart.vue";
import CheckoutPage from "./views/pages/Checkout.vue";
import OrderSuccess from "./views/pages/OrderSuccess.vue";
import Profile from "./views/pages/ProfileUser.vue";
import EditProfile from "./views/pages/EditProfile.vue";
import OrderHistory from "./views/pages/OrderHistory.vue";
import DetailToko from "./views/pages/DetailToko.vue";

/* =========================================================
   📌 IMPORT DASHBOARD USER
========================================================= */
import Dashboard from "./views/pages/Dashboard.vue";

/* =========================================================
   📌 IMPORT HALAMAN ADMIN
========================================================= */
import AdminLayout from "./layout/AdminLayout.vue";
import AdminDashboard from "./views/admin/DashboardAdmin.vue";
import KelolaProduk from "./views/admin/KelolaProduk.vue";
import Konfirmasi from "./views/admin/Konfirmasi.vue";

/* =========================================================
   📌 IMPORT HALAMAN MANAGE (SELLER / TOKO)
========================================================= */
import PcBuildIndex from "./views/manage_pages/pc_build/PcBuildIndex.vue";
import PcBuildCreate from "./views/manage_pages/pc_build/PcBuildCreate.vue";
import PcBuildEdit from "./views/manage_pages/pc_build/PcBuildEdit.vue";

import ProdukIndex from "./views/manage_pages/produk/ProdukIndex.vue";
import ProdukCreate from "./views/manage_pages/produk/ProdukCreate.vue";
import ProdukEdit from "./views/manage_pages/produk/ProdukEdit.vue";

import ManageUser from "./views/manage_pages/user/ManageUser.vue";
import CreateToko from "./views/manage_pages/toko/CreateToko.vue";
import SellerRequests from "./views/manage_pages/admin/SellerRequests.vue";

/* =========================================================
   📌 AXIOS GLOBAL CONFIG
========================================================= */
axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.withCredentials = true;

// Interceptor: banned auto logout
axios.interceptors.response.use(null, (error) => {
  if (error.response?.status === 403 && error.response.data?.banned) {
    localStorage.removeItem("auth_token");
    router.push("/login");
    alert("🔒 " + error.response.data.message);
  }
  return Promise.reject(error);
});

/* =========================================================
   📌 ROUTER CONFIG
========================================================= */
const router = createRouter({
  history: createWebHistory(),
  routes: [
    /* ---------- Admin Routes ---------- */
    {
      path: "/admin",
      component: AdminLayout,
      meta: { hideLayout: true },
      children: [
        { path: "", component: AdminDashboard },
        { path: "kelolaproduk", component: KelolaProduk },
        { path: "konfirmasi", component: Konfirmasi },
      ],
    },

    /* ---------- Public Routes ---------- */
    { path: "/", component: HomePage },
    { path: "/product", component: ProductPage },
    {
      path: "/product/:id",
      name: "product-detail",
      component: ProductDetail,
      props: true,
    },
    {
      path: "/toko/:id",
      name: "toko.detail",
      component: DetailToko,
    },
    { path: "/categories/:category", component: CategoryPage, props: true },
    { path: "/about", component: AboutPage },
    { path: "/testimonial", component: TestimonialPage },
    { path: "/profile", component: Profile },
    { path: "/profile/edit", component: EditProfile },
    { path: "/orderHistory", component: OrderHistory },

    { path: "/cart", component: CartPage },
    { path: "/checkout", name: "checkout", component: CheckoutPage },
    {
      path: "/checkout/success",
      name: "checkout.success",
      component: OrderSuccess,
    },

    /* ---------- Dashboard User ---------- */
    {
      path: "/dashboard",
      component: Dashboard,
      children: [
        { path: "manage/users", component: ManageUser },
        { path: "manage/create-toko", component: CreateToko },
        { path: "manage/seller-requests", component: SellerRequests },
      ],
    },
    {
      path: "/dashboard/manage/produk",
      name: "produk.index",
      component: ProdukIndex,
    },
    {
      path: "/dashboard/manage/produk/create",
      name: "produk.create",
      component: ProdukCreate,
    },
    {
      path: "/dashboard/manage/produk/:id/edit",
      name: "produk.edit",
      component: ProdukEdit,
    },

    { path: "/dashboard/manage/pcBuild", component: PcBuildIndex },
    { path: "/dashboard/manage/pcBuild/create", component: PcBuildCreate },
    {
      path: "/dashboard/manage/pcBuild/:id/edit",
      component: PcBuildEdit,
      props: true,
    },

    /* ---------- Auth ---------- */
    { path: "/login", component: Login, meta: { hideLayout: true } },
    { path: "/register", component: Register, meta: { hideLayout: true } },

    /* ---------- 404 Fallback ---------- */
    { path: "/:pathMatch(.*)*", redirect: "/" },
  ],
});

/* =========================================================
   📌 INISIALISASI AOS
========================================================= */
AOS.init();

/* =========================================================
   📌 MOUNT APLIKASI (HARUS PALING BAWAH)
========================================================= */
createApp(App).use(router).use(Toast).mount("#app");
