<!-- src/layouts/AdminLayout.vue -->
<template>
  <div class="min-h-screen flex flex-col bg-neutral-100">
    <NavbarAdmin />
    <div class="flex flex-1 overflow-hidden">
      <SidebarAdmin />
      <main class="flex-1 pl-[288px] pt-6 pr-8 pb-14 overflow-y-auto">
        <!-- Konten dinamis berubah sesuai rute -->
        <router-view />
      </main>
    </div>
    <FooterAdmin />
  </div>
</template>

<script setup>
import NavbarAdmin from '@/components/layout/NavbarAdmin.vue'
import SidebarAdmin from '@/components/layout/SidebarAdmin.vue'
import FooterAdmin from '@/components/layout/FooterAdmin.vue'
</script>