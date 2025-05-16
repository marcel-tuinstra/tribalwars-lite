<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  buildings: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['building-click'])

function handleBuildingClick(building) {
  emit('building-click', building)
}

// Mapping functions
function getBuildingColor(category) {
  switch (category) {
    case 'lumber_camp': return '#4caf50'
    case 'clay_pit': return '#a0522d'
    case 'iron_mine': return '#607d8b'
    case 'barracks': return '#888'
    case 'farm': return '#ff9800'
    case 'warehouse': return '#795548'
    default: return '#999'
  }
}

function getBuildingName(category) {
  switch (category) {
    case 'lumber_camp': return 'Lumber Camp'
    case 'clay_pit': return 'Clay Pit'
    case 'iron_mine': return 'Iron Mine'
    case 'barracks': return 'Barracks'
    case 'farm': return 'Farm'
    case 'warehouse': return 'Warehouse'
    default: return 'Building'
  }
}

// Nieuwe functie: bepaalt de x/y per category
function getBuildingCoordinates(type) {
  const mapping = {
    'lumber_camp': 0,
    'clay_pit': 1,
    'iron_mine': 2,
    'barracks': 3,
    'farm': 4,
    'warehouse': 5,
  }
  const index = mapping[type] ?? 0;
  const angle = (Math.PI * 2 / 6) * index;
  const radius = 20;
  return {
    x: 50 + Math.cos(angle) * radius,
    y: 50 + Math.sin(angle) * radius,
  };
}
</script>

<template>
  <svg
    viewBox="0 0 100 100"
    class="w-full h-auto max-h-[400px] bg-green-100 rounded shadow"
    xmlns="http://www.w3.org/2000/svg"
  >
    <!-- Village wall -->
    <circle cx="50" cy="50" r="45" fill="#ddd" stroke="#333" stroke-width="2" />

    <!-- HQ always in center -->
    <g @click="handleBuildingClick({ name: 'Headquarters', type: 'hq', level: 10 })" cursor="pointer">
      <rect x="46" y="46" width="8" height="8" fill="#555" stroke="#222" stroke-width="0.5" />
      <text x="50" y="44" text-anchor="middle" font-size="4" fill="#000">1</text>
      <title>Headquarters (Level 1)</title>
    </g>

    <!-- Dynamic buildings -->
    <g
      v-for="building in buildings"
      :key="building.category"
      v-if="building?.level > 0"
      @click="handleBuildingClick(building)"
      cursor="pointer"
    >
      <circle
        :cx="getBuildingCoordinates(building.category).x"
        :cy="getBuildingCoordinates(building.category).y"
        r="4"
        :fill="getBuildingColor(building.category)"
      />
      <text
        :x="getBuildingCoordinates(building.category).x"
        :y="getBuildingCoordinates(building.category).y - 6"
        text-anchor="middle"
        font-size="4"
        fill="#000"
        font-weight="bold"
      >
        {{ building.level || 1 }}
      </text>
      <title>{{ getBuildingName(building.category) }} (Level {{ building.level || 1 }})</title>
    </g>
  </svg>
</template>
