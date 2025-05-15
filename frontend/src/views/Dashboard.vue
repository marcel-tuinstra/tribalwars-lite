<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
</script>

<template>
  <div>
    <h1>Welcome, {{ auth.user.email }}</h1>
    <p>
      Some more info about this user:
      <pre>
        {{ auth.user }}
      </pre>
    </p>
  </div>
</template>
