div
<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";

import ProductPicker from "../../../components/ui/ProductPicker.vue";

const route = useRoute();
const router = useRouter();

const id = route.params.id;

const isLoading = ref(false);
const products = ref([]);
const variants = ref([]);
const form = ref({
  nama_build: "",
  komponen: {
    motherboard: { id: null, produk: null },
    cpu: { id: null, produk: null },
    ram: { id: null, produk: null },
    psu: { id: null, produk: null },
    storage: { id: null, produk: null },
    cooler: { id: null, produk: null },
    "video-card": { id: null, produk: null },
    case: { id: null, produk: null },
    monitor: { id: null, produk: null },
    mouse: { id: null, produk: null },
    keyboard: { id: null, produk: null },
  },
});

const totalHarga = ref(0);

const modal = ref({
  motherboard: false,
  cpu: false,
  ram: false,
  psu: false,
  storage: false,
  cooler: false,
  "video-card": false,
  case: false,
  monitor: false,
  mouse: false,
  keyboard: false,
});

const errorMessage = ref(null);
const successMessage = ref(null);

function updateHarga() {
  let tempTotal = 0;

  Object.values(form.value.komponen).forEach((element) => {
    tempTotal += Number(
      variants.value.find((p) => p.id_varian === element.produk)?.harga || "0"
    );
  });

  totalHarga.value = tempTotal;
}

function getProductName(id) {
  const varian = variants.value.find((p) => p.id_varian === id);
  const idProduk = varian?.id_produk || -1;
  const namaVarian = varian?.nama_varian || "-";
  const namaProduk =
    products.value.find((p) => p.id_produk === idProduk)?.nama_produk || "-";

  updateHarga();

  return namaProduk + " (" + namaVarian + ")";
}

onMounted(async () => {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/login");
    return;
  }

  await loadProducts();
  await loadBuildData();
});

const loadProducts = async () => {
  const res = await axios.get("http://127.0.0.1:8000/api/productAll");
  products.value = res.data.data;
  variants.value = res.data.variants;
};

const loadBuildData = async () => {
  const res = await axios.get(`http://127.0.0.1:8000/api/manage/pcBuild/${id}`);
  form.value.nama_build = res.data.data.nama_build;

  const komponen = res.data.data.build_detail;
  komponen.forEach((e) => {
    form.value.komponen[e.bagian_komponen] = { id: e.id, produk: e.id_varian };
  });

  //form.value.komponen = res.data.data.build_detail;
};

