<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import { useVillageStore } from '@/stores/village'
import { useAuthStore } from "@/stores/auth.ts";
import type { Village } from '@/types/village' // type voor jouw villages (optioneel)

// Stores
const auth = useAuthStore();
const villageStore = useVillageStore()

// Constants & State
const gridSize = 100
const cellSize = ref<number>(50)
const totalCells = gridSize * gridSize

const selectedCell = ref<{ x: number | null, y: number | null }>({ x: null, y: null })
const showDrawer = ref<boolean>(false)
const mapContainer = ref<HTMLElement | null>(null)

const tooltip = ref<{ show: boolean, x: number, y: number, village: Village | null }>({ show: false, x: 0, y: 0, village: null })
const activeVillage = ref<Village | null>(null)

// Functions
const getCellX = (index: number): number => ((index - 1) % gridSize) + 1

const getCellY = (index: number): number => Math.floor((index - 1) / gridSize) + 1

const selectCell = (x: number, y: number): void => {
  selectedCell.value = { x, y }
}

const isVillageCell = (x: number, y: number): boolean => {
  return villageStore.villages.some(v => v.coordinates.x === x && v.coordinates.y === y)
}

const toVillageColorClass = (village: object) => {
  if (!village.player.id) {
    return 'bg-gray-900';
  }

  return isPlayerVillage(village) ? 'bg-cyan-500' : 'bg-yellow-500';
}

const isPlayerVillage = (village: object) => {
  return village.player?.id === auth.user.id;
}

const centerMapOnVillage = (village: Village, closeDrawer = false): void => {
  if (!village?.coordinates) return
  selectedCell.value = { x: village.coordinates.x, y: village.coordinates.y }
  nextTick(() => {
    smoothScrollToCell(village.coordinates.x, village.coordinates.y)
  })
  if (closeDrawer) showDrawer.value = false
}

const smoothScrollToCell = (x: number, y: number): void => {
  if (!mapContainer.value) return
  const cellCenterX = (x - 0.5) * cellSize.value
  const cellCenterY = (y - 0.5) * cellSize.value
  const container = mapContainer.value
  const scrollLeft = cellCenterX - container.clientWidth / 2
  const scrollTop = cellCenterY - container.clientHeight / 2
  container.scrollTo({ left: scrollLeft, top: scrollTop, behavior: 'smooth' })
}

const zoomIn = (): void => {
  if (cellSize.value < 100) cellSize.value += 10
}

const zoomOut = (): void => {
  if (cellSize.value > 20) cellSize.value -= 10
}

// Drag & Pan
let isPanning = false
let startX = 0
let startY = 0
let scrollLeft = 0
let scrollTop = 0

const onMouseDown = (e: MouseEvent): void => {
  if (!mapContainer.value) return
  isPanning = true
  startX = e.pageX - mapContainer.value.offsetLeft
  startY = e.pageY - mapContainer.value.offsetTop
  scrollLeft = mapContainer.value.scrollLeft
  scrollTop = mapContainer.value.scrollTop
}

const onMouseMove = (e: MouseEvent): void => {
  if (!isPanning || !mapContainer.value) return
  e.preventDefault()
  const x = e.pageX - mapContainer.value.offsetLeft
  const y = e.pageY - mapContainer.value.offsetTop
  mapContainer.value.scrollLeft = scrollLeft - (x - startX)
  mapContainer.value.scrollTop = scrollTop - (y - startY)
}

const onMouseUp = (): void => {
  isPanning = false
}

const onTouchStart = (e: TouchEvent): void => {
  if (e.touches.length !== 1 || !mapContainer.value) return
  isPanning = true
  startX = e.touches[0].pageX - mapContainer.value.offsetLeft
  startY = e.touches[0].pageY - mapContainer.value.offsetTop
  scrollLeft = mapContainer.value.scrollLeft
  scrollTop = mapContainer.value.scrollTop
}

