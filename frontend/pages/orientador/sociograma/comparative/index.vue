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
  }
  // Se ha eliminado la opción de Comparativa d'Habilitats
];


const navigateTo = (route) => {
  // Establecer la preferencia de ver todos los datos
  localStorage.setItem('orientadorViewAllData', 'true');
  localStorage.setItem('orientadorViewAllStudents', 'true');
  
  router.push(route);
};
</script>


<template>
  <div class="min-h-screen bg-white">
    <DashboardNavOrientador class="w-full" />


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Encabezado con el nuevo estilo inspirado en el código 2 -->
      <div class="mb-12 bg-gradient-to-r from-blue-400 to-cyan-300 p-5 rounded-lg shadow-lg text-white text-center">
        <h1 class="text-3xl font-bold">
          ANÀLISI COMPARATIU DE SOCIOGRAMES
        </h1>
        <div class="w-16 h-1 bg-white mx-auto my-2 rounded-full opacity-70"></div>
        <p class="text-white mt-2">
          Selecciona el tipus d'anàlisi comparatiu que vols visualitzar
        </p>
      </div>


      <!-- Opciones de comparativa - Solo muestra la opción de Roles -->
      <div class="grid grid-cols-1 gap-8">
        <div
          v-for="option in comparativeOptions"
          :key="option.id"
          class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer overflow-hidden border-t-4 border-blue-400"
          @click="navigateTo(option.route)"
        >
          <div class="p-8">
            <div class="flex items-start mb-6">
              <!-- Icono con nuevo estilo -->
              <div class="flex-shrink-0 p-3 rounded-full bg-blue-100">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
             
              <!-- Título y descripción con colores actualizados -->
              <div class="ml-6">
                <h2 class="text-xl font-bold text-blue-500">
                  {{ option.title }}
                </h2>
                <p class="mt-2 text-gray-600">
                  {{ option.description }}
                </p>
              </div>
            </div>
           
            <!-- Ejemplos visuales con nuevos colores -->
            <div class="grid grid-cols-3 gap-4 mt-6">
              <div
                 class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-green-100 mb-1 flex items-center justify-center">
                  <span class="text-green-800 text-xs font-bold">P</span>
                </div>
                <span class="text-xs text-gray-500">Populars</span>
              </div>
              <div
                 class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-red-100 mb-1 flex items-center justify-center">
                  <span class="text-red-800 text-xs font-bold">A</span>
                </div>
                <span class="text-xs text-gray-500">Aïllats</span>
              </div>
              <div
                 class="h-24 bg-gray-50 rounded-lg flex flex-col items-center justify-center text-center p-2">
                <div class="w-12 h-12 rounded-full bg-blue-100 mb-1 flex items-center justify-center">
                  <span class="text-blue-800 text-xs font-bold">N</span>
                </div>
                <span class="text-xs text-gray-500">Neutrals</span>
              </div>
            </div>
           
            <!-- Botón para acceder con nuevo estilo -->
            <div class="mt-8 text-center">
              <button
                class="bg-blue-50 text-blue-500 hover:bg-blue-100 px-6 py-3 font-medium rounded-lg transition-colors duration-200 inline-flex items-center justify-center w-full"
              >
                Visualitzar comparativa
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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