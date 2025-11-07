<script setup>
const img = "/src/assets/logo.png";
</script>

<template>
    <footer class="bg-white text-blue-900 py-10 px-6 text-center md:text-left">
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div>
                <img :src="img" alt="" />
                <p class="text-sm py-4">
                    Jl. Slamet Riyadi, Surakarta, Jawa Tengah, Indonesia
                </p>
            </div>

            <div>
                <h4 class="font-bold mb-2">Links</h4>
                <ul class="space-y-1 text-pink-500">
                    <li><a href="#" class="hover:underline">Home</a></li>
                    <li><a href="#" class="hover:underline">Category</a></li>
                    <li><a href="#" class="hover:underline">About Us</a></li>
                    <li><a href="#" class="hover:underline">Testimoni</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold mb-2">Contact</h4>
                <ul class="space-y-1 text-pink-500">
                    <li><a href="#" class="hover:underline">Email</a></li>
                    <li><a href="#" class="hover:underline">LinkedIn</a></li>
                    <li><a href="#" class="hover:underline">Instagram</a></li>
                    <li><a href="#" class="hover:underline">Twitter</a></li>
                </ul>
            </div>

            <p class="text-xs text-blue-950">
                Copyright ©2025 ZENITH, All Rights Reserved
            </p>
        </div>
    </footer>
</template>
