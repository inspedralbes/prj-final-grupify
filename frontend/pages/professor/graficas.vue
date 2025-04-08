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
            <p class="text-gray-600 mt-1">Visualiza y compara datos entre diferentes clases para identificar patrones.</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-amber-500 flex items-start">
          <div class="bg-amber-100 p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 text-lg">Datos Interactivos</h3>
            <p class="text-gray-600 mt-1">Interactúa con los gráficos para obtener información detallada de cada clase.</p>
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
            <p class="text-gray-600 mt-1">Utiliza estos datos para tomar decisiones informadas sobre intervenciones en el aula.</p>
          </div>
        </div>
      </div>

      <!-- Sección de gráficos -->
      <div class="grid grid-cols-1 gap-8">
        <!-- Gráfico de Tags CESC -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gray-50 p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">
              Comparativa de Tags CESC por Clase
            </h2>
            <p class="text-gray-600 mt-2">
              Este gráfico muestra la distribución de estudiantes identificados con los tags "Rebutjat" (ID: 2) y "Víctima" (ID: 5) entre las diferentes clases, desde 1 ESO hasta 4 ESO con sus respectivas divisiones.
            </p>
          </div>

          <div class="p-1">
            <TagsGraphic />
          </div>
        </div>
      </div>

      <!-- Sección de ayuda e interpretación -->
      <div class="mt-8 bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-[#00ADEC]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Guía de interpretación
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="font-medium text-gray-800 mb-2">¿Qué significan estos tags?</h3>
            <ul class="list-disc pl-5 text-gray-600 space-y-2">
              <li><span class="font-medium text-blue-600">Rebutjat (ID: 2)</span>: Estudiantes que son rechazados por sus compañeros. Pueden experimentar aislamiento social.</li>
              <li><span class="font-medium text-amber-600">Víctima (ID: 5)</span>: Estudiantes que son víctimas de comportamientos negativos como intimidación o acoso.</li>
            </ul>
          </div>

          <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="font-medium text-gray-800 mb-2">¿Cómo utilizar esta información?</h3>
            <ul class="list-disc pl-5 text-gray-600 space-y-2">
              <li>Identifica clases con altos porcentajes de estudiantes rechazados o víctimas.</li>
              <li>Compara diferentes clases para detectar patrones o tendencias.</li>
              <li>Utiliza los datos para planificar intervenciones específicas en las clases que lo necesiten.</li>
              <li>Haz seguimiento de los cambios a lo largo del tiempo para evaluar la efectividad de las intervenciones.</li>
            </ul>
          </div>
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
