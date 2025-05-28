<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { useVillageStore } from '@/stores/village'

import { PhRows, PhMapTrifold, PhSignOut } from '@phosphor-icons/vue'

const router = useRouter()
const auth = useAuthStore()
const villageStore = useVillageStore()

function handleLogout() {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <ul class="menu w-full mb-4 bg-base-200 lg:menu-horizontal rounded-box gap-1 hidden lg:flex" v-show="auth.user">
    <li>
      <RouterLink
        to="/"
        class="tooltip tooltip-bottom"
        exactActiveClass="bg-base-300"
        data-tip="Overview"
      >
        <PhRows :size="24" />
        Overview

        <span class="badge badge-xs">{{ villageStore.playerVillages.length }}</span>
      </RouterLink>
    </li>
    <li>
      <RouterLink
        to="/map"
        class="tooltip tooltip-bottom"
        exactActiveClass="bg-base-300"
        data-tip="Map"
      >
        <PhMapTrifold :size="24" />
        Map
      </RouterLink>
    </li>
    <li class="ml-auto tooltip tooltip-bottom" data-tip="Sign Out">
      <span @click="handleLogout()">
        <PhSignOut :size="24" />
        Sign Out
      </span>
    </li>
  </ul>
</template>
