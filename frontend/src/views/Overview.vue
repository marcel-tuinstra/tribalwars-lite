<script setup lang="ts">
import { onMounted, ref, watchEffect } from 'vue'
import VillageMap from '@/components/overview/VillageMap.vue'
import { useVillageStore } from '@/stores/village'
import { useAuthStore } from '@/stores/auth'
import VillageListItem from '@/components/map/VillageListItem.vue'
import MenuSide from '@/components/MenuSide.vue'
import { buildingToName } from '@/utils/village.ts'

const villageStore = useVillageStore()

// Fetch player villages on mount
onMounted(() => {
  villageStore.fetchPlayerVillages()
})

const selectedVillage = ref(null)

watchEffect(() => {
  if (villageStore.playerVillages.length) {
    selectedVillage.value = villageStore.playerVillages[0]
  }
})

function selectVillage(village) {
  selectedVillage.value = village
}

function onBuildingClick(building) {
  alert(`Clicked on ${buildingToName(building.type)}`)
}
</script>

<template>
  <div class="flex flex-col md:flex-row h-full bg-base-100">
    <aside class="hidden md:block w-100 m-4 rounded-box">
      <MenuSide />

      <ul class="list bg-base-200 rounded-box shadow-md overflow-y-auto overflow-x-hidden max-h-[calc(100vh-8rem)]">
        <li class="p-4 pb-2 tracking-wide font-bold text-lg">Your Villages</li>

        <VillageListItem
          v-for="village in villageStore.playerVillages"
          :key="village.id"
          :village="village"
          @list-click="(v) => selectVillage(v)"
        />
      </ul>
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
                <li v-for="resource in selectedVillage.resources">
                  {{ resource.category }}:
                  {{ resource.amount }}
                </li>
              </ul>
            </div>
            <div>
              <h3 class="font-semibold mb-2">Troops</h3>
              <ul class="space-y-1">
                <li v-for="troop in selectedVillage.troops">
                  {{ troop.role }}: {{ troop.amount }}
                </li>
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
