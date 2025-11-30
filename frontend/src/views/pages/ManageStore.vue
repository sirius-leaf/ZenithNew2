<template>
  <div class="min-h-screen bg-gray-50 font-ubuntu">
    <!-- Header with Store Profile -->
    <div class="bg-white border-b border-gray-100 pb-8 pt-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="flex flex-col items-center justify-center">
          <div
            class="w-24 h-24 rounded-full overflow-hidden border-4 border-pink-50 shadow-sm mb-4"
          >
            <img
              :src="getStorePhoto(user)"
              alt="Store Profile"
              class="w-full h-full object-cover"
            />
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">
            {{ user?.store_name || "Nama Toko" }}
          </h1>
          <p class="text-gray-500 max-w-2xl mx-auto">
            {{ user?.description || "Deskripsi toko belum diatur." }}
          </p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Products Table -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div
          class="px-6 py-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4"
        >
          <h2 class="text-xl font-bold text-gray-800">Daftar Produk</h2>

          <div class="flex items-center gap-3 w-full sm:w-auto">
            <!-- Search Bar -->
            <div class="relative flex-grow sm:flex-grow-0">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari produk..."
                class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 w-full sm:w-64 transition-all"
              />
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>

            <!-- Tambah Produk Button -->
            <button
              @click="openAddModal"
              class="px-5 py-2.5 bg-pink-600 text-white rounded-xl hover:bg-pink-700 transition-colors shadow-sm shadow-pink-200 flex items-center gap-2 whitespace-nowrap font-medium"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              Tambah Produk
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-600">
            <thead
              class="bg-gray-50/50 text-gray-700 font-semibold uppercase tracking-wider"
            >
              <tr>
                <th class="px-6 py-4 rounded-tl-2xl">Produk</th>
                <th class="px-6 py-4">Harga</th>
                <th class="px-6 py-4">Stok</th>
                <th class="px-6 py-4">Terjual</th>
                <th class="px-6 py-4 text-right rounded-tr-2xl">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="loading">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                  Memuat produk...
                </td>
              </tr>
              <tr v-else-if="filteredProducts.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                  Belum ada produk.
                </td>
              </tr>
              <tr
                v-for="product in filteredProducts"
                :key="product.id_produk"
                class="hover:bg-gray-50/80 transition-colors group"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center gap-4">
                    <div
                      class="w-14 h-14 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100"
                    >
                      <img
                        :src="getProductImage(product)"
                        alt="Product"
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div>
                      <div
                        class="font-semibold text-gray-900 line-clamp-1 group-hover:text-pink-600 transition-colors"
                      >
                        {{ product.nama_produk }}
                      </div>
                      <div class="text-xs text-gray-500 mt-0.5">
                        {{ product.merek }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 font-medium text-gray-900">
                  Rp
                  {{
                    Number(product.variant?.[0]?.harga ?? 0).toLocaleString(
                      "id-ID"
                    )
                  }}
                </td>
                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-medium text-xs"
                  >
                    {{ product.variant?.[0]?.stok ?? 0 }}
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-500">
                  {{ product.terjual || 0 }}
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="relative">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="openDetailModal(product)"
                        class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-md flex items-center gap-1"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-4 w-4 text-gray-400"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                          />
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                          />
                        </svg>
                        Detail
                      </button>
                      <button
                        @click="openEditModal(product)"
                        class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-md flex items-center gap-1 font-medium"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-4 w-4"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                          />
                        </svg>
                        Edit
                      </button>
                      <button
                        @click="deleteProduct(product.id_produk)"
                        class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md flex items-center gap-1 font-medium"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-4 w-4"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          />
                        </svg>
                        Hapus
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          class="px-6 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50/30"
          v-if="filteredProducts.length > 0"
        >
          <span class="text-sm text-gray-500"
            >Menampilkan {{ filteredProducts.length }} produk</span
          >
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
      <div
        class="bg-white rounded-2xl shadow-xl max-w-6xl w-full p-6 transform transition-all scale-100 max-h-[90vh] overflow-y-auto"
      >
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">
            {{ isEditing ? "Edit Produk" : "Tambah Produk Baru" }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveProduct">
          <div class="space-y-6">
            <!-- Informasi Utama -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
              <h4
                class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4"
              >
                Informasi Produk
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Nama Produk</label
                  >
                  <input
                    v-model="form.nama_produk"
                    type="text"
                    required
                    placeholder="Contoh: Laptop Gaming Zenith X1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Merek</label
                  >
                  <input
                    v-model="form.merek"
                    type="text"
                    required
                    placeholder="Contoh: Zenith"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition"
                  />
                </div>
                <div>
                  <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-medium text-gray-700"
                      >Kategori</label
                    >
                    <button
                      type="button"
                      @click="addCategory"
                      class="text-xs text-pink-600 font-medium hover:text-pink-700"
                    >
                      + Tambah
                    </button>
                  </div>
                  <div class="space-y-2">
                    <div
                      v-for="(catItem, index) in form.kategori"
                      :key="index"
                      class="flex gap-2"
                    >
                      <select
                        v-model="catItem.id_kategori"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition bg-white"
                      >
                        <option value="" disabled>Pilih Kategori</option>
                        <option
                          v-for="cat in categories"
                          :key="cat.id_kategori"
                          :value="cat.id_kategori"
                        >
                          {{ cat.nama_kategori }}
                        </option>
                      </select>
                      <button
                        v-if="form.kategori.length > 1"
                        type="button"
                        @click="removeCategory(index)"
                        class="text-red-500 hover:text-red-700 px-2"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-5 w-5"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Deskripsi</label
                  >
                  <textarea
                    v-model="form.deskripsi"
                    rows="3"
                    placeholder="Jelaskan detail produkmu..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none transition"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Varian Produk -->
            <div>
              <div class="flex justify-between items-center mb-4">
                <h4
                  class="text-sm font-bold text-gray-800 uppercase tracking-wider"
                >
                  Varian Produk
                </h4>
                <button
                  type="button"
                  @click="addVariant"
                  class="text-sm text-pink-600 font-medium hover:text-pink-700 flex items-center gap-1"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4v16m8-8H4"
                    />
                  </svg>
                  Tambah Varian
                </button>
              </div>

              <div class="space-y-4">
                <div
                  v-for="(variant, index) in form.variants"
                  :key="index"
                  class="bg-white border border-gray-200 rounded-xl p-4 relative group hover:border-pink-200 transition-colors"
                >
                  <button
                    v-if="form.variants.length > 1"
                    type="button"
                    @click="removeVariant(index)"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>

                  <div
                    class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start"
                  >
                    <!-- Image Upload -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-xs font-medium text-gray-500 mb-1"
                        >Foto Varian</label
                      >
                      <div
                        class="relative w-full aspect-square bg-gray-50 rounded-lg border border-dashed border-gray-300 hover:border-pink-400 transition-colors overflow-hidden flex items-center justify-center cursor-pointer group-image"
                      >
                        <img
                          v-if="variant.gambar_preview"
                          :src="variant.gambar_preview"
                          class="w-full h-full object-cover"
                        />
                        <div v-else class="text-center p-2">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 mx-auto text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                          </svg>
                          <span class="text-[10px] text-gray-500 mt-1 block"
                            >Upload</span
                          >
                        </div>
                        <input
                          type="file"
                          @change="(e) => handleFileChange(e, index)"
                          accept="image/*"
                          class="absolute inset-0 opacity-0 cursor-pointer"
                        />
                      </div>
                    </div>

                    <!-- Variant Details -->
                    <div
                      class="md:col-span-10 grid grid-cols-1 md:grid-cols-3 gap-4"
                    >
                      <div>
                        <label
                          class="block text-xs font-medium text-gray-500 mb-1"
                          >Nama Varian</label
                        >
                        <input
                          v-model="variant.nama_varian"
                          type="text"
                          placeholder="Contoh: Merah, XL, 256GB"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none text-sm"
                        />
                      </div>
                      <div>
                        <label
                          class="block text-xs font-medium text-gray-500 mb-1"
                          >Harga (Rp)</label
                        >
                        <input
                          v-model="variant.harga"
                          type="number"
                          min="0"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none text-sm"
                        />
                      </div>
                      <div>
                        <label
                          class="block text-xs font-medium text-gray-500 mb-1"
                          >Stok</label
                        >
                        <input
                          v-model="variant.stok"
                          type="number"
                          min="0"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#e84797] focus:border-transparent outline-none text-sm"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 flex gap-3 pt-6 border-t border-gray-100">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="loadingSubmit"
              class="flex-1 px-4 py-2.5 bg-[#e84797] text-white font-medium rounded-xl hover:bg-[#d03a84] transition shadow-sm disabled:opacity-70 flex justify-center items-center shadow-pink-200"
            >
              <span v-if="loadingSubmit" class="mr-2 animate-spin">⌛</span>
              {{ isEditing ? "Simpan Perubahan" : "Tambah Produk" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Detail Modal -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
      <div
        class="bg-white rounded-2xl shadow-xl max-w-3xl w-full p-6 transform transition-all scale-100 max-h-[90vh] overflow-y-auto"
      >
        <div class="flex justify-between items-start mb-6">
          <div>
            <h3 class="text-xl font-bold text-gray-900">
              {{ selectedProduct?.nama_produk }}
            </h3>
            <p class="text-sm text-gray-500">{{ selectedProduct?.merek }}</p>
          </div>
          <button
            @click="closeDetailModal"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <div class="space-y-6">
          <!-- Description -->
          <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
            <h4
              class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-2"
            >
              Deskripsi
            </h4>
            <p class="text-gray-600 whitespace-pre-line">
              {{ selectedProduct?.deskripsi || "Tidak ada deskripsi." }}
            </p>
          </div>

          <!-- Variants -->
          <div>
            <h4
              class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3"
            >
              Varian Produk
            </h4>
            <div class="overflow-hidden border border-gray-200 rounded-xl">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Foto
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Nama Varian
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Harga
                    </th>
                    <th
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Stok
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="variant in selectedProduct?.variant"
                    :key="variant.id_varian"
                  >
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div
                        class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 border border-gray-200"
                      >
                        <img
                          v-if="variant.gambar_varian"
                          :src="`http://127.0.0.1:8000/storage/${variant.gambar_varian}`"
                          class="w-full h-full object-cover"
                        />
                        <div
                          v-else
                          class="w-full h-full flex items-center justify-center text-gray-400"
                        >
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                          >
                            <path
                              fill-rule="evenodd"
                              d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                              clip-rule="evenodd"
                            />
                          </svg>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                      {{ variant.nama_varian }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                      Rp {{ Number(variant.harga).toLocaleString("id-ID") }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                      {{ variant.stok }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="mt-8 flex justify-end">
          <button
            @click="closeDetailModal"
            class="px-6 py-2 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from "vue";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();

// State
const products = ref([]);
const categories = ref([]);
const user = ref(null);
const loading = ref(true);
const searchQuery = ref("");

// Modal State
const showModal = ref(false);
const showDetailModal = ref(false);
const isEditing = ref(false);
const loadingSubmit = ref(false);
const selectedProduct = ref(null);

// Form State
const form = ref({
  id: null,
  nama_produk: "",
  merek: "",
  deskripsi: "",
  kategori: [], // Array of { id_kategori: '' }
  category_detail_id: null,
  variants: [], // Array of { id_varian, nama_varian, harga, stok, gambar_varian, gambar_preview }
});

onMounted(() => {
  fetchData();
});

// Fetch Data
const fetchData = async () => {
  loading.value = true;
  try {
    const [productsRes, categoriesRes, userRes] = await Promise.all([
      axios.get("/manage/product"),
      axios.get("/categories"),
      axios.get("/profile"),
    ]);

    products.value = productsRes.data.data;
    categories.value = categoriesRes.data.data;
    user.value = userRes.data;
  } catch (error) {
    console.error("Error fetching data:", error);
    toast.error("Gagal memuat data produk");
  } finally {
    loading.value = false;
  }
};

// Computed
const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value;
  const query = searchQuery.value.toLowerCase();
  return products.value.filter(
    (p) =>
      p.nama_produk.toLowerCase().includes(query) ||
      p.merek.toLowerCase().includes(query)
  );
});

// Detail Modal Actions
const openDetailModal = (product) => {
  selectedProduct.value = product;
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  selectedProduct.value = null;
};

// Modal Actions
const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    nama_produk: "",
    merek: "",
    deskripsi: "",
    kategori: [{ id_kategori: "" }],
    category_detail_id: null,
    variants: [
      {
        id_varian: null,
        nama_varian: "Standard",
        harga: 0,
        stok: 0,
        gambar_varian: null,
        gambar_preview: null,
      },
    ],
  };
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditing.value = true;

  const categoryDetail =
    product.category_detail && product.category_detail.length > 0
      ? product.category_detail.map((d) => ({ id_kategori: d.id_kategori }))
      : [{ id_kategori: "" }];

  const categoryDetailId = null; // Not needed for sync logic anymore

  // Map variants
  const variants = (product.variant || []).map((v) => ({
    id_varian: v.id_varian,
    nama_varian: v.nama_varian,
    harga: v.harga,
    stok: v.stok,
    gambar_varian: null, // Don't prepopulate file input
    gambar_preview: v.gambar_varian
      ? `http://127.0.0.1:8000/storage/${v.gambar_varian}`
      : null,
  }));

  if (variants.length === 0) {
    variants.push({
      id_varian: null,
      nama_varian: "Standard",
      harga: 0,
      stok: 0,
      gambar_varian: null,
      gambar_preview: null,
    });
  }

  form.value = {
    id: product.id_produk,
    nama_produk: product.nama_produk,
    merek: product.merek,
    deskripsi: product.deskripsi,
    kategori: categoryDetail,
    category_detail_id: categoryDetailId,
    variants: variants,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

// Variant Actions
const addVariant = () => {
  form.value.variants.push({
    id_varian: null,
    nama_varian: "",
    harga: 0,
    stok: 0,
    gambar_varian: null,
    gambar_preview: null,
  });
};

const removeVariant = (index) => {
  form.value.variants.splice(index, 1);
};

const addCategory = () => {
  form.value.kategori.push({ id_kategori: "" });
};

const removeCategory = (index) => {
  form.value.kategori.splice(index, 1);
};

const handleFileChange = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    form.value.variants[index].gambar_varian = file;
    form.value.variants[index].gambar_preview = URL.createObjectURL(file);
  }
};

const saveProduct = async () => {
  if (
    !form.value.nama_produk ||
    form.value.kategori.length === 0 ||
    !form.value.kategori[0].id_kategori
  ) {
    toast.warning("Mohon lengkapi data wajib (Nama Produk, Kategori)");
    return;
  }

  // Validate variants
  for (const v of form.value.variants) {
    if (!v.nama_varian || v.harga < 0 || v.stok < 0) {
      toast.warning("Mohon lengkapi data varian dengan benar");
      return;
    }
    // For new variants, image is required. For existing, it's optional (if not changed)
    if (!v.id_varian && !v.gambar_varian) {
      toast.warning(`Mohon upload gambar untuk varian "${v.nama_varian}"`);
      return;
    }
  }

  loadingSubmit.value = true;
  try {
    const formData = new FormData();

    if (isEditing.value) {
      formData.append("_method", "PUT");
    }

    formData.append("nama_produk", form.value.nama_produk);
    formData.append("merek", form.value.merek);
    formData.append("deskripsi", form.value.deskripsi || "");

    // Handle Category
    form.value.kategori.forEach((cat, index) => {
      formData.append(`kategori[${index}]`, cat.id_kategori);
    });

    // Handle Variants
    form.value.variants.forEach((v, index) => {
      if (v.id_varian) {
        formData.append(`varian[${index}][id_varian]`, v.id_varian);
      }
      formData.append(`varian[${index}][nama_varian]`, v.nama_varian);
      formData.append(`varian[${index}][harga]`, v.harga);
      formData.append(`varian[${index}][stok]`, v.stok);

      if (v.gambar_varian) {
        formData.append(`varian[${index}][gambar_varian]`, v.gambar_varian);
      }
    });

    if (isEditing.value) {
      await axios.post(`/manage/product/${form.value.id}`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      toast.success("Produk berhasil diperbarui");
    } else {
      await axios.post("/manage/product", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      toast.success("Produk berhasil ditambahkan");
    }

    closeModal();
    fetchData(); // Refresh list
  } catch (error) {
    console.error("Error saving product:", error);
    toast.error(error.response?.data?.message || "Gagal menyimpan produk");
  } finally {
    loadingSubmit.value = false;
  }
};

const deleteProduct = async (id) => {
  if (!confirm("Apakah Anda yakin ingin menghapus produk ini?")) return;

  try {
    await axios.delete(`/manage/product/${id}`);
    toast.success("Produk berhasil dihapus");
    fetchData();
  } catch (error) {
    console.error("Error deleting product:", error);
    toast.error("Gagal menghapus produk");
  }
};

// Helper for image
const getProductImage = (product) => {
  if (
    product.variant &&
    product.variant.length > 0 &&
    product.variant[0].gambar_varian
  ) {
    return `http://127.0.0.1:8000/storage/${product.variant[0].gambar_varian}`;
  }
  return "https://via.placeholder.com/50";
};

const getStorePhoto = (user) => {
  if (user?.store_photo) {
    return `http://127.0.0.1:8000/storage/${user.store_photo}`;
  }
  return "https://via.placeholder.com/150?text=Store";
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap");

.font-ubuntu {
  font-family: "Ubuntu", sans-serif;
}
</style>
