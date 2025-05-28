<script setup lang="ts">
import { ref } from 'vue'
import {RouterLink, useRouter} from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const password = ref('')
const error = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const router = useRouter()
const auth = useAuthStore()

const handleLogin = async () => {
  isLoading.value = true
  error.value = ''

  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (err) {
    isLoading.value = false;
    error.value = 'Invalid credentials'
  }
}
</script>

<template>
  <img class="w-40 z-2 mt-5 ml-5 hidden md:block absolute" src="/images/logo.png" alt="Settlers of War"/>
  <div class="min-h-screen flex flex-col md:justify-center md:items-center md:px-4 md:py-6">
    <div class="card bg-base-200 h-screen md:h-auto w-full max-w-md shadow-md">
      <img src="/images/background.png" alt="Settlers of War" class="w-full object-cover max-h-80 md:hidden" />

      <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Log In to Your Account</h2>

        <div v-if="error" role="alert" class="alert alert-error shadow-sm mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <span>{{ error }}</span>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label for="email" class="label label-text font-semibold">Email Address</label>
            <input v-model="email" type="email" id="email" name="email"
                   class="input input-bordered w-full focus:outline-none focus:ring focus:ring-accent"
                   placeholder="you@example.com" required />
          </div>

          <div>
            <label for="password" class="label label-text font-semibold">Password</label>
            <div class="relative">
              <input :type="showPassword ? 'text' : 'password'" v-model="password" id="password" name="password"
                     class="input input-bordered w-full pr-12 focus:outline-none focus:ring focus:ring-accent"
                     placeholder="Enter your password" required />
              <button type="button" @click="showPassword = !showPassword"
                      class="absolute top-1/2 right-3 transform -translate-y-1/2 text-sm opacity-70 hover:opacity-100">
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
            <RouterLink to="/forgot" class="label">Forgot Password?</RouterLink>
          </div>

          <div class="card-actions justify-end mt-4">
            <button type="submit" class="btn btn-accent w-full sm:w-auto" :disabled="isLoading">
              <span v-if="!isLoading">Log In</span>
              <span v-else class="loading loading-spinner loading-sm"></span>
              <span v-if="isLoading" class="ml-2">Logging in...</span>
            </button>
          </div>
        </form>

        <div class="whitespace-nowrap">Dont have an account? <RouterLink to="/register" class="font-bold">Sign up.</RouterLink></div>
      </div>
    </div>
  </div>
</template>

<style>
body {
  background: url('/images/background.png') center center no-repeat;
  background-size: cover;
}
</style>