const onTouchMove = (e: TouchEvent): void => {
  if (!isPanning || !mapContainer.value) return
  e.preventDefault()
  const x = e.touches[0].pageX - mapContainer.value.offsetLeft
  const y = e.touches[0].pageY - mapContainer.value.offsetTop
  mapContainer.value.scrollLeft = scrollLeft - (x - startX)
  mapContainer.value.scrollTop = scrollTop - (y - startY)
}

const onTouchEnd = (): void => {
  isPanning = false
}

// Tooltip & Modal
const showTooltip = (e: MouseEvent, village: Village): void => {
  tooltip.value = { show: true, x: e.clientX + 10, y: e.clientY + 10, village }
}

const hideTooltip = (): void => {
  tooltip.value.show = false
}

const openVillageModal = (village: Village): void => {
  activeVillage.value = village
}

const closeVillageModal = (): void => {
  activeVillage.value = null
}

const getVillageAt = (x: number, y: number): Village | undefined => {
  return villageStore.villages.find(v => v.coordinates.x === x && v.coordinates.y === y)
}

// Lifecycle
onMounted(() => {
  const el = mapContainer.value
  if (!el) return

  el.addEventListener('mousedown', onMouseDown)
  el.addEventListener('mousemove', onMouseMove)
  el.addEventListener('mouseup', onMouseUp)
  el.addEventListener('mouseleave', onMouseUp)

  el.addEventListener('touchstart', onTouchStart)
  el.addEventListener('touchmove', onTouchMove, { passive: false })
  el.addEventListener('touchend', onTouchEnd)
  el.addEventListener('touchcancel', onTouchEnd)

  villageStore.fetchPlayerVillages()
  villageStore.fetchAllVillages()
})

onUnmounted(() => {
  const el = mapContainer.value
  if (!el) return

  el.removeEventListener('mousedown', onMouseDown)
  el.removeEventListener('mousemove', onMouseMove)
  el.removeEventListener('mouseup', onMouseUp)
  el.removeEventListener('mouseleave', onMouseUp)

  el.removeEventListener('touchstart', onTouchStart)
  el.removeEventListener('touchmove', onTouchMove)
  el.removeEventListener('touchend', onTouchEnd)
  el.removeEventListener('touchcancel', onTouchEnd)
})
</script>

<template>
  <div class="flex h-full overflow-hidden">
    <!-- Sidebar (desktop only) -->
    <aside class="hidden md:block w-72 bg-blue-50 p-4 overflow-y-auto border-r border-gray-200">
      <h2 class="font-semibold mb-4">Your Villages</h2>
      <ul class="space-y-2">
        <li
          v-for="village in villageStore.playerVillages"
          :key="village.id"
          @click="centerMapOnVillage(village)"
          class="p-3 bg-white rounded shadow hover:bg-blue-100 cursor-pointer"
        >
          <div class="font-semibold">{{ village.name }}</div>
          <div class="text-sm text-gray-500">Coords: ({{ village.coordinates.x }}, {{ village.coordinates.y }})</div>
        </li>
      </ul>
    </aside>

    <!-- Map grid area -->
    <main
      ref="mapContainer"
      class="flex-1 relative overflow-auto bg-green-100"
      :class="{'cursor-grab': !isPanning, 'cursor-grabbing': isPanning, 'user-select-none': isPanning}"
    >
      <div
        class="relative grid map-grid"
        :style="`grid-template-columns: repeat(${gridSize}, ${cellSize}px); grid-template-rows: repeat(${gridSize}, ${cellSize}px); width: ${gridSize * cellSize}px; height: ${gridSize * cellSize}px;`"
      >
        <div
          v-for="index in totalCells"
          :key="index"
          :style="`width: ${cellSize}px; height: ${cellSize}px;`"
        >
          <div
            v-if="isVillageCell(getCellX(index), getCellY(index))"
            @mouseenter="showTooltip($event, getVillageAt(getCellX(index), getCellY(index)))"
            @mouseleave="hideTooltip"
            @click="openVillageModal(getVillageAt(getCellX(index), getCellY(index)))"
            :class="[
    'border border-gray-500 flex items-center justify-center cursor-pointer text-xs hover:ring-2 hover:ring-blue-300',
    toVillageColorClass(getVillageAt(getCellX(index), getCellY(index))),
    selectedCell.x === getCellX(index) && selectedCell.y === getCellY(index) ? 'ring-4 ring-yellow-400' : ''
  ]"
            :style="`width: ${cellSize}px; height: ${cellSize}px;`"
          >
