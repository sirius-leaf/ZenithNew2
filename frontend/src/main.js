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
import Profile from "./views/pages/ProfileUser.vue";


// ✅ Manage pages tetap di lokasi semula
import PcBuildIndex from "./views/manage_pages/pc_build/PcBuildIndex.vue";
import PcBuildCreate from "./views/manage_pages/pc_build/PcBuildCreate.vue";
import PcBuildEdit from "./views/manage_pages/pc_build/PcBuildEdit.vue";
import ManageUser from "./views/manage_pages/user/ManageUser.vue";
import CreateToko from "./views/manage_pages/toko/CreateToko.vue";
import SellerRequests from "./views/manage_pages/admin/SellerRequests.vue";

import "aos/dist/aos.css";
import AOS from "aos";

// Setup global axios
axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.withCredentials = true;

// ✅ Buat router — HARUS sebelum createApp
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomePage,
    },
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
    { path: "/profile", 
      name: "profile", 
      component: Profile },
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
