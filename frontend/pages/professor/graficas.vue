<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher class="w-full" />

    <!-- Cabecera con información -->
    <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white py-8 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold">
              Gráficas Comparativas
            </h1>
            <p class="mt-2 text-blue-100">
              Análisis visual de datos CESC entre diferentes clases
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Tarjetas informativas -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#00ADEC] flex items-start">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#00ADEC]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 text-lg">Análisis Comparativo</h3>
            <p class="text-gray-600 mt-1">Visualiza y compara datos entre diferentes clases para identificar patrones y alumnos en riesgo.</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-amber-500 flex items-start">
          <div class="bg-amber-100 p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 text-lg">Datos Detallados</h3>
            <p class="text-gray-600 mt-1">Haga clic en cada clase para ver información específica de los alumnos que necesitan atención prioritaria.</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 flex items-start">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 text-lg">Toma de Decisiones</h3>
            <p class="text-gray-600 mt-1">Identifique a los estudiantes que requieren intervención inmediata por estar en riesgo de "Rebutjat" o "Víctima".</p>
          </div>
        </div>
      </div>

      <!-- Separador -->
      <div class="w-full h-1 bg-gradient-to-r from-blue-200 to-transparent rounded mb-8"></div>

      <!-- Sección de gráficos -->
      <div class="grid grid-cols-1 gap-8">
        <!-- Gráfico de Tags CESC -->

          <div class="p-1">
            <TagsGraphic />
          
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue';
import TagsGraphic from '@/components/Teacher/CescComponents/TagsGraphic.vue';

// Datos del usuario
const userData = ref(null);

// Cargar datos del usuario desde localStorage
onMounted(() => {
  const storedUser = localStorage.getItem('user');
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
  }
});
</script>
