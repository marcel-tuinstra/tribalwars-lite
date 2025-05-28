<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')
const success = ref('')
const error = ref('')
const confirmDirty = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const isLoading = ref(false)
const router = useRouter()
const auth = useAuthStore()

const passwordsMatch = computed(() => password.value === passwordConfirm.value)

const handleRegister = async () => {
  isLoading.value = true
  error.value = ''

  if (password.value !== passwordConfirm.value) {
    isLoading.value = false
    return
  }

  try {
    await auth.register(name.value, email.value, password.value)
    success.value = 'Account created successfully. Please log in.'
    isLoading.value = false
  } catch (err) {
    isLoading.value = false
    error.value = err
  }
}
</script>

<template>
  <img
    class="w-40 z-2 mt-5 ml-5 hidden md:block absolute"
    src="/images/logo.png"
    alt="Settlers of War"
  />
  <div class="min-h-screen flex flex-col md:justify-center md:items-center md:px-4 md:py-6">
    <div class="card bg-base-200 h-screen md:h-auto w-full max-w-md shadow-md">
      <img
        src="/images/background.png"
        alt="Settlers of War"
        class="w-full object-cover max-h-80 md:hidden"
      />

      <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Setup Your Account</h2>

        <div v-if="success" role="alert" class="alert alert-success shadow-sm mb-4">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 shrink-0 stroke-current"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <span>{{ success }}</span>
        </div>

        <div v-if="error" role="alert" class="alert alert-error shadow-sm mb-4">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6 shrink-0 stroke-current"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <span>{{ error }}</span>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <div>
            <label for="name" class="label label-text font-semibold">Display name</label>
            <input
              v-model="name"
              type="text"
              id="name"
              name="name"
              pattern="[A-Za-z0-9][A-Za-z0-9\-]*"
              minlength="5"
              maxlength="20"
              title="Only letters, numbers or dash"
              class="input input-bordered validator w-full focus:outline-none focus:ring focus:ring-accent"
              placeholder="sir-slapstick"
              required
            />
            <p class="validator-hint">
              5–20 characters. Letters, numbers, and dashes only. Must start with a letter.
            </p>
          </div>

          <div>
            <label for="email" class="label label-text font-semibold">Email address</label>
            <input
              v-model="email"
              inputmode="email"
              type="email"
              id="email"
              name="email"
              class="input input-bordered validator w-full"
              placeholder="you@example.com"
              required
            />
            <p class="validator-hint">Please enter a valid email address (e.g. you@example.com).</p>
          </div>

          <div>
            <label for="password" class="label label-text font-semibold">Password</label>
            <div class="relative">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                id="password"
                name="password"
                class="input input-bordered w-full pr-12 focus:outline-none focus:ring focus:ring-accent"
                placeholder="Enter your password"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute top-1/2 right-3 transform -translate-y-1/2 text-sm opacity-70 hover:opacity-100"
              >
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
          </div>

          <div>
            <label for="password-confirm" class="label label-text font-semibold"
              >Confirm Password</label
            >
            <div class="relative">
              <input
                @input="confirmDirty = true"
                :type="showPasswordConfirm ? 'text' : 'password'"
                v-model="passwordConfirm"
                id="password-confirm"
                name="password-confirm"
                class="input input-bordered validator w-full pr-12"
                placeholder="Enter your password"
                required
              />
              <button
                type="button"
                @click="showPasswordConfirm = !showPasswordConfirm"
                class="absolute top-1/2 right-3 transform -translate-y-1/2 text-sm opacity-70 hover:opacity-100"
              >
                {{ showPasswordConfirm ? 'Hide' : 'Show' }}
              </button>
            </div>
            <p
              v-if="confirmDirty && passwordConfirm && !passwordsMatch"
              class="validator-hint block visible text-error"
            >
              Passwords do not match.
            </p>
          </div>

          <div class="card-actions justify-end mt-4">
            <button type="submit" class="btn btn-accent w-full sm:w-auto" :disabled="isLoading">
              <span v-if="!isLoading">Register</span>
              <span v-else class="loading loading-spinner loading-sm"></span>
              <span v-if="isLoading" class="ml-2">Creating account...</span>
            </button>
          </div>
        </form>

        <div class="whitespace-nowrap">
          Already have an account?
          <RouterLink to="/login" class="font-bold">Log in.</RouterLink>
        </div>
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
