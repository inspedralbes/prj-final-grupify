<template>
    <div class="min-h-screen bg-gray-900 text-white">
      <header class="bg-gray-800 border-b border-gray-700">
        <nav
          class="container mx-auto px-4 py-3 flex items-center justify-between"
        >
          <div class="flex items-center space-x-4">
            <h1 class="text-xl font-bold">Codepen Clone</h1>
            <div class="flex space-x-2">
              <button
                @click="activeMode = 'code'"
                :class="[
                  'px-3 py-1 rounded',
                  activeMode === 'code' ? 'bg-blue-600' : 'bg-gray-700',
                ]"
              >
                Code Editor
              </button>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <button class="px-3 py-1 bg-green-600 rounded hover:bg-green-700">
              Save
            </button>
            <button class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600">
              Share
            </button>
          </div>
        </nav>
      </header>
  
      <main class="container mx-auto p-4">
        <StudentCodeEditor v-if="activeMode === 'code'" />
      </main>
    </div>
  </template>
  
  <script setup lang="ts">
  const activeMode = ref('code');
  </script>
  