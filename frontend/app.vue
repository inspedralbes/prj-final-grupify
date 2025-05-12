<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '~/stores/authStore';

// Inicializar el authStore al cargar la aplicación
onMounted(() => {
  const authStore = useAuthStore();
  
  // Verificar si hay un token para inicializar
  if (authStore.token) {
    console.log("App: Initializing authStore on mount");
    authStore.initialize();
  }
});
</script>

<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>
