<script setup>
const images = [
    {
        image: "testi.png",
        file: "testi.png",
        name: "Raditya Hafid",
        title: "testimoni1",
        desc: "Barang bagus2 dan aku bisa mendapatkan barang yang murah. aku juga buka toko di sini, jangan lupa beli ya!",
    },
    {
        image: "testi.png",
        file: "testi.png",
        name: "Hafiz Ahmad",
        title: "testimoni2",
        desc: "Fiturnya lengkap, ini sangat rekomendasi sih",
    },
    {
        image: "testi.png",
        file: "testi.png",
        name: "Satrio Aji Pamungkas",
        title: "testimoni2",
        desc: "Fiturnya lengkap, ini sangat rekomendasi sih",
    },
];
const getImage = (file) => {
  return new URL(`../../assets/${file}`, import.meta.url).href
}
</script>

<template>
    <section class="py-16 px-6 bg-gray-50">
        <h3 class="text-3xl font-bold text-center mb-10">
            Read What Others Have To Say
        </h3>

        <div class="flex flex-wrap justify-center gap-8">
            <div
                v-for="image in images"
                :key="image.id"
                class="bg-blue-900 text-white rounded-2xl p-6 w-72 text-center"
                data-aos-duration="1300"
                data-aos="fade-left"
            >
                <img
                    :src="getImage(image.file)"
                    class="w-20 h-20 mx-auto rounded-full mb-4"
                    :alt="image.title"
                />
                <h4 class="font-bold mb-2">{{ image.name }}</h4>
                <p class="text-sm text-gray-200">
                    {{ image.desc }}
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped></style>
