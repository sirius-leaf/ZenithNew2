import { ref, computed } from 'vue';

// Menggunakan Composition API untuk membuat state global sederhana
// Ini mensimulasikan Vuex/Pinia Store

const cartItems = ref([]); // [{id_varian: X, kuantitas: Y, variantDetail: {..}}]

// Dapatkan detail keranjang dari localStorage saat aplikasi dimuat
const loadCart = () => {
  const savedCart = localStorage.getItem('localCart');
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart);
  }
};

// Simpan detail keranjang ke localStorage
const saveCart = () => {
  localStorage.setItem('localCart', JSON.stringify(cartItems.value));
};

// Fungsi menghitung total kuantitas
const totalItems = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.kuantitas, 0);
});

// Fungsi untuk Add/Update Item
const updateCartItem = (id_varian, kuantitas, variantDetail = null) => {
  const index = cartItems.value.findIndex(item => item.id_varian === id_varian);
  
  if (index !== -1) {
    // Item sudah ada, update kuantitas
    const newKuantitas = cartItems.value[index].kuantitas + kuantitas;

    if (newKuantitas <= 0) {
      // Hapus jika kuantitas <= 0
      cartItems.value.splice(index, 1);
    } else {
      cartItems.value[index].kuantitas = newKuantitas;
    }
  } else if (kuantitas > 0) {
    // Item baru, tambahkan
    cartItems.value.push({
      id_varian,
      kuantitas,
      variantDetail: variantDetail || {} 
    });
  }
  saveCart();
};

const removeCartItem = (id_varian) => {
    const index = cartItems.value.findIndex(item => item.id_varian === id_varian);
    if (index !== -1) {
        cartItems.value.splice(index, 1);
        saveCart();
    }
};

const clearCart = () => {
    cartItems.value = [];
    saveCart();
};

loadCart(); // Muat keranjang saat script dijalankan

export function useCartStore() {
  return {
    cartItems,
    totalItems,
    updateCartItem,
    removeCartItem,
    clearCart
  };
}