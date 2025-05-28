<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useVillageStore } from '@/stores/village'
import type { Village } from '@/types/village'
import { useAuthStore } from '@/stores/auth.ts'
import VillageListItem from '@/components/map/VillageListItem.vue'
import VillageTooltip from '@/components/map/VillageTooltip.vue'
import MenuSide from "@/components/MenuSide.vue";

const auth = useAuthStore()

const villageStore = useVillageStore()

const canvas = ref<HTMLCanvasElement | null>(null)
const cellSize = ref(60)
const gridSize = 200
const canvasSize = ref(gridSize * cellSize.value)

let ctx: CanvasRenderingContext2D | null = null

const imageCache: Record<string, HTMLImageElement> = {}

const terrainMap: Record<string, string> = {}

function getCachedImage(src: string): HTMLImageElement {
  if (!imageCache[src]) {
    const img = new Image()
    img.src = src
    imageCache[src] = img
  }
  return imageCache[src]
}

let isDragging = false
let offsetX = 0
let offsetY = 0
let dragStartX = 0
let dragStartY = 0

let lastHoverVillageId: number | null = null

const showDrawer = ref(false)

const hoverVillage = ref<Village | null>(null)
const tooltip = ref<{ show: boolean; x: number; y: number; village: Village | null }>({
  show: false,
  x: 0,
  y: 0,
  village: null,
})

