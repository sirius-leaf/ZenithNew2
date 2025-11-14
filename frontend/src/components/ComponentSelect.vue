<script setup>
defineProps({
  label: String,
  modelValue: String,
  products: Array,
  error: Array,
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
  <div class="mb-4">
    <label class="block mb-1 font-medium">{{ label }}</label>

    <select
      :value="modelValue"
      @input="emit('update:modelValue', $event.target.value)"
      class="w-full border p-2 rounded"
    >
      <option value="">-- Pilih Komponen --</option>
      <option v-for="p in products" :key="p.id_produk" :value="p.id_produk">
        {{ p.nama_produk }} ({{ p.merek }})
      </option>
    </select>

    <p v-if="error" class="text-red-500 text-sm">{{ error[0] }}</p>
  </div>
</template>
