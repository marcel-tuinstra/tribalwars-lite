import {defineStore} from 'pinia'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    user: '',
  }),
  actions: {
    async login(email: string, password: string) {
      const res = await fetch(API_BASE_URL + '/api/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({email, password}),
      });

      if (!res.ok) throw new Error('Login failed');
      const data = await res.json();

      this.token = data.token;
      localStorage.setItem('token', this.token);
    },

    logout() {
      this.token = '';
      this.user = '';
      localStorage.removeItem('token');
    },

    async fetchProfile() {
      if (!this.token) return;

      const res = await fetch(API_BASE_URL + '/api/player/profile', {
        headers: {
          'Authorization': `Bearer ${this.token}`,
        },
      });

      if (!res.ok) {
        // Token expired of invalid? Force logout
        this.logout();
        return;
      }

      this.user = await res.json();
      localStorage.setItem('user', JSON.stringify(this.user));
    }
  },
});
