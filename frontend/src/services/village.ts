import {apiFetch} from './api.ts';

export const getPlayerVillages = () => apiFetch('/api/villages');
export const getAllVillages = () => apiFetch('/api/villages/all');
export const getVillage = (id: number) => apiFetch(`/api/villages/${id}`);
