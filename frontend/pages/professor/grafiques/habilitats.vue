<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher class="w-full" />

    <!-- Cabecera con información -->
    <div class="bg-gradient-to-r from-[#FFA500] to-[#FF8C00] text-white py-8 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold">
              Comparativa d'Habilitats
            </h1>
            <p class="mt-2 text-orange-100">
              Analitza i compara les habilitats de lideratge, creativitat i organització entre classes
            </p>
          </div>
          <div class="mt-4 md:mt-0">
            <nuxt-link to="/professor/grafiques" class="px-4 py-2 bg-white text-orange-600 rounded-lg hover:bg-orange-50 font-medium">
              Tornar a Comparatives
            </nuxt-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Pantalla de carga -->
      <div v-if="isLoading" class="flex flex-col justify-center items-center h-64 bg-white rounded-xl shadow-md p-8">
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#FFA500] mb-4"></div>
        <p class="text-gray-600 font-medium">Cargando datos...</p>
      </div>

      <!-- Mensaje de error -->
      <div v-else-if="error" class="text-center bg-red-50 text-red-600 p-6 rounded-xl shadow-md border border-red-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="text-lg font-semibold mb-2">Error al cargar los datos</h3>
        <p>{{ error }}</p>
      </div>

      <!-- Contenido principal cuando hay datos -->
      <div v-else class="space-y-8">
        <!-- Selector de visualización -->
        <div class="bg-white rounded-xl shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Tipus de visualització</h2>
          <div class="flex flex-wrap gap-4">
            <button
              @click="chartType = 'stacked'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="chartType === 'stacked' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              Gràfic apilat
            </button>
            <button
              @click="chartType = 'grouped'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="chartType === 'grouped' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              Gràfic agrupat
            </button>
            <button
              @click="chartType = 'radar'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="chartType === 'radar' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              Gràfic radar
            </button>
          </div>
        </div>

        <!-- Tarjetas de resumen de las Habilidades -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <div class="bg-gradient-to-r from-[#FFA500] to-[#FF8C00] text-white p-6">
            <h2 class="text-2xl font-bold mb-2">Habilitats Destacades</h2>
            <p class="opacity-90">Anàlisi de les habilitats més valorades per l'alumnat en les seves nominacions</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            <!-- LIDERATGE (L) -->
            <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100 flex items-start">
              <div class="bg-indigo-500 text-white p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-indigo-800 text-lg">Lideratge (L)</h3>
                <p class="text-indigo-600 mt-1">Estudiants amb capacitat d'influència, presa de decisions i guia del grup. Solen ser referents per als companys.</p>
              </div>
            </div>

            <!-- CREATIVITAT (C) -->
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 flex items-start">
              <div class="bg-purple-500 text-white p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-purple-800 text-lg">Creativitat (C)</h3>
                <p class="text-purple-600 mt-1">Estudiants amb capacitat d'innovació, originalitat i resolució creativa de problemes. Aporten idees noves al grup.</p>
              </div>
            </div>

            <!-- ORGANITZACIÓ (O) -->
            <div class="bg-cyan-50 rounded-xl p-4 border border-cyan-100 flex items-start">
              <div class="bg-cyan-500 text-white p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-cyan-800 text-lg">Organització (O)</h3>
                <p class="text-cyan-600 mt-1">Estudiants amb capacitat de planificació, gestió del temps i estructuració de tasques. Ajuden a coordinar el treball grupal.</p>
              </div>
            </div>
          </div>

          <!-- Estadísticas generales -->
          <div class="px-6 pb-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <h3 class="font-semibold text-gray-700 mb-2">Estadístiques generals</h3>
              <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                <div class="bg-white p-3 rounded-lg shadow-sm">
                  <p class="text-sm text-gray-500">Total de classes</p>
                  <p class="text-xl font-bold text-gray-800">12</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                  <p class="text-sm text-gray-500">Total d'estudiants</p>
                  <p class="text-xl font-bold text-gray-800">283</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                  <p class="text-sm text-indigo-500">Lideratge</p>
                  <p class="text-xl font-bold text-indigo-600">92</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                  <p class="text-sm text-purple-500">Creativitat</p>
                  <p class="text-xl font-bold text-purple-600">78</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                  <p class="text-sm text-cyan-500">Organització</p>
                  <p class="text-xl font-bold text-cyan-600">113</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfico -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Comparativa per Classes</h2>
          <div class="mt-4">
            <div class="h-96 w-full bg-gray-100 rounded-xl flex items-center justify-center">
              <p class="text-gray-500">Les dades comparatives es mostraran aquí quan estiguin disponibles</p>
            </div>
            <p class="text-center text-gray-500 mt-4">Actualment s'està implementant aquesta funcionalitat. Aviat podràs visualitzar la comparativa d'habilitats entre classes.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue';

// Estado
const isLoading = ref(false);
const error = ref(null);
const chartType = ref('stacked'); // 'stacked', 'grouped', 'radar'
</script>
