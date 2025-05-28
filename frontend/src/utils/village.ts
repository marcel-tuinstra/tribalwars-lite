export type BuildingType = 'lumber_camp' | 'clay_pit' | 'iron_mine' | 'barracks' | 'farm' | 'warehouse' | 'hq'
export type ResourceType = 'wood' | 'stone' | 'food' | 'population'

export function buildingToName(type: BuildingType) {
  const map: Record<BuildingType, string> = {
    lumber_camp: 'Lumber Camp',
    clay_pit: 'Clay Pit',
    iron_mine: 'Iron Mine',
    barracks: 'Barracks',
    farm: 'Farm',
    warehouse: 'Warehouse',
    hq: 'Headquarters',
  }

  return map[type]
}

export function resourceToName(resource: ResourceType): string {
  const map: Record<ResourceType, string> = {
    wood: 'Wood',
    stone: 'Stone',
    food: 'Food',
    population: 'Population',
  }

  return map[resource]
}
