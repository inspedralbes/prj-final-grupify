<template>
  <div class="container mx-auto p-4">
    <!-- Header -->
    <div class="mb-8 bg-gradient-to-r from-blue-400 to-cyan-300 p-5 rounded-lg shadow-lg text-white text-center">
      <h2 class="text-3xl font-bold">CESC</h2>
      <div class="w-16 h-1 bg-white mx-auto my-2 rounded-full opacity-70"></div>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- SOCIAL Card -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border-t-4 border-green-400">
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex justify-center items-center mb-3">
            <div class="p-3 bg-green-100 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
          </div>
          <h3 class="text-xl font-bold text-green-500 mb-2">SOCIAL</h3>
          
          <div class="flex justify-center gap-2 mb-3">
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-600 text-xs">Popular</span>
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-600 text-xs">Prosocial</span>
          </div>
          
          <div class="mt-auto">
            <button 
              @click.stop="selectCategory('social')" 
              class="w-full bg-green-50 text-green-500 px-3 py-2 rounded-lg font-medium inline-flex items-center justify-center hover:bg-green-100 transition-colors"
            >
              Ver gráfica
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- VIOLENTO Card -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border-t-4 border-red-400">
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex justify-center items-center mb-3">
            <div class="p-3 bg-red-100 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
          <h3 class="text-xl font-bold text-red-500 mb-2">VIOLENTO</h3>
          
          <div class="flex justify-center mb-3">
            <span class="px-2 py-1 rounded-full bg-red-100 text-red-600 text-xs">Agresivo</span>
          </div>
          
          <div class="mt-auto">
            <button 
              @click.stop="selectCategory('violento')" 
              class="w-full bg-red-50 text-red-500 px-3 py-2 rounded-lg font-medium inline-flex items-center justify-center hover:bg-red-100 transition-colors"
            >
              Ver gráfica
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- AFECTADO Card -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border-t-4 border-amber-400">
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex justify-center items-center mb-3">
            <div class="p-3 bg-amber-100 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
            </div>
          </div>
          <h3 class="text-xl font-bold text-amber-500 mb-2">AFECTADO</h3>
          
          <div class="flex justify-center gap-2 mb-3">
            <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-600 text-xs">Rechazado</span>
            <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-600 text-xs">Víctima</span>
          </div>
          
          <div class="mt-auto">
            <button 
              @click.stop="selectCategory('afectado')" 
              class="w-full bg-amber-50 text-amber-500 px-3 py-2 rounded-lg font-medium inline-flex items-center justify-center hover:bg-amber-100 transition-colors"
            >
              Ver gráfica
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- TODOS Card -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border-t-4 border-blue-400">
        <div class="p-4 text-center flex flex-col h-full">
          <div class="flex justify-center items-center mb-3">
            <div class="p-3 bg-blue-100 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <h3 class="text-xl font-bold text-blue-500 mb-2">TODOS</h3>
          
          <div class="flex flex-wrap justify-center gap-1 mb-3">
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-600 text-xs">Popular</span>
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-600 text-xs">Prosocial</span>
            <span class="px-2 py-1 rounded-full bg-red-100 text-red-600 text-xs">Agresivo</span>
            <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-600 text-xs">Rechazado</span>
            <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-600 text-xs">Víctima</span>
          </div>
          
          <div class="mt-auto">
            <button 
              @click.stop="selectCategory('all')" 
              class="w-full bg-blue-50 text-blue-500 px-3 py-2 rounded-lg font-medium inline-flex items-center justify-center hover:bg-blue-100 transition-colors"
            >
              Ver gráfica
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
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