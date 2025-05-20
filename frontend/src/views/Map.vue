<script setup lang="ts">
import { onMounted, ref, nextTick } from 'vue'
import { useVillageStore } from '@/stores/village'
import type { Village } from '@/types/village'
import {useAuthStore} from "@/stores/auth.ts";
const auth = useAuthStore();

const villageStore = useVillageStore()

const canvas = ref<HTMLCanvasElement | null>(null)
const cellSize = ref(15)
const gridSize = 100
const canvasSize = ref(gridSize * cellSize.value)

let ctx: CanvasRenderingContext2D | null = null
let isDragging = false
let offsetX = 0
let offsetY = 0
let dragStartX = 0
let dragStartY = 0

const showDrawer = ref(false)

const hoverVillage = ref<Village | null>(null)
const tooltip = ref<{ show: boolean, x: number, y: number, village: Village | null }>({
    show: false,
    x: 0,
    y: 0,
    village: null
})

function clampOffset() {
    if (!canvas.value) return
    const minX = -gridSize * cellSize.value + canvas.value.width
    const minY = -gridSize * cellSize.value + canvas.value.height
    offsetX = Math.max(Math.min(offsetX, 0), minX)
    offsetY = Math.max(Math.min(offsetY, 0), minY)
}

function draw() {
    if (!ctx || !canvas.value) return
    const width = canvas.value.width
    const height = canvas.value.height
    ctx.clearRect(0, 0, width, height)

    ctx.save()
    ctx.strokeStyle = '#d1fae5'
    ctx.lineWidth = 1
    for (let i = 0; i <= gridSize; i++) {
        const gx = i * cellSize.value + offsetX
        const gy = i * cellSize.value + offsetY
        if (gx >= 0 && gx <= width) {
            ctx.beginPath()
            ctx.moveTo(gx, 0)
            ctx.lineTo(gx, height)
            ctx.stroke()
        }
        if (gy >= 0 && gy <= height) {
            ctx.beginPath()
            ctx.moveTo(0, gy)
            ctx.lineTo(width, gy)
            ctx.stroke()
        }
    }
    ctx.restore()

    for (const village of villageStore.villages) {
        const { x, y } = village
        const drawX = (x - 1) * cellSize.value + offsetX
        const drawY = (y - 1) * cellSize.value + offsetY
        if (
            drawX + cellSize.value < 0 || drawX > width ||
            drawY + cellSize.value < 0 || drawY > height
        ) continue

        ctx.save()
        if (!village.playerId) {
            ctx.fillStyle = '#6b7280'
        } else {
            ctx.fillStyle = village.playerId === auth.user.id ? '#06b6d4' : '#facc15'
        }
        const padding = cellSize.value * 0.15
        ctx.fillRect(drawX + padding, drawY + padding, cellSize.value * 0.7, cellSize.value * 0.7)
        if (hoverVillage.value && hoverVillage.value.id === village.id) {
            ctx.strokeStyle = '#0ea5e9' // Tailwind blue-500
            ctx.lineWidth = 2
            ctx.strokeRect(drawX + padding, drawY + padding, cellSize.value * 0.7, cellSize.value * 0.7)
        }
        ctx.restore()
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
    const village = villageStore.villages.find(v => {
      const drawX = (v.x - 1) * cellSize.value + offsetX
      const drawY = (v.y - 1) * cellSize.value + offsetY
      return (
        x >= drawX &&
        x <= drawX + cellSize.value &&
        y >= drawY &&
        y <= drawY + cellSize.value
      )
    })

    if (isDragging) {
        const dx = e.clientX - dragStartX
        const dy = e.clientY - dragStartY
        dragStartX = e.clientX
        dragStartY = e.clientY
        offsetX += dx
        offsetY += dy
        clampOffset()
        draw()
        tooltip.value.show = false
    } else if (village) {
        hoverVillage.value = village
        tooltip.value = {
            show: true,
            x: (village.x - 1) * cellSize.value + offsetX + cellSize.value / 2,
            y: (village.y - 1) * cellSize.value + offsetY - 10,
            village
        }
        draw()
    } else {
        hoverVillage.value = null
        tooltip.value.show = false
        draw()
    }
}
function onMouseUp() {
    isDragging = false
}

function zoomIn() {
    if (cellSize.value < 50) {
        cellSize.value += 5
        updateCanvasSize()
    }
}
function zoomOut() {
    if (cellSize.value > 10) {
        cellSize.value -= 5
        updateCanvasSize()
    }
}
function updateCanvasSize() {
    if (!canvas.value) return
    const ratio = window.devicePixelRatio || 1
    canvas.value.width = window.innerWidth * ratio
    canvas.value.height = window.innerHeight * ratio
    canvas.value.style.width = `${window.innerWidth}px`
    canvas.value.style.height = `${window.innerHeight}px`
    ctx = canvas.value.getContext('2d')
    ctx?.scale(ratio, ratio)
    clampOffset()
    draw()
}

function centerMapOnVillage(village: Village): void {
    // Center the selected village in the viewport
    const vw = window.innerWidth
    const vh = window.innerHeight
    offsetX = vw / 2 - (village.x - 0.5) * cellSize.value
    offsetY = vh / 2 - (village.y - 0.5) * cellSize.value
    clampOffset()
    nextTick(draw)
    showDrawer.value = false
}

function onClick(e: MouseEvent) {
    const rect = canvas.value!.getBoundingClientRect()
    const x = e.clientX - rect.left
    const y = e.clientY - rect.top
    const village = villageStore.villages.find(v => {
      const drawX = (v.x - 1) * cellSize.value + offsetX
      const drawY = (v.y - 1) * cellSize.value + offsetY
      return (
        x >= drawX &&
        x <= drawX + cellSize.value &&
        y >= drawY &&
        y <= drawY + cellSize.value
      )
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
        canvas.value.width = window.innerWidth * ratio
        canvas.value.height = window.innerHeight * ratio
        canvas.value.style.width = `${window.innerWidth}px`
        canvas.value.style.height = `${window.innerHeight}px`
        ctx = canvas.value.getContext('2d')
        ctx?.scale(ratio, ratio)
        draw()
    }
})
</script>

<template>
    <div class="flex h-full overflow-hidden">
        <!-- Sidebar -->
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
                    <div class="text-sm text-gray-500">Coords: ({{ village.x }}, {{ village.y }})</div>
                </li>
            </ul>
            Total Villages: {{ villageStore.villages.length }}
        </aside>

        <!-- Canvas -->
        <main class="flex-1 relative overflow-hidden bg-green-200">
            <canvas
                ref="canvas"
                @mousedown="onMouseDown"
                @mousemove="onMouseMove"
                @mouseup="onMouseUp"
                @click="onClick"
                class="w-full h-full"
            />
            <div
                v-if="tooltip.show"
                :style="`position: absolute; top: ${tooltip.y}px; left: ${tooltip.x}px; transform: translate(-50%, -100%)`"
                class="bg-black bg-opacity-80 text-white text-xs px-2 py-1 rounded z-50 pointer-events-none"
            >
                {{ tooltip.village?.name }} ({{ tooltip.village?.x }}, {{ tooltip.village?.y }})
            </div>
        </main>

        <!-- Zoom buttons -->
        <div class="fixed bottom-4 right-4 flex flex-col space-y-2 z-50">
            <button @click="zoomIn" class="bg-white shadow rounded-full p-3 hover:bg-blue-100">+</button>
            <button @click="zoomOut" class="bg-white shadow rounded-full p-3 hover:bg-blue-100">-</button>
        </div>

        <!-- Mobile drawer trigger -->
        <div class="fixed bottom-4 left-4 md:hidden z-50">
            <button @click="showDrawer = true" class="bg-white shadow rounded-full p-3 hover:bg-blue-100">=</button>
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
    </div>
</template>

<style scoped>
canvas {
    background-color: #bbf7d0;
}
</style>
