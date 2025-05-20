<template>
  <div class="container mx-auto p-4">
    <!-- Header -->
    <div class="mb-8 bg-gradient-to-r from-blue-400 to-cyan-300 p-5 rounded-lg shadow-lg text-white text-center">
      <h1 class="text-3xl font-bold">ANÀLISI COMPARATIU DEL CESC</h1>
      <div class="w-16 h-1 bg-white mx-auto my-2 rounded-full opacity-70"></div>
        <p class="text-white mt-2">
          Selecciona el tipus d'anàlisi comparatiu que vols visualitzar
        </p>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- SOCIAL Card -->
      <div class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer overflow-hidden">
        <div class="h-2 bg-green-400"></div>
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex items-start mb-6">
            <div class="flex-shrink-0 bg-green-100 p-4 rounded-full text-green-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div class="ml-6 text-left">
              <h3 class="text-2xl font-medium text-green-500 mb-2">SOCIAL</h3>
              <p class="mt-2 text-gray-600">Analitza perfils populars i prosocials dins del grup</p>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4 mt-6">
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-green-100 mb-1 flex items-center justify-center">
                <span class="text-green-800 text-xs font-bold">P</span>
              </div>
              <span class="text-xs text-gray-500">Popular</span>
            </div>
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-green-100 mb-1 flex items-center justify-center">
                <span class="text-green-800 text-xs font-bold">PS</span>
              </div>
              <span class="text-xs text-gray-500">Prosocial</span>
            </div>
          </div>
          
          <div class="mt-8 text-center">
            <button 
              @click.stop="selectCategory('social')" 
              class="px-6 py-3 bg-green-50 hover:bg-green-100 text-green-700 font-medium rounded-lg transition-colors duration-200 w-full"
            >
              Veure gràfic
            </button>
          </div>
        </div>
      </div>

      <!-- VIOLENT Card -->
      <div class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer overflow-hidden">
        <div class="h-2 bg-red-400"></div>
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex items-start mb-6">
            <div class="flex-shrink-0 bg-red-100 p-4 rounded-full text-red-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="ml-6 text-left">
              <h3 class="text-2xl font-medium text-red-500 mb-2">VIOLENT</h3>
              <p class="mt-2 text-gray-600">Identifica patrons de comportament agressiu o conflictiu</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 gap-4 mt-6">
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-red-100 mb-1 flex items-center justify-center">
                <span class="text-red-800 text-xs font-bold">AG</span>
              </div>
              <span class="text-xs text-gray-500">Agressiu</span>
            </div>
          </div>
          
          <div class="mt-8 text-center">
            <button 
              @click.stop="selectCategory('violent')" 
              class="px-6 py-3 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition-colors duration-200 w-full"
            >
              Veure gràfic
            </button>
          </div>
        </div>
      </div>

      <!-- AFECTAT Card -->
      <div class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer overflow-hidden">
        <div class="h-2 bg-amber-400"></div>
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex items-start mb-6">
            <div class="flex-shrink-0 bg-amber-100 p-4 rounded-full text-amber-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
            </div>
            <div class="ml-6 text-left">
              <h3 class="text-2xl font-medium text-amber-500 mb-2">AFECTAT</h3>
              <p class="mt-2 text-gray-600">Analitza casos de rebuig o victimització dins del grup</p>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4 mt-6">
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-amber-100 mb-1 flex items-center justify-center">
                <span class="text-amber-800 text-xs font-bold">R</span>
              </div>
              <span class="text-xs text-gray-500">Rebutjat</span>
            </div>
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-amber-100 mb-1 flex items-center justify-center">
                <span class="text-amber-800 text-xs font-bold">V</span>
              </div>
              <span class="text-xs text-gray-500">Víctima</span>
            </div>
          </div>
          
          <div class="mt-8 text-center">
            <button 
              @click.stop="selectCategory('afectat')" 
              class="px-6 py-3 bg-amber-50 hover:bg-amber-100 text-amber-700 font-medium rounded-lg transition-colors duration-200 w-full"
            >
              Veure gràfic
            </button>
          </div>
        </div>
      </div>

      <!-- TOTS Card -->
      <div class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer overflow-hidden">
        <div class="h-2 bg-blue-400"></div>
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex items-start mb-6">
            <div class="flex-shrink-0 bg-blue-100 p-4 rounded-full text-blue-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div class="ml-6 text-left">
              <h3 class="text-2xl font-medium text-blue-500 mb-2">TOTS</h3>
              <p class="mt-2 text-gray-600">Visió general de tots els perfils i les seves interaccions</p>
            </div>
          </div>
          
          <div class="grid grid-cols-3 gap-4 mt-6">
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-green-100 mb-1 flex items-center justify-center">
                <span class="text-green-800 text-xs font-bold">S</span>
              </div>
              <span class="text-xs text-gray-500">Social</span>
            </div>
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-red-100 mb-1 flex items-center justify-center">
                <span class="text-red-800 text-xs font-bold">V</span>
              </div>
              <span class="text-xs text-gray-500">Violent</span>
            </div>
            <div class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
              <div class="w-12 h-12 rounded-full bg-amber-100 mb-1 flex items-center justify-center">
                <span class="text-amber-800 text-xs font-bold">A</span>
              </div>
              <span class="text-xs text-gray-500">Afectat</span>
            </div>
          </div>
          
          <div class="mt-8 text-center">
            <button 
              @click.stop="selectCategory('all')" 
              class="px-6 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg transition-colors duration-200 w-full"
            >
              Veure gràfic
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const emit = defineEmits(['category-selected']);

const selectCategory = (category) => {
  emit('category-selected', category);
};
</script>