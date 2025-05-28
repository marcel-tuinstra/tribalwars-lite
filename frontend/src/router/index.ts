import {createRouter, createWebHistory} from 'vue-router'
import {useAuthStore} from '@/stores/auth';
import Overview from '@/views/Overview.vue'
import Map from '@/views/Map.vue'
import Register from "@/views/Register.vue";
import Login from '@/views/Login.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {path: '/', component: Overview, meta: { requiresAuth: true }},
    {path: '/map', component: Map, meta: { requiresAuth: true }},
    {path: '/register', component: Register},
    // {path: '/forgot-password', component: ForgotPassword},
    // {path: '/forgot-email', component: ForgotEmail},
    {path: '/login', component: Login}
  ]
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore();

  if (auth.token && !auth.user) {
    await auth.fetchProfile();
  }

  // Protected route?
  if (to.meta.requiresAuth && !auth.user) {
    return next('/login');
  }

  next();
});

export default router
