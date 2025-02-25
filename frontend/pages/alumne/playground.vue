<template>
  <div class="min-h-screen bg-gray-900 text-white">  
    <main class="container mx-auto p-2 sm:p-4">
      <StudentCodeEditor v-if="activeMode === 'code'" />
    </main>
  </div>
</template>
  
  <script setup lang="ts">
  const activeMode = ref('code');
  </script>
  