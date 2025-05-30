import { useAuthStore } from '@/stores/auth';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

export const apiFetch = async (path: string, options: RequestInit = {}) => {
  const auth = useAuthStore();
  const headers = {
    ...(options.headers || {}),
    Authorization: auth.token ? `Bearer ${auth.token}` : '',
    'Content-Type': 'application/json'
  };

  const res = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers
  });

  if (!res.ok) {
    throw new Error(`API error: ${res.status}`);
  }

  return res.json();
};
