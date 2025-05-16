<script setup lang="ts">
import {useVillageStore} from '@/stores/village';
import {onMounted} from "vue";
import {useAuthStore} from "@/stores/auth.ts";

const TILE_SIZE = 44; // always match to grid-template-columns
const auth = useAuthStore();
const villageStore = useVillageStore();

const isVillage = (i: number) => {
  const coordinates = getCoordinates(i);

  return villageStore.villages.some(v => v.coordinates.x === coordinates.x && v.coordinates.y === coordinates.y);
};

const isPlayerVillage = (village: object) => {
  console.log(village);

  return village.player?.id === auth.user.id;
}

const getCoordinates = (i: number) => {
  const x = ((i - 1) % 100) + 1;
  const y = Math.floor((i - 1) / 100) + 1;

  return {'x': x, 'y': y};
}

const toVillageColorClass = (village: object) => {
  if (!village.player.id) {
    return 'bg-gray-900';
  }

  return isPlayerVillage(village) ? 'bg-cyan-500' : 'bg-yellow-500';
}

const scrollToVillage = (village: object) => {
  const el = document.getElementById(`village-${village.coordinates.x}-${village.coordinates.y}`);
  console.log(el);
  if (el) {
    el.scrollIntoView({
      behavior: 'smooth',
      block: 'center',
      inline: 'center'
    });
  }
}

onMounted(() => {
  villageStore.fetchPlayerVillages();
  villageStore.fetchAllVillages();
})
</script>

<template>
  <div class="container mx-auto flex justify-between">
    <!-- Sidebar -->
    <div class="w-80 h-screen bg-blue-100 overflow-y-auto">
      <h2 class="text-xl font-semibold mb-2">Your Villages</h2>
      <ul class="space-y-2">
        <li v-for="playerVillage in villageStore.playerVillages" :key="playerVillage.id"
            class="border p-4 rounded bg-white shadow">
          <div class="font-bold" @click="scrollToVillage(playerVillage)">{{ playerVillage.name }}</div>
          <div>Coordinates: ({{ playerVillage.coordinates.x }}, {{ playerVillage.coordinates.y }})</div>
        </li>
      </ul>
    </div>

    <!-- Map Scroll Container -->
    <div class="bg-gray-100 map-container">
      <!-- Map Grid including axes -->
      <div
        class="grid"
        style="grid-template-columns: 44px repeat(100, 44px); grid-template-rows: 20px repeat(100, 44px);">

        <!-- Top-left empty block -->
        <div class="bg-gray-100 border-b border-r"></div>

        <!-- Top Axis -->
        <div
          v-for="x in 100"
          :key="'x'+x"
          class="w-11 h-5 text-xs text-center text-gray-700 bg-gray-100 border-b border-r">
          {{ x }}
        </div>

        <!-- Y Axis + Map -->
        <template v-for="y in 100" :key="'y'+y">
          <!-- Left Axis -->
          <div class="w-11 h-11 text-xs text-right text-gray-700 pr-1 bg-gray-100 border-b border-r">
            {{ y }}
          </div>

          <!-- Map Row -->
          <div
            v-for="x in 100"
            :key="'cell-'+x+'-'+y"
            class="relative w-11 h-11 border border-gray-300 bg-green-200">

            <!-- Village if matches -->
            <div
              v-for="village in villageStore.villages.filter(v => v.coordinates.x === x && v.coordinates.y === y)"
              :key="village.id"
              :id="'village-' + village.coordinates.x + '-' + village.coordinates.y"
              class="w-8 h-8 rounded-full relative z-10 group"
              :class="toVillageColorClass(village)">

              <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap z-50 pointer-events-none opacity-0 group-hover:opacity-100">
                <div class="font-bold">{{ village.name }}</div>
                <div>Coordinates: ({{ village.coordinates.x }}, {{ village.coordinates.y }})</div>
                <hr>
                <div>Owner: {{ village.player.email ?? 'BOT' }}</div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.map-container {
  width: 48rem;
  height: 48rem;


  overflow: auto;
  position: relative;
}
</style>
