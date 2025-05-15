<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import DashboardNavOrientador from "@/components/Orientador/DashboardNavOrientador.vue";


const router = useRouter();


const comparativeOptions = [
  {
    id: 'roles',
    title: 'Comparativa de Rols',
    description: 'Analitza i compara nivells de popularitat, aïllament i neutralitat entre classes',
    icon: 'users',
    color: '#0080C0',
    route: '/orientador/sociograma/comparative/roles'
  },
  {
    id: 'skills',
    title: 'Comparativa d\'Habilitats',
    description: 'Analitza i compara les habilitats de lideratge, creativitat i organització entre classes',
    icon: 'star',
    color: '#FF9800',
    route: '/orientador/sociograma/comparative/skills'
  }
];


const navigateTo = (route) => {
  router.push(route);
};
</script>


<template>
  <div class="min-h-screen bg-white">
    <DashboardNavOrientador class="w-full" />


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Encabezado -->
      <div class="mb-12">
        <h1 class="text-3xl font-semibold text-[#0080C0] text-center">
          ANÀLISI COMPARATIU DE SOCIOGRAMES
        </h1>
        <p class="text-center text-gray-600 mt-2">
          Selecciona el tipus d'anàlisi comparatiu que vols visualitzar
        </p>
      </div>


      <!-- Opciones de comparativa -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div
          v-for="option in comparativeOptions"
          :key="option.id"
          class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 cursor-pointer overflow-hidden"
          @click="navigateTo(option.route)"
        >
          <div class="h-2" :style="{ backgroundColor: option.color }"></div>
          <div class="p-8">
            <div class="flex items-start mb-6">
              <!-- Icono -->
              <div class="flex-shrink-0 bg-gray-100 p-4 rounded-lg" :class="`text-[${option.color}]`">
                <svg v-if="option.icon === 'users'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else-if="option.icon === 'star'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </div>
             
              <!-- Título y descripción -->
              <div class="ml-6">
                <h2 class="text-2xl font-medium" :style="{ color: option.color }">
                  {{ option.title }}
                </h2>
                <p class="mt-2 text-gray-600">
                  {{ option.description }}
                </p>
              </div>
            </div>
           
            <!-- Ejemplos visuales -->
            <div class="grid grid-cols-3 gap-4 mt-6">
              <div v-if="option.id === 'roles'"
                   class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-green-100 mb-1 flex items-center justify-center">
                  <span class="text-green-800 text-xs font-bold">P</span>
                </div>
                <span class="text-xs text-gray-500">Populars</span>
              </div>
              <div v-if="option.id === 'roles'"
                   class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-red-100 mb-1 flex items-center justify-center">
                  <span class="text-red-800 text-xs font-bold">A</span>
                </div>
                <span class="text-xs text-gray-500">Aïllats</span>
              </div>
              <div v-if="option.id === 'roles'"
                   class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-blue-100 mb-1 flex items-center justify-center">
                  <span class="text-blue-800 text-xs font-bold">N</span>
                </div>
                <span class="text-xs text-gray-500">Neutrals</span>
              </div>
             
              <div v-if="option.id === 'skills'"
                   class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-orange-100 mb-1 flex items-center justify-center">
                  <span class="text-orange-800 text-xs font-bold">L</span>
                </div>
                <span class="text-xs text-gray-500">Lideratge</span>
              </div>
              <div v-if="option.id === 'skills'"
                   class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-purple-100 mb-1 flex items-center justify-center">
                  <span class="text-purple-800 text-xs font-bold">C</span>
                </div>
                <span class="text-xs text-gray-500">Creativitat</span>
              </div>
              <div v-if="option.id === 'skills'"
                   class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-cyan-100 mb-1 flex items-center justify-center">
                  <span class="text-cyan-800 text-xs font-bold">O</span>
                </div>
                <span class="text-xs text-gray-500">Organització</span>
              </div>
            </div>
           
            <!-- Botón para acceder -->
            <div class="mt-8 text-center">
              <button
                class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200"
              >
                Visualitzar comparativa
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>