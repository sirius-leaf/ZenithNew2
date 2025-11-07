<template>
  <div class="register">
    <h2>Register</h2>
    <form @submit.prevent="registerUser">
      <input v-model="form.name" placeholder="Name" required />
      <input v-model="form.email" type="email" placeholder="Email" required />
      <input v-model="form.password" type="password" placeholder="Password" required />
      <button type="submit">Register</button>
    </form>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref } from 'vue'

const form = ref({
  name: '',
  email: '',
  password: ''
})

const registerUser = async () => {
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/register', form.value)
    console.log(res.data)
    alert('Registrasi berhasil!')
  } catch (err) {
    console.error(err.response?.data)
    alert('Registrasi gagal!')
  }
}
</script>

<style scoped>
.register {
  display: flex;
  flex-direction: column;
  width: 300px;
  margin: 50px auto;
  gap: 10px;
}
</style>
