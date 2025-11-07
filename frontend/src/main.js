import { createApp } from "vue";
import "./style.css";
import App from "./App.vue";
import { createRouter, createWebHistory } from "vue-router";
import HomePage from "./components/Pages/HomePage.vue";
import ProductPage from "./components/Pages/ProductPage.vue";
import CategoryPage from "./components/Pages/CategoryPage.vue";
import AboutPage from "./components/Pages/AboutPage.vue";
import TestimonialPage from "./components/Pages/TestimonialPage.vue";

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
    },
  ],
});

AOS.init();
createApp(App).use(router).mount("#app");
