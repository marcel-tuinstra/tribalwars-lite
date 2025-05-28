<script setup lang="ts">
import {defineEmits, defineProps} from "vue";
import {buildingToCap, resourceToName} from "@/utils/village.ts";
import { PhWarning, PhLog, PhCube, PhBread, PhUsersThree, PhWarehouse } from '@phosphor-icons/vue'

const props = defineProps({
  village: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['list-click'])

function handleListClick() {
  emit('list-click', props.village)
}

</script>

<template>
  <li
    class="mx-2 mb-3 cursor-pointer hover:bg-base-100 transition px-2 py-1 rounded"
    @click="handleListClick"
  >
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-2 w-full">
        <img
          class="w-12 h-12 rounded"
          :alt="village.name"
          :src="`/images/map/player_village_${village.level}.png`"
        />
        <div class="text-sm font-medium">
          {{ village.name }}

          <div class="flex items-center gap-2 mt-1">
            <span class="bg-base-300 text-xs font-semibold px-2 py-0.5 rounded">
              ({{ village.x }}, {{ village.y }})
            </span>
            <span class="bg-base-300 text-xs font-semibold px-2 py-0.5 rounded">
              Lv {{ village.level }}
            </span>
          </div>
        </div>

        <div class="avatar indicator tooltip tooltip-left ml-auto mr-1" :data-tip="village.counters.incomingAttacks + 'x incoming attacks'" v-if="village.counters.incomingAttacks">
          <span class="indicator-item badge badge-xs badge-warning px-1">{{ village.counters.incomingAttacks }}</span>
          <div class="h-5 w-5">
            <PhWarning :size="18" weight="duotone"/>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-between gap-3 mt-2 items-center text-xs text-base-content">
      <div v-for="resource in village.resources" :key="resource.category" class="flex gap-1 tooltip" :data-tip="resourceToName(resource.category)">
        <PhLog :size="18" weight="duotone" v-if="resource.category === 'wood'"/>
        <PhCube :size="18" weight="duotone" v-if="resource.category === 'stone'"/>
        <PhBread :size="18" weight="duotone" v-if="resource.category === 'food'"/>
        <span>{{ resource.amount }}</span>
      </div>
      <div v-for="(cap, key) in village.caps" :key="key" class="flex gap-1 tooltip" :data-tip="buildingToCap(key)">
        <PhWarehouse :size="18" weight="duotone" v-if="key === 'storage'"/>
        <PhUsersThree :size="18" weight="duotone" v-if="key === 'population'"/>
        <span v-if="key === 'population'">{{ village.counters.population }} /</span>
        <span>{{ cap }}</span>
      </div>
    </div>
  </li>
</template>
