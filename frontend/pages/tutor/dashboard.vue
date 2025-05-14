<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '~/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

// Al montar el componente, redirigir al dashboard de profesor
onMounted(() => {
  // Verificar que sea un tutor
  if (!authStore.isTutor) {
    router.push('/login');
    return;
  }
  
  // Redirigir al dashboard de profesor
  router.push('/professor/dashboard');
});
</script>

<template>
  <div class="flex justify-center items-center h-screen">
    <div class="text-center">
      <h1 class="text-2xl font-bold mb-4">Redirigiendo al panel de profesor...</h1>
      <p>Serás redirigido automáticamente.</p>
    </div>
  </div>
</template>
