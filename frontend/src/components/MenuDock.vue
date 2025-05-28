<script setup lang="ts">
import {useRouter, useRoute} from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

import { PhRows, PhMapTrifold, PhSignOut } from '@phosphor-icons/vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

function handleLogout() {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="dock lg:hidden" v-show="auth.user">
    <button role="button" :class="{ 'dock-active': route.path === '/' }" @click="router.push('/')">
      <PhRows :size="24" />
      <span class="dock-label">Overview</span>
    </button>
    <button role="button" :class="{ 'dock-active': route.path === '/map' }" @click="router.push('/map')">
      <PhMapTrifold :size="24" />
      <span class="dock-label">Map</span>
    </button>
    <button role="button" @click="handleLogout">
      <PhSignOut :size="24" />
      <span class="dock-label">Sign Out</span>
    </button>
  </div>
</template>
