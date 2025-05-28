<script setup lang="ts">
import {defineProps} from "vue";
import {resourceToName} from "@/utils/village.ts";

defineProps({
  village: {
    type: Object,
    required: true
  },
  x: {
    type: Number,
    required: true
  },
  y: {
    type: Number,
    required: true
  },
})
</script>

<template>
  <div
    :style="`position: absolute; top: ${y}px; left: ${x}px; transform: translate(-50%, -100%)`"
    class="bg-base-200 bg-opacity-90 text-sm px-3 py-2 rounded shadow z-50 pointer-events-none w-max min-w-[200px]"
  >
    <!-- Village Name -->
    <h6 class="font-semibold leading-tight flex items-center gap-2">
      {{ village.name }}
      <span class="bg-base-300 text-xs font-semibold px-2 py-0.5 rounded">
            Lv {{ village.level }}
          </span>
    </h6>

    <!-- Coordinates -->
    <div class="text-xs uppercase font-semibold opacity-60 mb-2">
      {{ village.x }}, {{ village.y }}
    </div>

    <!-- Resources -->
    <div
      class="flex flex-wrap gap-2 text-xs font-semibold"
      v-if="village.resources && village.resources.length"
    >
      <div
        v-for="resource in village.resources"
        :key="resource.category"
        class="flex items-center gap-1 rounded px-2 py-1 bg-base-300 text-base-content"
      >
<!--        <img-->
<!--          :src="resourceToIcon(resource.category)"-->
<!--          :alt="resourceToName(resource.category)"-->
<!--          class="w-4 h-4"-->
<!--        />-->
        <span>{{ resource.amount }}</span>
      </div>
    </div>
  </div>
</template>
