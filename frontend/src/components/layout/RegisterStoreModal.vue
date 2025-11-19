<!-- src/components/RegisterStoreModal.vue -->
<template>
  <Transition name="modal-fade">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <Transition name="modal-slide">
        <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl overflow-hidden" @click.stop>
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-blue-900">
              {{ currentStep === 1 ? 'Buat Toko Anda' : 
                 currentStep === 2 ? 'Lengkapi Persyaratan' : 
                 'Pendaftaran Berhasil!' }}
            </h3>
            <button @click="close" class="text-gray-500 hover:text-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="p-6 max-h-[70vh] overflow-y-auto">
            <Transition name="step-fade" mode="out-in">
              <!-- Step 1: Informasi Toko -->
              <div v-if="currentStep === 1" key="step-1" class="space-y-4">
                <div>
                  <label class="block text-gray-700 text-sm font-medium mb-1">Nama Toko</label>
                  <input v-model="storeName" type="text" placeholder="Masukan nama toko" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300" />
                </div>
                <div>
                  <label class="block text-gray-700 text-sm font-medium mb-1">Alamat</label>
                  <input v-model="address" type="text" placeholder="Masukan alamat lengkap" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300" />
                </div>
                <div>
                  <label class="block text-gray-700 text-sm font-medium mb-1">Deskripsi Toko</label>
                  <textarea v-model="description" rows="3" placeholder="Ceritakan tentang tokomu..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none"></textarea>
                </div>
              </div>

              <!-- Step 2: Dokumen -->
              <div v-else-if="currentStep === 2" key="step-2" class="space-y-4">
                <div>
                  <label class="block text-gray-700 text-sm font-medium mb-1">Upload KTP</label>
                  <input type="file" @change="handleFileUpload($event, 'ktp')" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-pink-50 file:text-pink-600" />
                </div>
                <div>
                  <label class="block text-gray-700 text-sm font-medium mb-1">Upload NPWP</label>
                  <input type="file" @change="handleFileUpload($event, 'npwp')" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-pink-50 file:text-pink-600" />
                </div>
                <div class="flex items-start gap-2">
                  <input v-model="agreed" type="checkbox" class="mt-0.5 w-4 h-4 text-pink-500 rounded focus:ring-pink-400" />
                  <label class="text-xs text-gray-700">
                    Saya setuju dengan <span class="text-pink-500 font-medium">Syarat dan Ketentuan</span>
                  </label>
                </div>
              </div>

              <!-- Step 3: Sukses -->
              <div v-else-if="currentStep === 3" key="step-3" class="text-center py-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-green-700 mb-2">Toko berhasil diajukan!</h4>
                <p class="text-gray-600 mb-6">
                  Permintaan Anda sedang menunggu konfirmasi dari admin.<br />
                  Anda akan langsung dialihkan ke halaman CRUD produk setelah disetujui.
                </p>
                <div class="inline-flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Menunggu konfirmasi...
                </div>
              </div>
            </Transition>
          </div>

          <div v-if="currentStep !== 3" class="px-6 py-4 bg-gray-50 flex justify-between gap-3">
            <button v-if="currentStep === 2" @click="prevStep" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100">
              Kembali
            </button>
            <div class="flex-grow"></div>
            <button
              v-if="currentStep === 1"
              @click="nextStep"
              :disabled="!storeName.trim() || !address.trim()"
              class="px-6 py-2.5 bg-pink-500 text-white text-sm font-medium rounded-lg hover:bg-pink-600 disabled:opacity-50"
            >
              Lanjut
            </button>
            <button
              v-else-if="currentStep === 2"
              @click="submitForm"
              :disabled="!agreed || !ktpFile || !npwpFile"
              class="px-6 py-2.5 bg-pink-500 text-white text-sm font-medium rounded-lg hover:bg-pink-600 disabled:opacity-50"
            >
              Kirim Pendaftaran
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: Boolean
})
const emit = defineEmits(['update:modelValue', 'sellerApproved'])

// State form
const currentStep = ref(1)
const storeName = ref('')
const address = ref('')
const description = ref('')
const ktpFile = ref(null)
const npwpFile = ref(null)
const agreed = ref(false)
const pollId = ref(null)

// Reset form saat modal dibuka
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    currentStep.value = 1
    storeName.value = ''
    address.value = ''
    description.value = ''
    ktpFile.value = null
    npwpFile.value = null
    agreed.value = false
    if (pollId.value) clearInterval(pollId.value)
  }
})

const close = () => {
  if (pollId.value) {
    clearInterval(pollId.value)
    pollId.value = null
  }
  emit('update:modelValue', false)
}

const nextStep = () => {
  if (storeName.value.trim() && address.value.trim()) {
    currentStep.value = 2
  }
}

const prevStep = () => {
  currentStep.value = 1
}

const handleFileUpload = (event, field) => {
  const file = event.target.files[0]
  if (field === 'ktp') ktpFile.value = file
  if (field === 'npwp') npwpFile.value = file
}

const submitForm = async () => {
  if (!agreed.value || !ktpFile.value || !npwpFile.value) {
    alert('Harap lengkapi semua persyaratan.')
    return
  }

  try {
    // Buat FormData
    const formData = new FormData()
    formData.append('store_name', storeName.value)
    formData.append('store_address', address.value)
    formData.append('store_description', description.value)
    formData.append('ktp', ktpFile.value)
    formData.append('npwp', npwpFile.value)

    // Kirim ke Laravel
    await axios.post('/api/seller/request', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    // Pindah ke step sukses
    currentStep.value = 3

    // 🔁 Mulai polling (cek setiap 3 detik)
    pollId.value = setInterval(async () => {
      try {
        const res = await axios.get('/api/seller/check-role')
        if (res.data.role === 'penjual') {
          clearInterval(pollId.value)
          pollId.value = null
          emit('sellerApproved') // ✅ Emit event ke parent
          close()
        }
      } catch (err) {
        console.warn('[Polling] Gagal cek role:', err.message)
      }
    }, 3000)

  } catch (error) {
    console.error('Gagal mendaftar:', error)
    alert('Gagal mengajukan toko. Silakan coba lagi.')
  }
}
</script>

<style scoped>
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
.step-fade-enter-active,
.step-fade-leave-active {
  transition: opacity 0.25s ease;
}
.step-fade-enter-from,
.step-fade-leave-to {
  opacity: 0;
}
</style>