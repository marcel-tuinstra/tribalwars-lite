<script setup lang="ts">
import {RouterView} from 'vue-router'

import MenuDock from "@/components/MenuDock.vue";
</script>

<template>
  <div class="flex flex-col h-screen">
    <!-- Page Content -->
    <main class="flex-1 overflow-hidden">
      <RouterView />
    </main>

    <MenuDock/>
  </div>
</template>
