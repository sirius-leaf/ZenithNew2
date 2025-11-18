<template>
  <div class="w-full bg-gray-50 p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-8 flex items-center gap-2">
      <button @click="$router.back()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
        ← Kembali ke Dashboard
      </button>
    </div>

    <!-- Profile Card -->
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-6">
      <h2 class="text-lg font-semibold text-neutral-950 mb-6">Informasi Akun</h2>

      <!-- Informasi Akun (vertikal) -->
      <div class="space-y-6">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-pink-50 rounded-lg flex justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 14c3.866 0 7 1.791 7 4v1H5v-1c0-2.209 3.134-4 7-4zm0-2a4 4 0 100-8 4 4 0 000 8z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Username</p>
            <p class="text-base font-medium text-neutral-950">{{ userData.username }}</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-50 rounded-lg flex justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Email</p>
            <p class="text-base font-medium text-neutral-950">{{ userData.email }}</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-blue-50 rounded-lg flex justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 2l7 4v6c0 5.25-3.5 10-7 10S5 17.25 5 12V6l7-4zm-2 10l2 2 4-4" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Role</p>
            <p class="text-base font-medium text-neutral-950">{{ userData.role }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Buka Toko Section (di bawah profil) -->
    <div class="max-w-2xl mx-auto mt-6 bg-gradient-to-r from-pink-50 to-blue-50 rounded-xl p-6 shadow-md">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 bg-white rounded-full border-2 border-pink-500 flex justify-center items-center shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 9l1-4h16l1 4M4 9h16v10H4V9zm4 5h8" />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-neutral-950 mb-2">Buka Toko Anda Sendiri</h3>
          <p class="text-sm text-gray-600 mb-4">
            Jadilah penjual dan raih penghasilan dengan membuka toko di platform kami.
            Proses pendaftaran mudah dan gratis!
          </p>
          <button 
            @click="openStore"
            class="w-full py-2 px-4 bg-pink-500 hover:bg-pink-600 text-white font-medium rounded-lg flex items-center justify-center gap-2 transition-all duration-200 hover:shadow-md active:scale-[0.98]"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v8m4-4H8m12 0a8 8 0 11-16 0 8 8 0 0116 0z" />
            </svg>
            Daftar Toko Sekarang
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: Form Pendaftaran Toko -->
    <Transition name="modal-fade">
      <div v-if="showStoreForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <Transition name="modal-slide">
          <div
            class="w-full max-w-2xl bg-white rounded-xl shadow-xl overflow-hidden"
            @click.stop
          >
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <h3 class="text-lg font-semibold text-blue-900">
                {{ currentStep === 1 ? 'Buat Toko Anda' : 'Lengkapi Persyaratan' }}
              </h3>
              <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 max-h-[70vh] overflow-y-auto">
              <Transition name="step-fade" mode="out-in">
                <!-- Step 1 -->
                <div v-if="currentStep === 1" key="step-1" class="space-y-4">
                  <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1">Nama Toko</label>
                    <input
                      v-model="formData.storeName"
                      type="text"
                      placeholder="Masukan nama toko"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-400 transition"
                    />
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1">Alamat</label>
                    <input
                      v-model="formData.address"
                      type="text"
                      placeholder="Masukan alamat lengkap"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-400 transition"
                    />
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1">Deskripsi Toko</label>
                    <textarea
                      v-model="formData.description"
                      rows="3"
                      placeholder="Ceritakan tentang tokomu..."
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-400 transition resize-none"
                    ></textarea>
                  </div>
                </div>

                <!-- Step 2 -->
                <div v-else-if="currentStep === 2" key="step-2" class="space-y-4">
                  <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1">Upload KTP</label>
                    <input
                      type="file"
                      accept=".jpg,.jpeg,.png,.pdf"
                      @change="handleFileUpload($event, 'ktp')"
                      class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-pink-50 file:text-pink-600 hover:file:bg-pink-100 file:transition-colors"
                    />
                  </div>
                  <div>
                    <label class="block text-gray-700 text-sm font-medium mb-1">Upload NPWP</label>
                    <input
                      type="file"
                      accept=".jpg,.jpeg,.png,.pdf"
                      @change="handleFileUpload($event, 'npwp')"
                      class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-pink-50 file:text-pink-600 hover:file:bg-pink-100 file:transition-colors"
                    />
                  </div>
                  <div class="flex items-start gap-2">
                    <input
                      v-model="formData.agreed"
                      type="checkbox"
                      class="mt-0.5 w-4 h-4 text-pink-500 rounded focus:ring-pink-400 focus:ring-2 transition"
                    />
                    <label class="text-xs text-gray-700">
                      Saya setuju dengan
                      <span class="text-pink-500 font-medium">Syarat dan Ketentuan</span>
                      yang berlaku.
                    </label>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Footer Modal (Tombol) -->
            <div class="px-6 py-4 bg-gray-50 flex justify-between gap-3">
              <button
                v-if="currentStep === 2"
                @click="prevStep"
                class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition"
              >
                Kembali
              </button>
              <div class="flex-grow"></div>
              <button
                v-if="currentStep === 1"
                @click="nextStep"
                :disabled="!formData.storeName.trim() || !formData.address.trim()"
                class="px-6 py-2.5 bg-pink-500 text-white text-sm font-medium rounded-lg hover:bg-pink-600 disabled:opacity-50 transition-all duration-200 hover:shadow active:scale-[0.99]"
              >
                Lanjut
              </button>
              <button
                v-else
                @click="submitForm"
                :disabled="!formData.agreed || !formData.ktp || !formData.npwp"
                class="px-6 py-2.5 bg-pink-500 text-white text-sm font-medium rounded-lg hover:bg-pink-600 disabled:opacity-50 transition-all duration-200 hover:shadow active:scale-[0.99]"
              >
                Kirim Pendaftaran
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const userData = ref({
  username: 'Budi Santoso',
  email: 'budi.santoso@email.com',
  role: 'User'
})

const showStoreForm = ref(false)
const currentStep = ref(1)

const formData = reactive({
  storeName: '',
  address: '',
  description: '',
  ktp: null,
  npwp: null,
  agreed: false
})

const openStore = () => {
  showStoreForm.value = true
  currentStep.value = 1
}

const closeModal = () => {
  showStoreForm.value = false
}

const nextStep = () => {
  if (formData.storeName.trim() && formData.address.trim()) {
    currentStep.value = 2
  }
}

const prevStep = () => {
  currentStep.value = 1
}

const handleFileUpload = (event, field) => {
  const file = event.target.files[0]
  formData[field] = file
}

const submitForm = () => {
  if (!formData.agreed || !formData.ktp || !formData.npwp) {
    alert('Harap lengkapi semua persyaratan.')
    return
  }

  alert(`Pendaftaran toko "${formData.storeName}" berhasil diajukan!`)
  closeModal()
}
</script>

<style scoped>
/* Animasi Modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-slide-enter-active,
.modal-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.2s ease;
}

.modal-slide-enter-from {
  transform: translateY(-20px);
  opacity: 0;
}

.modal-slide-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}

/* Animasi Step */
.step-fade-enter-active,
.step-fade-leave-active {
  transition: opacity 0.25s ease;
}

.step-fade-enter-from,
.step-fade-leave-to {
  opacity: 0;
}
</style>