<script setup>
import { ref, onMounted, computed } from "vue";
import { useCartStore } from "@/stores/cartStore";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();
const { clearCart } = useCartStore();

// State
const loading = ref(true);
const loadingCheckout = ref(false);
const apiError = ref(null);
const cartSummary = ref([]);
const totalPrice = ref(0);
const successMessage = ref(null);

// Form & User State
const userDetails = ref({ name: "", email: "" });
const form = ref({ alamat_pengiriman: "" });
const paymentMethod = ref("transfer"); // State untuk pilihan metode pembayaran (Default: Transfer/Midtrans)

const totalItems = computed(() => cartSummary.value.length);

// 1. Ambil data ringkasan & user
const fetchCheckoutData = async () => {
  loading.value = true;
  apiError.value = null;

  const selectedItemsJson = localStorage.getItem("checkout_selection");
  if (!selectedItemsJson) {
    apiError.value = "Keranjang kosong atau belum memilih item.";
    loading.value = false;
    return;
  }
  const cartDataForApi = JSON.parse(selectedItemsJson);

  if (cartDataForApi.length === 0) {
    apiError.value = "Tidak ada item yang dipilih untuk checkout.";
    loading.value = false;
    return;
  }

  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push({ name: "login" });
    return;
  }

  try {
    const userRes = await axios.get("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${token}` },
    });
    userDetails.value.name = userRes.data.name;
    userDetails.value.email = userRes.data.email;

    const response = await axios.post(
      "http://127.0.0.1:8000/api/order/preview",
      {
        cartItems: cartDataForApi,
      },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    cartSummary.value = response.data.cartItems;
    totalPrice.value = response.data.totalPrice;
  } catch (error) {
    apiError.value =
      error.response?.data?.message || "Gagal memuat ringkasan keranjang.";
  } finally {
    loading.value = false;
  }
};

