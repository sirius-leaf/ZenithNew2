<!-- src/layouts/AdminLayout.vue -->
<template>
  <div class="min-h-screen bg-neutral-100">
    <!-- Navbar: fixed agar selalu di atas -->
    <NavbarAdmin class="fixed top-0 left-0 right-0 z-50" />

    <!-- Sidebar: fixed di kiri, mulai tepat di bawah navbar -->
    <SidebarAdmin class="fixed left-0 top-16 z-40" />

    <!-- Konten utama -->
    <main class="ml-72 pt-16 pb-20 pr-6 pl-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto">
        <router-view />
      </div>
    </main>

    <!-- Footer: fixed di bawah (opsional), atau static jika scrollable -->
    <FooterAdmin class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t" />
  </div>
</template>

<script setup>
import NavbarAdmin from '@/components/layout/NavbarAdmin.vue'
import SidebarAdmin from '@/components/layout/SidebarAdmin.vue'
import FooterAdmin from '@/components/layout/FooterAdmin.vue'
</script>