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
  <header>
    <div class="wrapper">
      <nav>
        <RouterLink to="/">Dashboard</RouterLink>
        <RouterLink to="/map">Map</RouterLink>

        <button v-if="auth.user" @click="auth.logout">Logout</button>
      </nav>
    </div>
  </header>

  <RouterView/>
</template>

<style scoped>

</style>