function draw() {
  if (!ctx || !canvas.value) return
  const width = canvas.value.width
  const height = canvas.value.height
  ctx.fillStyle = '#e5e7eb' // Tailwind gray-200
  ctx.fillRect(0, 0, width, height)
  ctx.clearRect(0, 0, width, height)

  ctx.save()
  let fillColor = '#64a30d' // Default green

  if (villageStore.villages.length) {
    const playerVillage = villageStore.villages.find((v) => v.playerId === auth.user?.id)
    const enemyVillage = villageStore.villages.find(
      (v) => v.playerId && v.playerId !== auth.user?.id,
    )

    if (playerVillage) {
      fillColor = '#facc15' // Tailwind yellow-100 for own territory
    } else if (enemyVillage) {
      fillColor = '#713e12' // Tailwind red-100 for enemy territory
    } else {
      fillColor = '#a3a3a3' // Tailwind green-50 for barbarian only
    }
  }

  ctx.fillStyle = fillColor
  ctx.fillRect(offsetX, offsetY, gridSize * cellSize.value, gridSize * cellSize.value)
  ctx.strokeStyle = '#3f6212'
  ctx.lineWidth = 1
  for (let i = 0; i <= gridSize; i++) {
    const gx = i * cellSize.value + offsetX
    const gy = i * cellSize.value + offsetY

    // Only draw vertical lines within grid bounds
    if (gx >= offsetX && gx <= gridSize * cellSize.value + offsetX) {
      ctx.beginPath()
      ctx.moveTo(gx, offsetY)
      ctx.lineTo(gx, offsetY + gridSize * cellSize.value)
      ctx.stroke()
    }

    // Only draw horizontal lines within grid bounds
    if (gy >= offsetY && gy <= gridSize * cellSize.value + offsetY) {
      ctx.beginPath()
      ctx.moveTo(offsetX, gy)
      ctx.lineTo(offsetX + gridSize * cellSize.value, gy)
      ctx.stroke()
    }
  }

  ctx.restore()

  // Build a lookup for village positions
  const occupied = new Set(villageStore.villages.map((v) => `${v.x},${v.y}`))

  // Draw grass or forest in empty cells
  for (let x = 1; x <= gridSize; x++) {
    for (let y = 1; y <= gridSize; y++) {
      if (occupied.has(`${x},${y}`)) continue

      const drawX = (x - 1) * cellSize.value + offsetX
      const drawY = (y - 1) * cellSize.value + offsetY

      if (
        drawX + cellSize.value < 0 ||
        drawX > canvas.value!.width ||
        drawY + cellSize.value < 0 ||
        drawY > canvas.value!.height
      )
        continue

      const key = `${x},${y}`
      if (!terrainMap[key]) {
        const randomGrassTile = Math.floor(Math.random() * 4) + 1
        const rand = Math.random()

        if (rand < 0.02) {
          terrainMap[key] = '/images/map/sea.png'
        } else if (rand < 0.2) {
          terrainMap[key] = '/images/map/forest.png'
        } else {
          terrainMap[key] = `/images/map/grass_${randomGrassTile}.png`
        }
      }
      const imgSrc = terrainMap[key]
      const img = getCachedImage(imgSrc)
      if (img.complete) {
        ctx.drawImage(img, drawX, drawY, cellSize.value, cellSize.value)
      } else {
        img.onload = () => {
          draw()
        }
      }
    }
  }

  for (const village of villageStore.villages) {
    const { x, y } = village
    const drawX = (x - 1) * cellSize.value + offsetX
    const drawY = (y - 1) * cellSize.value + offsetY
    if (drawX + cellSize.value < 0 || drawX > width || drawY + cellSize.value < 0 || drawY > height)
      continue

    ctx.save()
    const imgSrc = !village.playerId
      ? `/images/map/barbarian_village_${village.level}.png`
      : `/images/map/player_village_${village.level}.png`
    const img = getCachedImage(imgSrc)
    if (img.complete) {
      ctx.drawImage(img, drawX, drawY, cellSize.value, cellSize.value)
    } else {
      img.onload = () => {
        draw()
      }
    }
    ctx.restore()
  }

  // Draw top-left cell and labels after everything else
  const baseColor = getComputedStyle(document.documentElement)
    .getPropertyValue('--color-base-200')
    .trim()
  const baseContentColor = getComputedStyle(document.documentElement)
    .getPropertyValue('--color-base-content')
    .trim()
  ctx.save()
  ctx.fillStyle = baseColor
  ctx.fillRect(0, 0, cellSize.value, cellSize.value)
  ctx.restore()

  for (let i = 0; i < gridSize; i++) {
    const gx = i * cellSize.value + offsetX
    const gy = i * cellSize.value + offsetY

    if (gx >= 0 && gx <= width) {
      ctx.save()
      ctx.fillStyle = baseColor
      ctx.fillRect(gx, 0, cellSize.value, cellSize.value)
      ctx.fillStyle = baseContentColor
      ctx.font = '12px sans-serif'
      ctx.textAlign = 'center'
      ctx.textBaseline = 'middle'
      ctx.fillText((i + 1).toString(), gx + cellSize.value / 2, cellSize.value / 2)
      ctx.restore()
    }

    if (gy >= 0 && gy <= height) {
      ctx.save()
      ctx.fillStyle = baseColor
      ctx.fillRect(0, gy, cellSize.value, cellSize.value)
      ctx.fillStyle = baseContentColor
      ctx.font = '12px sans-serif'
      ctx.textAlign = 'center'
      ctx.textBaseline = 'middle'
      ctx.fillText((i + 1).toString(), cellSize.value / 2, gy + cellSize.value / 2)
      ctx.restore()
    }
  }
}

function onMouseDown(e: MouseEvent) {
  isDragging = true
  dragStartX = e.clientX
  dragStartY = e.clientY
}

