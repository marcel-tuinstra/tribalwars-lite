<template>
  <div class="flex flex-col md:flex-row h-full bg-gray-50">
    <!-- Left: Compact villages list -->
    <aside class="md:w-1/3 bg-white shadow p-4 overflow-y-auto border-b md:border-b-0 md:border-r border-gray-200">
      <h2 class="font-bold text-lg mb-4">Your Villages</h2>
      <table class="w-full text-sm">
        <thead class="hidden md:table-header-group sticky top-0 z-10 bg-white">
        <tr>
          <th class="text-left px-2 py-1">Village</th>
          <th class="text-left px-2 py-1">Coords</th>
          <th class="text-left px-2 py-1">Pop</th>
          <th class="text-left px-2 py-1">Lvl</th>
        </tr>
        </thead>
        <tbody>
        <tr
          v-for="village in villageStore.playerVillages"
          :key="village.id"
          @click="selectVillage(village)"
          :class="['cursor-pointer hover:bg-blue-100', selectedVillage.id === village.id ? 'bg-blue-200 font-bold' : '']"
        >
          <td class="px-2 py-1">{{ village.name }}</td>
          <td class="px-2 py-1">({{ village.x }}, {{ village.y }})</td>
          <td class="px-2 py-1">
            {{ village.resources.find(r => r.category === 'population')?.amount ?? 'N/A' }}
          </td>
          <td class="px-2 py-1">
            {{ village.level }}
          </td>
        </tr>
        </tbody>
      </table>
    </aside>

    <!-- Right: Village detail UI (graphic, resources, actions) -->
    <main class="flex-1 p-4 overflow-y-auto">
      <template v-if="villageStore.playerVillages.length && selectedVillage">
        <h2 class="font-bold text-2xl mb-4">{{ selectedVillage.name }}</h2>

        <div class="flex flex-col md:flex-row gap-4">
          <!-- Village Visual (placeholder image, future interactive) -->
          <div class="bg-gray-200 rounded-lg flex-1 flex items-center justify-center min-h-[300px]">
            <VillageMap :buildings="selectedVillage.buildings" @building-click="onBuildingClick" />
          </div>

          <!-- Info panel -->
          <div class="bg-white rounded shadow p-4 flex-1 space-y-4">
            <div>
              <h3 class="font-semibold mb-2">Resources</h3>
              <ul class="space-y-1">
                <li v-for="resource in selectedVillage.resources">{{ resource.category }}: {{ resource.amount }}</li>
              </ul>
            </div>
            <div>
              <h3 class="font-semibold mb-2">Troops</h3>
              <ul class="space-y-1">
                <li v-for="troop in selectedVillage.troops">{{ troop.role }}: {{ troop.amount }}</li>
              </ul>
            </div>
            <div>
              <h3 class="font-semibold mb-2">Actions</h3>
              <div class="flex gap-2 flex-wrap">
                <button class="bg-blue-600 text-white px-3 py-2 rounded">Attack</button>
                <button class="bg-green-600 text-white px-3 py-2 rounded">Recruit</button>
                <button class="bg-yellow-500 text-white px-3 py-2 rounded">Build</button>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="text-gray-500 text-lg">No villages available. Go conquer!</div>
      </template>
    </main>
  </div>
</template>

<script setup>
import {ref, watchEffect} from 'vue'
import VillageMap from '@/components/overview/VillageMap.vue'

import { onMounted } from 'vue';
import { useVillageStore } from '@/stores/village';

const villageStore = useVillageStore();

// Fetch player villages on mount
onMounted(() => {
  villageStore.fetchPlayerVillages();
});

const selectedVillage = ref(null);

watchEffect(() => {
  if (villageStore.playerVillages.length) {
    selectedVillage.value = villageStore.playerVillages[0];
  }
});

function selectVillage(village) {
  selectedVillage.value = village
}

function onBuildingClick(building) {
  alert(`Clicked on ${getBuildingDisplayName(building.type)}`)
}

// Optional helper om display name te krijgen, zodat alles clean blijft
function getBuildingDisplayName(type) {
  switch (type) {
    case 'lumber_camp': return 'Lumber Camp'
    case 'clay_pit': return 'Clay Pit'
    case 'iron_mine': return 'Iron Mine'
    case 'barracks': return 'Barracks'
    case 'farm': return 'Farm'
    case 'warehouse': return 'Warehouse'
    case 'hq': return 'Headquarters'
    default: return 'Unknown Building'
  }
}

</script>
