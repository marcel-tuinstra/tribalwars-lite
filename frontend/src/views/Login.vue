<script setup lang="ts">
import {ref} from 'vue';
import {useRouter} from 'vue-router';
import {useAuthStore} from '@/stores/auth'

const email = ref('');
const password = ref('');
const error = ref('');
const router = useRouter();
const auth = useAuthStore();

const handleLogin = async () => {
  try {
    await auth.login(email.value, password.value);
    router.push('/');
  } catch (err) {
    error.value = 'Invalid credentials';
  }
};
</script>

<template>
  <div class="max-w-md mx-auto mt-20 p-6 border rounded shadow">
    <h1 class="text-2xl mb-4">Login</h1>
    <form @submit.prevent="handleLogin">
      <input v-model="email" type="email" placeholder="Email" class="mb-2 w-full border p-2"/>
      <input v-model="password" type="password" placeholder="Password" class="mb-2 w-full border p-2"/>
      <button type="submit" class="w-full bg-blue-500 text-white p-2">Login</button>
    </form>
    <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
  </div>
</template>