function onMouseMove(e: MouseEvent) {
  const rect = canvas.value!.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  let village = villageStore.playerVillages.find((v) => {
    const drawX = (v.x - 1) * cellSize.value + offsetX
    const drawY = (v.y - 1) * cellSize.value + offsetY
    return x >= drawX && x <= drawX + cellSize.value && y >= drawY && y <= drawY + cellSize.value
  })

  if (!village) {
    village = villageStore.villages.find((v) => {
      const drawX = (v.x - 1) * cellSize.value + offsetX
      const drawY = (v.y - 1) * cellSize.value + offsetY
      return x >= drawX && x <= drawX + cellSize.value && y >= drawY && y <= drawY + cellSize.value
    })
  }

  if (isDragging) {
    const dx = e.clientX - dragStartX
    const dy = e.clientY - dragStartY
    if (Math.abs(dx) > 0 || Math.abs(dy) > 0) {
      dragStartX = e.clientX
      dragStartY = e.clientY
      offsetX += dx
      offsetY += dy
      tooltip.value.show = false
      draw()
    }
  } else if (village) {
    if (lastHoverVillageId !== village.id) {
      lastHoverVillageId = village.id
      hoverVillage.value = village
      tooltip.value = {
        show: true,
        x: (village.x - 1) * cellSize.value + offsetX + cellSize.value / 2,
        y: (village.y - 1) * cellSize.value + offsetY - 10,
        village,
      }
      draw()
    }
  } else {
    if (lastHoverVillageId !== null) {
      lastHoverVillageId = null
      hoverVillage.value = null
      tooltip.value.show = false
      draw()
    }
  }
}

function onMouseUp() {
  isDragging = false
}

function zoomIn() {
  if (cellSize.value < 60) {
    const centerX = (canvas.value!.clientWidth / 2 - offsetX) / cellSize.value
    const centerY = (canvas.value!.clientHeight / 2 - offsetY) / cellSize.value
    cellSize.value += 15
    offsetX = canvas.value!.clientWidth / 2 - centerX * cellSize.value
    offsetY = canvas.value!.clientHeight / 2 - centerY * cellSize.value
    updateCanvasSize()
  }
}

function zoomOut() {
  if (cellSize.value > 30) {
    const centerX = (canvas.value!.clientWidth / 2 - offsetX) / cellSize.value
    const centerY = (canvas.value!.clientHeight / 2 - offsetY) / cellSize.value
    cellSize.value -= 15
    offsetX = canvas.value!.clientWidth / 2 - centerX * cellSize.value
    offsetY = canvas.value!.clientHeight / 2 - centerY * cellSize.value
    updateCanvasSize()
  }
}

function updateCanvasSize() {
  if (!canvas.value) return
  const ratio = window.devicePixelRatio || 1
  const vw = canvas.value?.clientWidth || window.innerWidth
  const vh = canvas.value?.clientHeight || window.innerHeight

  canvas.value.width = vw * ratio
  canvas.value.height = vh * ratio
  canvas.value.style.width = `${vw}px`
  canvas.value.style.height = `${vh}px`
  ctx = canvas.value.getContext('2d')
  ctx?.scale(ratio, ratio)
  draw()
}

function centerMap() {
  const vw = canvas.value?.clientWidth || window.innerWidth
  const vh = canvas.value?.clientHeight || window.innerHeight
  const centerX = gridSize / 2
  const centerY = gridSize / 2
  offsetX = vw / 2 - (centerX - 0.5) * cellSize.value
  offsetY = vh / 2 - (centerY - 0.5) * cellSize.value
}

function centerMapOnVillage(village: Village): void {
  const vw = canvas.value?.clientWidth || window.innerWidth
  const vh = canvas.value?.clientHeight || window.innerHeight

  const targetOffsetX = vw / 2 - (village.x - 0.5) * cellSize.value
  const targetOffsetY = vh / 2 - (village.y - 0.5) * cellSize.value

  const frames = 20
  let frame = 0
  const startX = offsetX
  const startY = offsetY
  const deltaX = (targetOffsetX - startX) / frames
  const deltaY = (targetOffsetY - startY) / frames

  function animateScroll() {
    if (frame < frames) {
      offsetX += deltaX
      offsetY += deltaY
      draw()
      frame++
      requestAnimationFrame(animateScroll)
    } else {
      offsetX = targetOffsetX
      offsetY = targetOffsetY
      draw()
    }
  }

  animateScroll()
  showDrawer.value = false
}

