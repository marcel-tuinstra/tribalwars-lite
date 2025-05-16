<script setup lang="ts">
import {RouterLink, RouterView} from 'vue-router'

import {useAuthStore} from '@/stores/auth';
import {onMounted} from "vue";

const auth = useAuthStore();

onMounted(() => {
  if (auth.token && !auth.user) {
    auth.fetchProfile();
  }
});
</script>

<template>
  <div class="flex flex-col h-screen">
    <!-- Persistent Header -->
    <header class="flex items-center justify-between bg-white shadow p-4">
      <div class="flex items-center space-x-4">
        <span class="font-bold text-lg">Tribal Wars Lite</span>
        <nav class="hidden md:flex space-x-4">
          <RouterLink to="/" exactActiveClass="text-blue-600 font-medium underline">
            Overview
          </RouterLink>
          <RouterLink to="/map" exactActiveClass="text-blue-600 font-medium underline">
            Map
          </RouterLink>
        </nav>
      </div>
      <button @click="auth.logout" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
    </header>

    <!-- Page Content -->
    <main class="flex-1 overflow-hidden bg-gray-50">
      <RouterView />
    </main>
  </div>
</template>