<!--            {{ getCellX(index) }},{{ getCellY(index) }}-->
          </div>
          <div
            v-else
            class="border border-gray-300 flex items-center justify-center bg-green-200 text-xs"
            :style="`width: ${cellSize}px; height: ${cellSize}px;`"
          >
<!--            {{ getCellX(index) }},{{ getCellY(index) }}-->
          </div>
        </div>
      </div>

      <div
        v-if="tooltip.show"
        :style="`top: ${tooltip.y}px; left: ${tooltip.x}px;`"
        class="fixed bg-black bg-opacity-80 text-white text-xs px-2 py-1 rounded z-50 pointer-events-none"
      >
        {{ tooltip.village.name }} ({{ tooltip.village.coordinates.x }}, {{ tooltip.village.coordinates.y }})
      </div>

      <div v-if="activeVillage"
           class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded shadow w-64">
          <h2 class="font-bold mb-2">{{ activeVillage.name }}</h2>
          <p class="text-sm text-gray-500">Coords: (
            {{ activeVillage.coordinates.x }}, {{ activeVillage.coordinates.y }})
          </p>
          <div class="mt-4 space-y-2">
            <button class="bg-blue-500 text-white px-4 py-2 rounded w-full">Attack</button>
            <button class="bg-green-500 text-white px-4 py-2 rounded w-full">Spy</button>
            <button class="bg-yellow-500 text-white px-4 py-2 rounded w-full">Info</button>
          </div>
          <button @click="closeVillageModal" class="mt-4 text-gray-600 underline w-full">Close
          </button>
        </div>
      </div>
    </main>

    <!-- Floating controls (Fixed to viewport) -->
    <div class="fixed bottom-4 right-4 flex flex-col space-y-2 z-50">
      <button @click="zoomIn" class="bg-white shadow rounded-full p-3 hover:bg-blue-100">+</button>
      <button @click="zoomOut" class="bg-white shadow rounded-full p-3 hover:bg-blue-100">-</button>
    </div>

    <!-- Mobile drawer trigger -->
    <div class="fixed bottom-4 left-4 md:hidden z-50">
      <button @click="showDrawer = true" class="bg-white shadow rounded-full p-3 hover:bg-blue-100">
        =
      </button>
    </div>

    <!-- Mobile Drawer -->
    <transition name="slide">
      <div v-if="showDrawer" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex flex-col">
        <div class="bg-white p-4 flex-1 overflow-y-auto">
          <button @click="showDrawer = false" class="mb-4 text-gray-700">Close</button>
          <h2 class="font-semibold mb-4">Your Villages</h2>
          <ul class="space-y-2">
            <li
              v-for="village in villageStore.playerVillages"
              :key="village.id"
              @click="centerMapOnVillage(village, true)"
              class="p-3 bg-gray-100 rounded hover:bg-blue-100 cursor-pointer"
            >
              <div class="font-semibold">{{ village.name }}</div>
              <div class="text-sm text-gray-500">Coords: ({{ village.coordinates.x }}, {{ village.coordinates.y }})</div>
            </li>
          </ul>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.map-grid {
  transition: all 0.2s ease-in-out;
}

.cursor-grab {
  cursor: grab;
}

.cursor-grabbing {
  cursor: grabbing;
}

.user-select-none {
  user-select: none;
}
</style>
