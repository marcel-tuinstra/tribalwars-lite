import { defineStore } from 'pinia';
import { getAllVillages, getPlayerVillages } from '@/services/village';

export const useVillageStore = defineStore('village', {
  state: () => ({
    villages: [] as any[],
    playerVillages: [] as any[],
    villagesLoaded: false,
    playerVillagesLoaded: false,
  }),
  actions: {
    async fetchPlayerVillages() {
      if (this.playerVillagesLoaded) return;
      this.playerVillages = await getPlayerVillages();
      this.playerVillagesLoaded = true;
    },
    async fetchAllVillages() {
      if (this.villagesLoaded) return;
      this.villages = await getAllVillages();
      this.villagesLoaded = true;
    },
    findByCoords(x: number, y: number) {
      return this.villages.find(v => v.coordinates.x === x && v.coordinates.y === y);
    },
  },
});