const updateBuild = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = null;

    const res = await axios.put(
      `http://127.0.0.1:8000/api/manage/pcBuild/${id}`,
      form.value
    );

    successMessage.value = "Berhasil memperbarui build!";
    setTimeout(() => router.push("/dashboard/manage/pcBuild"), 1000);
  } catch (err) {
    errorMessage.value = err.response?.data?.message || "Gagal update data.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="max-w-xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Edit PC Build</h2>

    <div v-if="errorMessage" class="bg-red-200 text-red-700 p-3 rounded mb-3">
      {{ errorMessage }}
    </div>

    <div
      v-if="successMessage"
      class="bg-green-200 text-green-700 p-3 rounded mb-3"
    >
      {{ successMessage }}
    </div>

    <form @submit.prevent="updateBuild" class="space-y-4">
      <!-- Nama Build -->
      <div>
        <label class="block font-medium mb-1">Nama Build</label>
        <input
          type="text"
          v-model="form.nama_build"
          class="w-full border px-3 py-2 rounded"
          required
        />
      </div>

      <div>
        <label class="block mb-1 font-medium">Motherboard</label>

        <button
          type="button"
          @click="modal.motherboard = true"
          class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
        >
          {{
            form.komponen.motherboard.produk
              ? getProductName(form.komponen.motherboard.produk)
              : "Pilih Motherboard"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.motherboard']" class="text-red-500 text-sm">
          {{ errors["komponen.motherboard"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.motherboard"
          label="Motherboard"
          :products="products"
          @close="modal.motherboard = false"
          @select="(id) => (form.komponen.motherboard.produk = id)"
        />

        <div v-if="form.komponen.motherboard.produk">
          <table class="min-w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="p-2 border">Harga</th>
                <th class="p-2 border">Stok</th>
                <th class="p-2 border">Toko</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-2 border">
                  Rp.
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.motherboard.produk
                    )?.harga
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.motherboard.produk
                    )?.stok ?? "0"
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    products.find(
                      (pr) =>
                        pr.id_produk ===
                        variants.find(
                          (p) =>
                            p.id_varian === form.komponen.motherboard.produk
                        )?.id_produk
                    )?.toko.toko_name ?? "-"
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <label class="block mb-1 font-medium">CPU</label>

        <button
          type="button"
          @click="modal.cpu = true"
          class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
        >
          {{
            form.komponen.cpu.produk
              ? getProductName(form.komponen.cpu.produk)
              : "Pilih CPU"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.cpu']" class="text-red-500 text-sm">
          {{ errors["komponen.cpu"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.cpu"
          label="CPU"
          :products="products"
          @close="modal.cpu = false"
          @select="(id) => (form.komponen.cpu.produk = id)"
        />

        <div v-if="form.komponen.cpu.produk">
          <table class="min-w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="p-2 border">Harga</th>
                <th class="p-2 border">Stok</th>
                <th class="p-2 border">Toko</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-2 border">
                  Rp.
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.cpu.produk
                    )?.harga
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.cpu.produk
                    )?.stok ?? "0"
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    products.find(
                      (pr) =>
                        pr.id_produk ===
                        variants.find(
                          (p) => p.id_varian === form.komponen.cpu.produk
                        )?.id_produk
                    )?.toko.toko_name ?? "-"
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <label class="block mb-1 font-medium">RAM</label>

        <button
          type="button"
          @click="modal.ram = true"
          class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
        >
          {{
            form.komponen.ram.produk
              ? getProductName(form.komponen.ram.produk)
              : "Pilih RAM"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.ram']" class="text-red-500 text-sm">
          {{ errors["komponen.ram"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.ram"
          label="RAM"
          :products="products"
          @close="modal.ram = false"
          @select="(id) => (form.komponen.ram.produk = id)"
        />

        <div v-if="form.komponen.ram.produk">
          <table class="min-w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="p-2 border">Harga</th>
                <th class="p-2 border">Stok</th>
                <th class="p-2 border">Toko</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-2 border">
                  Rp.
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.ram.produk
                    )?.harga
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.ram.produk
                    )?.stok ?? "0"
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    products.find(
                      (pr) =>
                        pr.id_produk ===
                        variants.find(
                          (p) => p.id_varian === form.komponen.ram.produk
                        )?.id_produk
                    )?.toko.toko_name ?? "-"
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <label class="block mb-1 font-medium">Power Supply</label>

        <button
          type="button"
          @click="modal.psu = true"
          class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
        >
          {{
            form.komponen.psu.produk
              ? getProductName(form.komponen.psu.produk)
              : "Pilih Power Supply"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.psu']" class="text-red-500 text-sm">
          {{ errors["komponen.psu"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.psu"
          label="Power Supply"
          :products="products"
          @close="modal.psu = false"
          @select="(id) => (form.komponen.psu.produk = id)"
        />

        <div v-if="form.komponen.psu.produk">
          <table class="min-w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="p-2 border">Harga</th>
                <th class="p-2 border">Stok</th>
                <th class="p-2 border">Toko</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-2 border">
                  Rp.
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.psu.produk
                    )?.harga
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.psu.produk
                    )?.stok ?? "0"
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    products.find(
                      (pr) =>
                        pr.id_produk ===
                        variants.find(
                          (p) => p.id_varian === form.komponen.psu.produk
                        )?.id_produk
                    )?.toko.toko_name ?? "-"
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <label class="block mb-1 font-medium">Storage</label>

        <button
          type="button"
          @click="modal.storage = true"
          class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
        >
          {{
            form.komponen.storage.produk
              ? getProductName(form.komponen.storage.produk)
              : "Pilih Storage"
          }}
        </button>

        <!-- Error 
        <p v-if="errors['komponen.storage']" class="text-red-500 text-sm">
          {{ errors["komponen.storage"][0] }}
        </p>-->

        <!-- Modal -->
        <ProductPicker
          :open="modal.storage"
          label="Storage"
          :products="products"
          @close="modal.storage = false"
          @select="(id) => (form.komponen.storage.produk = id)"
        />

        <div v-if="form.komponen.storage.produk">
          <table class="min-w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="p-2 border">Harga</th>
                <th class="p-2 border">Stok</th>
                <th class="p-2 border">Toko</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-2 border">
                  Rp.
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.storage.produk
                    )?.harga
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    variants.find(
                      (p) => p.id_varian === form.komponen.storage.produk
                    )?.stok ?? "0"
                  }}
                </td>
                <td class="p-2 border">
                  {{
                    products.find(
                      (pr) =>
                        pr.id_produk ===
                        variants.find(
                          (p) => p.id_varian === form.komponen.storage.produk
                        )?.id_produk
                    )?.toko.toko_name ?? "-"
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div>
          <label class="block mb-1 font-medium">CPU Cooler</label>

          <button
            type="button"
            @click="modal.cooler = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.cooler.produk
                ? getProductName(form.komponen.cooler.produk)
                : "Pilih CPU Cooler"
            }}
          </button>

          <!-- Error 
          <p v-if="errors['komponen.cooler']" class="text-red-500 text-sm">
            {{ errors["komponen.cooler"][0] }}
          </p>-->

          <!-- Modal -->
          <ProductPicker
            :open="modal.cooler"
            label="CPU Cooler"
            :products="products"
            @close="modal.cooler = false"
            @select="(id) => (form.komponen.cooler.produk = id)"
          />

          <div v-if="form.komponen.cooler.produk">
            <table class="min-w-full border border-gray-300">
              <thead>
                <tr class="bg-gray-200">
                  <th class="p-2 border">Harga</th>
                  <th class="p-2 border">Stok</th>
                  <th class="p-2 border">Toko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="p-2 border">
                    Rp.
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.cooler.produk
                      )?.harga
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.cooler.produk
                      )?.stok ?? "0"
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      products.find(
                        (pr) =>
                          pr.id_produk ===
                          variants.find(
                            (p) => p.id_varian === form.komponen.cooler.produk
                          )?.id_produk
                      )?.toko.toko_name ?? "-"
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div>
          <label class="block mb-1 font-medium">Video Card</label>

          <button
            type="button"
            @click="modal['video-card'] = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen["video-card"].produk
                ? getProductName(form.komponen["video-card"].produk)
                : "Pilih Video Card"
            }}
          </button>

          <!-- Error 
          <p v-if="errors['komponen.video-card']" class="text-red-500 text-sm">
            {{ errors["komponen.video-card"][0] }}
          </p>-->

          <!-- Modal -->
          <ProductPicker
            :open="modal['video-card'].produk"
            label="Video Card"
            :products="products"
            @close="modal['video-card'] = false"
            @select="(id) => (form.komponen['video-card'].produk = id)"
          />

          <div v-if="form.komponen['video-card'].produk">
            <table class="min-w-full border border-gray-300">
              <thead>
                <tr class="bg-gray-200">
                  <th class="p-2 border">Harga</th>
                  <th class="p-2 border">Stok</th>
                  <th class="p-2 border">Toko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="p-2 border">
                    Rp.
                    {{
                      variants.find(
                        (p) =>
                          p.id_varian === form.komponen["video-card"].produk
                      )?.harga
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      variants.find(
                        (p) =>
                          p.id_varian === form.komponen["video-card"].produk
                      )?.stok ?? "0"
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      products.find(
                        (pr) =>
                          pr.id_produk ===
                          variants.find(
                            (p) =>
                              p.id_varian === form.komponen["video-card"].produk
                          )?.id_produk
                      )?.toko.toko_name ?? "-"
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div>
          <label class="block mb-1 font-medium">Case</label>

          <button
            type="button"
            @click="modal.case = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.case.produk
                ? getProductName(form.komponen.case.produk)
                : "Pilih Case"
            }}
          </button>

          <!-- Error
          <p v-if="errors['komponen.case']" class="text-red-500 text-sm">
            {{ errors["komponen.case"][0] }}
          </p>-->

          <!-- Modal -->
          <ProductPicker
            :open="modal.case"
            label="Case"
            :products="products"
            @close="modal.case = false"
            @select="(id) => (form.komponen.case.produk = id)"
          />

          <div v-if="form.komponen.case.produk">
            <table class="min-w-full border border-gray-300">
              <thead>
                <tr class="bg-gray-200">
                  <th class="p-2 border">Harga</th>
                  <th class="p-2 border">Stok</th>
                  <th class="p-2 border">Toko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="p-2 border">
                    Rp.
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.case.produk
                      )?.harga
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.case.produk
                      )?.stok ?? "0"
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      products.find(
                        (pr) =>
                          pr.id_produk ===
                          variants.find(
                            (p) => p.id_varian === form.komponen.case.produk
                          )?.id_produk
                      )?.toko.toko_name ?? "-"
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div>
          <label class="block mb-1 font-medium">Monitor</label>

          <button
            type="button"
            @click="modal.monitor = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.monitor.produk
                ? getProductName(form.komponen.monitor.produk)
                : "Pilih Monitor"
            }}
          </button>

          <!-- Error
          <p v-if="errors['komponen.monitor']" class="text-red-500 text-sm">
            {{ errors["komponen.monitor"][0] }}
          </p>-->

          <!-- Modal -->
          <ProductPicker
            :open="modal.monitor"
            label="Monitor"
            :products="products"
            @close="modal.monitor = false"
            @select="(id) => (form.komponen.monitor.produk = id)"
          />

          <div v-if="form.komponen.monitor.produk">
            <table class="min-w-full border border-gray-300">
              <thead>
                <tr class="bg-gray-200">
                  <th class="p-2 border">Harga</th>
                  <th class="p-2 border">Stok</th>
                  <th class="p-2 border">Toko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="p-2 border">
                    Rp.
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.monitor.produk
                      )?.harga
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.monitor.produk
                      )?.stok ?? "0"
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      products.find(
                        (pr) =>
                          pr.id_produk ===
                          variants.find(
                            (p) => p.id_varian === form.komponen.monitor.produk
                          )?.id_produk
                      )?.toko.toko_name ?? "-"
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div>
          <label class="block mb-1 font-medium">Mouse</label>

          <button
            type="button"
            @click="modal.mouse = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.mouse.produk
                ? getProductName(form.komponen.mouse.produk)
                : "Pilih Mouse"
            }}
          </button>

          <!-- Error
          <p v-if="errors['komponen.mouse']" class="text-red-500 text-sm">
            {{ errors["komponen.mouse"][0] }}
          </p>-->

          <!-- Modal -->
          <ProductPicker
            :open="modal.mouse"
            label="Mouse"
            :products="products"
            @close="modal.mouse = false"
            @select="(id) => (form.komponen.mouse.produk = id)"
          />

          <div v-if="form.komponen.mouse.produk">
            <table class="min-w-full border border-gray-300">
              <thead>
                <tr class="bg-gray-200">
                  <th class="p-2 border">Harga</th>
                  <th class="p-2 border">Stok</th>
                  <th class="p-2 border">Toko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="p-2 border">
                    Rp.
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.mouse.produk
                      )?.harga
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.mouse.produk
                      )?.stok ?? "0"
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      products.find(
                        (pr) =>
                          pr.id_produk ===
                          variants.find(
                            (p) => p.id_varian === form.komponen.mouse.produk
                          )?.id_produk
                      )?.toko.toko_name ?? "-"
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div>
          <label class="block mb-1 font-medium">Keyboard</label>

          <button
            type="button"
            @click="modal.keyboard = true"
            class="border px-3 py-2 rounded bg-gray-100 hover:bg-gray-200"
          >
            {{
              form.komponen.keyboard.produk
                ? getProductName(form.komponen.keyboard.produk)
                : "Pilih Keyboard"
            }}
          </button>

          <!-- Error
          <p v-if="errors['komponen.keyboard']" class="text-red-500 text-sm">
            {{ errors["komponen.keyboard"][0] }}
          </p>-->

          <!-- Modal -->
          <ProductPicker
            :open="modal.keyboard"
            label="Keyboard"
            :products="products"
            @close="modal.keyboard = false"
            @select="(id) => (form.komponen.keyboard.produk = id)"
          />

          <div v-if="form.komponen.keyboard.produk">
            <table class="min-w-full border border-gray-300">
              <thead>
                <tr class="bg-gray-200">
                  <th class="p-2 border">Harga</th>
                  <th class="p-2 border">Stok</th>
                  <th class="p-2 border">Toko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="p-2 border">
                    Rp.
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.keyboard.produk
                      )?.harga
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      variants.find(
                        (p) => p.id_varian === form.komponen.keyboard.produk
                      )?.stok ?? "0"
                    }}
                  </td>
                  <td class="p-2 border">
                    {{
                      products.find(
                        (pr) =>
                          pr.id_produk ===
                          variants.find(
                            (p) => p.id_varian === form.komponen.keyboard.produk
                          )?.id_produk
                      )?.toko.toko_name ?? "-"
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- COMBO SELECT COMPONENT 
      <template v-for="(value, key) in form.komponen" :key="key">
        <div>
          <label class="block font-medium mb-1 capitalize">
            {{ key }}
          </label>

          <select
            v-model="form.komponen[key].produk"
            class="w-full border px-3 py-2 rounded"
          >
            <option disabled value="">Pilih {{ key }}</option>

            <option
              v-for="p in products"
              :key="p.id_produk"
              :value="p.id_produk"
            >
              {{ p.nama_produk }} ({{ p.merek }})
            </option>
          </select>
        </div>
      </template> -->

      <h2 class="text-lg font-semibold mb-2">
        Total Harga : Rp. {{ totalHarga }}
      </h2>

      <button
        type="submit"
        :disabled="isLoading"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        {{ isLoading ? "Menyimpan..." : "Simpan Perubahan" }}
      </button>
    </form>
  </div>
</template>
