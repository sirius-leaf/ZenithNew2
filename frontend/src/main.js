import { createApp } from "vue";
import "./style.css";
import App from "./App.vue";
import { createRouter, createWebHistory } from "vue-router";
import HomePage from "./components/Pages/HomePage.vue";
import ProductPage from "./components/Pages/ProductPage.vue";
import CategoryPage from "./components/Pages/CategoryPage.vue";
import AboutPage from "./components/Pages/AboutPage.vue";
import TestimonialPage from "./components/Pages/TestimonialPage.vue";
import Login from "./components/Pages/Login.vue";
import ProductDetail from "./components/Pages/ProductDetail.vue";

import "aos/dist/aos.css";
import AOS from "aos";
import Register from "./views/Register.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "",
      component: HomePage,
    },
    {
      path: "/product/",
      component: ProductPage,
      name: "product-list",
    },
    
    {
      // Path ini akan menangani URL seperti /product/1, /product/4, dll.
      path: "/product/:id", 
      component: ProductDetail, // Komponen yang akan ditampilkan
      name: "product-detail", // Nama yang dicari oleh <router-link> Anda
    },

    {
      path: "/categories/:category",
      component: CategoryPage,
    },
    {
      path: "/about",
      component: AboutPage,
    },
    {
      path: "/testimonial",
      component: TestimonialPage,
    },

    {
      path: "/register",
      component: Register,
      meta: { hideLayout: true },
    },

    {
      path: "/login",
      component: Login,
      meta: { hideLayout: true },
    },
  ],
});

AOS.init();
createApp(App).use(router).mount("#app");
