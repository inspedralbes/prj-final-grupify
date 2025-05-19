<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Barra de navegación del orientador -->
    <DashboardNavOrientador class="w-full" />
    
    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Header con animación y gradiente -->
      <div class="mb-8 bg-gradient-to-r from-blue-400 to-cyan-300 p-5 rounded-lg shadow-lg text-white text-center relative z-10">
        <h1 class="text-3xl font-bold">Gràfiques Comparatives</h1>
        <div class="w-16 h-1 bg-white mx-auto my-2 rounded-full opacity-70"></div>
      </div>
      
      <!-- Tarjetas de selección -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tarjeta CESC con estilo sobrio -->
        <div 
          class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border-t-4 border-blue-400"
          @click="navigateToCesc"
          @mouseenter="hoveredCard = 'cesc'"
          @mouseleave="hoveredCard = null"
        >
          <div class="p-4 text-center flex flex-col h-full">
            <div class="flex justify-center items-center mb-4">
              <div class="p-4 bg-blue-100 rounded-full">
                <svg class="h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                </svg>
              </div>
            </div>
            <h3 class="text-2xl font-bold text-blue-500 mb-2">Gràfiques CESC</h3>
            
            <div class="flex flex-wrap justify-center gap-2 mb-3">
              <span class="px-3 py-2 rounded-full bg-blue-100 text-blue-600 text-sm flex items-center font-medium">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Comportament Social
              </span>
              <span class="px-3 py-2 rounded-full bg-blue-100 text-blue-600 text-sm flex items-center font-medium">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                Conductes d'Assetjament
              </span>
              <span class="px-3 py-2 rounded-full bg-blue-100 text-blue-600 text-sm flex items-center font-medium">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
                Anàlisi de Tendències
              </span>
            </div>
            
            <p class="text-gray-500 text-sm mb-4">
              Visualització comparativa de qüestionaris CESC per detectar conductes d'assetjament escolar i dinàmiques socials a l'aula.
            </p>
            
            <div class="mt-auto">
              <button 
                @click.stop="navigateToCesc" 
                class="w-full bg-blue-50 text-blue-500 px-3 py-2 rounded-lg font-medium inline-flex items-center justify-center hover:bg-blue-100 transition-colors"
              >
                Explorar Gràfiques
                <svg class="h-4 w-4 ml-2 transform transition-transform duration-300" :class="hoveredCard === 'cesc' ? 'translate-x-1' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Tarjeta Sociograma con estilo sobrio -->
        <div 
          class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border-t-4 border-green-400"
          @click="navigateToSociogram"
          @mouseenter="hoveredCard = 'sociograma'"
          @mouseleave="hoveredCard = null"
        >
          <div class="p-4 text-center flex flex-col h-full">
            <div class="flex justify-center items-center mb-4">
              <div class="p-4 bg-green-100 rounded-full">
                <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                </svg>
              </div>
            </div>
            <h3 class="text-2xl font-bold text-green-500 mb-2">Gràfiques de Sociograma</h3>
            
            <div class="flex flex-wrap justify-center gap-2 mb-3">
              <span class="px-3 py-2 rounded-full bg-green-100 text-green-600 text-sm flex items-center font-medium">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Relacions Socials
              </span>
              <span class="px-3 py-2 rounded-full bg-green-100 text-green-600 text-sm flex items-center font-medium">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Rols Grupals
              </span>
              <span class="px-3 py-2 rounded-full bg-green-100 text-green-600 text-sm flex items-center font-medium">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                </svg>
                Dinàmiques d'Inclusió
              </span>
            </div>
            
            <p class="text-gray-500 text-sm mb-4">
              Anàlisi comparatiu de les relacions i dinàmiques socials dins del grup de classe, identificant patrons d'interacció entre l'alumnat.
            </p>
            
            <div class="mt-auto">
              <button 
                @click.stop="navigateToSociogram" 
                class="w-full bg-green-50 text-green-500 px-3 py-2 rounded-lg font-medium inline-flex items-center justify-center hover:bg-green-100 transition-colors"
              >
                Explorar Gràfiques
                <svg class="h-4 w-4 ml-2 transform transition-transform duration-300" :class="hoveredCard === 'sociograma' ? 'translate-x-1' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import DashboardNavOrientador from '@/components/Orientador/DashboardNavOrientador.vue';

const router = useRouter();
const hoveredCard = ref(null);

// Datos del usuario
const userData = ref(null);

const navigateToCesc = () => {
  router.push('/orientador/cesc/comparative');
};

const navigateToSociogram = () => {
  router.push('/orientador/sociograma/comparative');
};

// Cargar datos del usuario cuando se monta el componente
onMounted(() => {
  const storedUser = localStorage.getItem('user');
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
  }
});
</script>

<style scoped>
/* Añadimos algunas animaciones y transiciones para mejorar la experiencia visual */
@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.animate-pulse-slow {
  animation: pulse 3s infinite ease-in-out;
}
</style>
