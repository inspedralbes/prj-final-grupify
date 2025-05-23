<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

// Redirigir automáticamente a la página de comparativa
const router = useRouter();
onMounted(() => {
  // Redirigir a la página principal de comparativa
  router.push('/orientador/sociograma/comparative');
});
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-white">
    <div class="text-center p-8">
      <h1 class="text-xl font-bold text-gray-700 mb-4">Aquesta pàgina ja no està disponible</h1>
      <p class="text-gray-600 mb-6">Redirigint a la pàgina principal de comparatives...</p>
      <button 
        @click="router.push('/orientador/sociograma/comparative')"
        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
      >
        Tornar a Comparatives
      </button>
    </div>
  </div>
</template>
