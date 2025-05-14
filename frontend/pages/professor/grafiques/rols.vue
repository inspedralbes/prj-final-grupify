<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher class="w-full" />

    <!-- Cabecera con información -->
    <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white py-8 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold">
              Comparativa de Rols
            </h1>
            <p class="mt-2 text-blue-100">
              Analitza i compara nivells de popularitat, aïllament i neutralitat entre classes
            </p>
          </div>
          <div class="mt-4 md:mt-0">
            <nuxt-link to="/professor/grafiques" class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 font-medium">
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
        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#00ADEC] mb-4"></div>
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
              :class="chartType === 'stacked' ? 'bg-[#00ADEC] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              Gràfic apilat
            </button>
            <button
              @click="chartType = 'grouped'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="chartType === 'grouped' ? 'bg-[#00ADEC] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              Gràfic agrupat
            </button>
            <button
              @click="chartType = 'percentage'"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="chartType === 'percentage' ? 'bg-[#00ADEC] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              Percentatges
            </button>
          </div>
        </div>

        <!-- Tarjetas de resumen de los Roles -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white p-6">
            <h2 class="text-2xl font-bold mb-2">Rols Sociomètrics</h2>
            <p class="opacity-90">Anàlisi de la distribució de rols entre l'alumnat en funció de les seves nominacions</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            <!-- POPULARS (P) -->
            <div class="bg-green-50 rounded-xl p-4 border border-green-100 flex items-start">
              <div class="bg-green-500 text-white p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-green-800 text-lg">Populars (P)</h3>
                <p class="text-green-600 mt-1">Estudiants amb un alt nombre de nominacions positives i poques o cap negatives. Tenen bona acceptació social.</p>
              </div>
            </div>

            <!-- AÏLLATS (A) -->
            <div class="bg-red-50 rounded-xl p-4 border border-red-100 flex items-start">
              <div class="bg-red-500 text-white p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-red-800 text-lg">Aïllats (A)</h3>
                <p class="text-red-600 mt-1">Estudiants amb poques o cap nominació, tant positives com negatives. Passen desapercebuts en el grup.</p>
              </div>
            </div>

            <!-- NEUTRALS (N) -->
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 flex items-start">
              <div class="bg-blue-500 text-white p-3 rounded-lg mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-blue-800 text-lg">Neutrals (N)</h3>
                <p class="text-blue-600 mt-1">Estudiants amb un nombre equilibrat de nominacions positives i negatives. Tenen una posició intermèdia en el grup.</p>
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
                  <p class="text-sm text-green-500">Populars</p>
                  <p class="text-xl font-bold text-green-600">84</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                  <p class="text-sm text-red-500">Aïllats</p>
                  <p class="text-xl font-bold text-red-600">62</p>
                </div>
                <div class="bg-white p-3 rounded-lg shadow-sm">
                  <p class="text-sm text-blue-500">Neutrals</p>
                  <p class="text-xl font-bold text-blue-600">137</p>
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
            <p class="text-center text-gray-500 mt-4">Actualment s'està implementant aquesta funcionalitat. Aviat podràs visualitzar la comparativa de rols entre classes.</p>
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
const chartType = ref('stacked'); // 'stacked', 'grouped', 'percentage'
</script>