// 2. Proses Checkout (INTEGRASI MIDTRANS)
const finalizeCheckout = async () => {
  loadingCheckout.value = true;
  apiError.value = null;

  if (form.value.alamat_pengiriman.trim() === "") {
    apiError.value = "Alamat pengiriman wajib diisi.";
    loadingCheckout.value = false;
    return;
  }

  // Ambil data dari LocalStorage selection
  const selectedItemsJson = localStorage.getItem("checkout_selection");
  const cartItemsPayload = JSON.parse(selectedItemsJson);

  const payload = {
    alamat_pengiriman: form.value.alamat_pengiriman,
    cartItems: cartItemsPayload,
    payment_method: paymentMethod.value, // Kirim metode pembayaran (transfer/cod)
  };

  const token = localStorage.getItem("authToken");

  try {
    // A. Panggil API Store ke Backend
    const response = await axios.post(
      "http://127.0.0.1:8000/api/order/store",
      payload,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    const orderIds = response.data.order_ids;
    const snapToken = response.data.snap_token; // Token dari Midtrans (jika ada)

    // B. Cek Metode Pembayaran
    if (paymentMethod.value === "cod") {
      // Jika COD, langsung sukses tanpa popup
      handleSuccess(orderIds);
    } else {
      clearCart();
      // Jika Transfer/Online, Buka Popup Midtrans Snap
      if (window.snap && snapToken) {
        window.snap.pay(snapToken, {
          onSuccess: async function (result) {
            console.log("Payment Success:", result);
            // Panggil API untuk update status jadi 'paid'
            try {
              for (const orderId of orderIds) {
                await axios.post(
                  `http://127.0.0.1:8000/api/orders/${orderId}/pay`,
                  {},
                  { headers: { Authorization: `Bearer ${token}` } }
                );
              }
            } catch (e) {
              console.error("Gagal update status paid:", e);
            }
            handleSuccess(orderIds);
          },
          onPending: function (result) {
            console.log("Payment Pending:", result);
            handleSuccess(orderIds); // Tetap dianggap sukses order (status pending)
          },
          onError: function (result) {
            console.error("Payment Error:", result);
            apiError.value = "Pembayaran gagal atau dibatalkan.";
          },
          onClose: function () {
            console.warn(
              "Customer closed the popup without finishing the payment"
            );
            // Opsional: Arahkan ke riwayat transaksi
            alert(
              "Anda menutup pembayaran. Silakan cek riwayat pesanan untuk membayar ulang."
            );
            router.push({ name: "dashboard" });
          },
        });
      } else {
        apiError.value =
          "Gagal memuat sistem pembayaran. Token tidak ditemukan.";
      }
    }
  } catch (error) {
    console.error("Checkout Gagal:", error);
    apiError.value =
      error.response?.data?.message || "Gagal memproses pesanan. Coba lagi.";
  } finally {
    loadingCheckout.value = false;
  }
};

// Fungsi Helper setelah order berhasil
const handleSuccess = (orderIds) => {
  clearCart(); // Hapus item dari keranjang global
  localStorage.removeItem("checkout_selection"); // Hapus seleksi

  // Redirect ke halaman sukses dengan ID pesanan pertama
  router.push({ name: "checkout.success", query: { id: orderIds[0] } });
};

onMounted(() => {
  fetchCheckoutData();
});
</script>

<template>
  <div class="font-ubuntu container mx-auto p-4 md:p-8">
    <div class="flex items-center gap-3 mb-8">
      <div class="bg-pink-100 p-2 rounded-lg">
        <svg
          class="w-6 h-6 text-pink-600"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
          />
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-blue-800">Proses Checkout</h1>
    </div>

    <div v-if="loading" class="text-center py-10 text-gray-500">
      <div
        class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-pink-500 mb-3"
      ></div>
      Memuat data checkout...
    </div>

    <div
      v-else-if="apiError && totalItems === 0"
      class="p-6 bg-red-50 border border-red-300 rounded-xl"
    >
      <p class="text-red-700 font-medium">{{ apiError }}</p>
      <router-link
        :to="{ name: 'product-list' }"
        class="mt-2 inline-block text-pink-600 hover:text-pink-700 font-medium"
      >
        ← Kembali ke Produk
      </router-link>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Kolom Kiri: Form Checkout -->
      <div
        class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md border border-gray-200"
      >
        <h2
          class="text-xl font-bold mb-5 text-blue-800 flex items-center gap-2"
        >
          <svg
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
            />
          </svg>
          Detail Pengiriman
        </h2>

        <form @submit.prevent="finalizeCheckout" class="space-y-5">
          <div>
            <label
              class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1"
            >
              <svg
                class="w-4 h-4 text-gray-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
              Nama Penerima
            </label>
            <input
              type="text"
              :value="userDetails.name"
              readonly
              class="w-full px-4 py-2.5 border border-gray-300 bg-gray-50 rounded-lg text-gray-700"
            />
          </div>

          <div>
            <label
              class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1"
            >
              <svg
                class="w-4 h-4 text-gray-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
              </svg>
              Email
            </label>
            <input
              type="email"
              :value="userDetails.email"
              readonly
              class="w-full px-4 py-2.5 border border-gray-300 bg-gray-50 rounded-lg text-gray-700"
            />
          </div>

          <div>
            <label
              class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1"
            >
              <svg
                class="w-4 h-4 text-gray-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              Alamat Pengiriman Lengkap <span class="text-pink-600">*</span>
            </label>
            <textarea
              v-model="form.alamat_pengiriman"
              rows="4"
              required
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-300 focus:border-pink-500 transition"
              placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"
            ></textarea>
          </div>

          <div
            v-if="apiError"
            class="p-3 bg-red-100 text-red-700 rounded-lg text-sm"
          >
            {{ apiError }}
          </div>

          <div class="pt-4 border-t border-gray-200">
            <h3
              class="text-md font-semibold text-blue-800 mb-3 flex items-center gap-2"
            >
              <svg
                class="w-5 h-5 text-pink-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              Pilih Metode Pembayaran
            </h3>

            <div class="space-y-3">
              <!-- Midtrans -->
              <label
                class="flex items-start p-4 border rounded-xl cursor-pointer transition-all hover:shadow-sm"
                :class="
                  paymentMethod === 'transfer'
                    ? 'border-pink-500 bg-pink-50'
                    : 'border-gray-300'
                "
              >
                <input
                  type="radio"
                  v-model="paymentMethod"
                  value="transfer"
                  name="payment_method"
                  class="form-radio h-5 w-5 text-pink-600 focus:ring-pink-500 mt-1"
                />
                <div class="ml-4">
                  <p class="font-medium text-gray-800">
                    Pembayaran Online (Midtrans)
                  </p>
                  <p class="text-xs text-gray-600 mt-1">
                    Transfer Bank, GoPay, ShopeePay, QRIS — aman & instan.
                  </p>
                </div>
              </label>

              <!-- COD -->
              <label
                class="flex items-start p-4 border rounded-xl cursor-pointer transition-all hover:shadow-sm"
                :class="
                  paymentMethod === 'cod'
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-300'
                "
              >
                <input
                  type="radio"
                  v-model="paymentMethod"
                  value="cod"
                  name="payment_method"
                  class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500 mt-1"
                />
                <div class="ml-4">
                  <p class="font-medium text-gray-800">COD (Bayar di Tempat)</p>
                  <p class="text-xs text-gray-600 mt-1">
                    Bayar tunai kepada kurir saat barang sampai.
                  </p>
                </div>
              </label>
            </div>
          </div>

          <button
            type="submit"
            :disabled="loadingCheckout || apiError"
            class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3.5 rounded-xl transition-all disabled:bg-gray-300 flex justify-center items-center gap-2 shadow-md hover:shadow-lg"
          >
            <svg
              v-if="!loadingCheckout"
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
              />
            </svg>
            <span v-if="loadingCheckout">Memproses...</span>
            <span v-else>Bayar Sekarang</span>
          </button>
        </form>
      </div>

      <!-- Kolom Kanan: Ringkasan -->
      <div class="lg:col-span-1">
        <div
          class="bg-white p-6 rounded-xl shadow-inner border border-gray-200 sticky top-6"
        >
          <div class="flex items-center gap-2 mb-4">
            <svg
              class="w-5 h-5 text-pink-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
            <h2 class="text-xl font-bold text-blue-800">
              Ringkasan ({{ totalItems }} Item)
            </h2>
          </div>

          <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
            <div
              v-for="item in cartSummary"
              :key="item.variant.id_varian"
              class="flex justify-between text-sm py-2 border-b border-gray-100 last:border-0"
            >
              <div class="text-gray-700">
                <div class="font-medium">
                  {{ item.variant.product.nama_produk }}
                </div>
                <div
                  class="text-xs text-gray-500 mt-0.5"
                  v-if="item.variant.nama_varian"
                >
                  {{ item.variant.nama_varian }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  x{{ item.kuantitas }}
                </div>
              </div>
              <span class="font-bold text-blue-800">
                Rp
                {{
                  (item.variant.harga * item.kuantitas).toLocaleString("id-ID")
                }}
              </span>
            </div>
          </div>

          <div
            class="flex justify-between text-xl font-bold mt-5 pt-4 border-t border-gray-300"
          >
            <span>Total Bayar</span>
            <span class="text-pink-600"
              >Rp {{ totalPrice.toLocaleString("id-ID") }}</span
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