function onClick(e: MouseEvent) {
  const rect = canvas.value!.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top
  const village = villageStore.villages.find((v) => {
    const drawX = (v.x - 1) * cellSize.value + offsetX
    const drawY = (v.y - 1) * cellSize.value + offsetY
    return x >= drawX && x <= drawX + cellSize.value && y >= drawY && y <= drawY + cellSize.value
  })
  if (village) {
    alert(`Clicked on village: ${village.name}`)
  }
}

onMounted(async () => {
  await villageStore.fetchPlayerVillages()
  await villageStore.fetchAllVillages()
  if (canvas.value) {
    const ratio = window.devicePixelRatio || 1
    const vw = canvas.value?.clientWidth || window.innerWidth
    const vh = canvas.value?.clientHeight || window.innerHeight

    canvas.value.width = vw * ratio
    canvas.value.height = vh * ratio
    canvas.value.style.width = `${vw}px`
    canvas.value.style.height = `${vh}px`
    ctx = canvas.value.getContext('2d')
    if (ctx) ctx.scale(ratio, ratio)
    centerMap()
    draw()
  }

  if (villageStore.playerVillages.length) {
    centerMapOnVillage(villageStore.playerVillages[0])
  }
})
</script>

<template>
  <div class="flex h-full overflow-hidden bg-base-100">
    <aside class="hidden lg:block w-100 m-4 rounded-box">
      <MenuSide />

      <ul class="list bg-base-200 rounded-box shadow-md overflow-y-auto overflow-x-hidden max-h-[calc(100vh-8rem)]">
        <li class="p-4 pb-2 tracking-wide font-bold text-lg">Your Villages</li>

        <VillageListItem
          v-for="village in villageStore.playerVillages"
          :key="village.id"
          :village="village"
          @list-click="(v) => centerMapOnVillage(v)"
        />
      </ul>
    </aside>

    <!-- Canvas -->
    <main class="flex-1 p-4 relative rounded-box overflow-hidden">
      <canvas
        ref="canvas"
        @mousedown="onMouseDown"
        @mousemove="onMouseMove"
        @mouseup="onMouseUp"
        @click="onClick"
        class="w-full h-full max-h-[calc(100vh-6rem)] lg:max-h-screen rounded-box"
      />
      <VillageTooltip
        v-if="tooltip.show && tooltip.village"
        :village="tooltip.village"
        :x="tooltip.x"
        :y="tooltip.y"
      />


      <!-- Zoom buttons -->
      <div class="absolute bottom-22 lg:bottom-6 right-7 lg:right-6 flex flex-col space-y-2 z-50">
        <button @click="showDrawer = true" class="bg-base-200 shadow rounded-full p-3 hover:bg-base-100 lg:hidden">
          =
        </button>
        <button @click="zoomIn" class="bg-base-200 shadow rounded-full p-3 hover:bg-base-100">
          +
        </button>
        <button @click="zoomOut" class="bg-base-200 shadow rounded-full p-3 hover:bg-base-100">
          -
        </button>
      </div>

      <!-- Mobile Drawer -->
      <transition name="slide">
        <div v-if="showDrawer" class="absolute inset-0 bg-black bg-opacity-50 z-50 flex flex-col">
          <div class="bg-white p-4 flex-1 overflow-y-auto">
            <button @click="showDrawer = false" class="mb-4 text-gray-700">Close</button>
            <h2 class="font-semibold mb-4">Your Villages</h2>
            <ul class="space-y-2">
              <li
                v-for="village in villageStore.playerVillages"
                :key="village.id"
                @click="centerMapOnVillage(village)"
                class="p-3 bg-gray-100 rounded hover:bg-blue-100 cursor-pointer"
              >
                <div class="font-semibold">{{ village.name }}</div>
                <div class="text-sm text-gray-500">Coords: ({{ village.x }}, {{ village.y }})</div>
              </li>
            </ul>
          </div>
        </div>
      </transition>
    </main>
  </div>
</template>
