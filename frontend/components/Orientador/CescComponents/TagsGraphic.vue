<template>
  <div class="space-y-6 mt-2">
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
    <div v-else-if="graphData.length > 0" class="bg-white rounded-2xl shadow-xl overflow-hidden">
      <!-- Cabecera con información -->
      <div class="p-4">
        <div class="flex items-start justify-between mb-3">
          <button @click="handleReturn" class="flex items-center py-1 px-4 bg-[#0072AF] hover:bg-[#005d8f] text-white rounded-md transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Tornar
          </button>
        </div>
      
          <h1 class="text-2xl md:text-3xl font-semibold text-[#0080C0] text-center mb-4 sm:mb-0">ANÀLISI DE TAGS CESC</h1>
          <p class="text-gray-600 text-center mb-4">Anàlisi de la distribució de puntuacions segons les categories CESC: Popular (A), Rebutjat (C), Agressiu (B), Prosocial (A) i Víctima (C)</p>

      </div>

      <!-- Tarjetas de resumen de los TAGS CESC -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
        <!-- POPULAR (A) - Solo visible si categoría es 'all' o 'social' -->
        <div v-if="categoria === 'all' || categoria === 'social'" class="bg-green-50 rounded-xl p-4 border border-green-100 flex items-start">
          <div class="bg-green-500 text-white p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-green-800 text-lg">Popular (A)</h3>
            <p class="text-green-600 mt-1">Estudiantes que son populares entre sus compañeros. Tienen buena aceptación social en el grupo.</p>
          </div>
        </div>

        <!-- REBUJAT (C) - Solo visible si categoría es 'all' o 'afectado' -->
        <div v-if="categoria === 'all' || categoria === 'afectado'" class="bg-blue-50 rounded-xl p-4 border border-blue-100 flex items-start">
          <div class="bg-blue-500 text-white p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-blue-800 text-lg">Rebutjat (C)</h3>
            <p class="text-blue-600 mt-1">Estudiantes que son rechazados por sus compañeros. Identificar estos casos es crucial para mejorar la integración social.</p>
          </div>
        </div>

        <!-- AGRESSIU (B) - Solo visible si categoría es 'all' o 'violento' -->
        <div v-if="categoria === 'all' || categoria === 'violento'" class="bg-red-50 rounded-xl p-4 border border-red-100 flex items-start">
          <div class="bg-red-500 text-white p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-red-800 text-lg">Agressiu (B)</h3>
            <p class="text-red-600 mt-1">Estudiantes que muestran comportamientos agresivos hacia otros. Necesitan atención para mejorar su interacción social.</p>
          </div>
        </div>

        <!-- PROSOCIAL (A) - Solo visible si categoría es 'all' o 'social' -->
        <div v-if="categoria === 'all' || categoria === 'social'" class="bg-purple-50 rounded-xl p-4 border border-purple-100 flex items-start">
          <div class="bg-purple-500 text-white p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-purple-800 text-lg">Prosocial (A)</h3>
            <p class="text-purple-600 mt-1">Estudiantes que muestran comportamientos positivos y de ayuda hacia los demás. Son un activo valioso para el clima de clase.</p>
          </div>
        </div>

        <!-- VICTIMA (C) - Solo visible si categoría es 'all' o 'afectado' -->
        <div v-if="categoria === 'all' || categoria === 'afectado'" class="bg-amber-50 rounded-xl p-4 border border-amber-100 flex items-start">
          <div class="bg-amber-500 text-white p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-amber-800 text-lg">Víctima (C)</h3>
            <p class="text-amber-600 mt-1">Estudiantes que son víctimas de comportamientos negativos. Requieren atención y apoyo para prevenir problemas de bienestar emocional.</p>
          </div>
        </div>
      </div>

      <!-- Estadísticas generales -->
      <div class="px-6 pb-4">
        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
          <h3 class="font-semibold text-gray-700 mb-2">Estadísticas generales</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-gray-500">Total de clases</p>
              <p class="text-xl font-bold text-gray-800">{{ graphData.length }}</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-gray-500">Total de estudiantes</p>
              <p class="text-xl font-bold text-gray-800">{{ totalStudents }}</p>
            </div>
            <!-- Solo mostramos la estadística de la categoría seleccionada -->
            <div v-if="categoria === 'all' || categoria === 'social'" class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-green-500">Total Popular (A)</p>
              <p class="text-xl font-bold text-green-600">{{ totalPopular }} puntos</p>
            </div>
            <div v-if="categoria === 'all' || categoria === 'afectado'" class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-blue-500">Total Rebutjat (C)</p>
              <p class="text-xl font-bold text-blue-600">{{ totalRebutjat }} puntos</p>
            </div>
            <div v-if="categoria === 'all' || categoria === 'violento'" class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-red-500">Total Agressiu (B)</p>
              <p class="text-xl font-bold text-red-600">{{ totalAgressiu }} puntos</p>
            </div>
            <div v-if="categoria === 'all' || categoria === 'social'" class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-purple-500">Total Prosocial (A)</p>
              <p class="text-xl font-bold text-purple-600">{{ totalProsocial }} puntos</p>
            </div>
            <div v-if="categoria === 'all' || categoria === 'afectado'" class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-amber-500">Total Víctima (C)</p>
              <p class="text-xl font-bold text-amber-600">{{ totalVictima }} puntos</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Selector de visualización -->
      <div class="px-6 pb-4">
        <div class="flex flex-wrap gap-4 mb-4">
          <button
            @click="chartType = 'stacked'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="chartType === 'stacked' ? 'bg-[#00ADEC] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            Gráfico apilado
          </button>
          <button
            @click="chartType = 'grouped'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="chartType === 'grouped' ? 'bg-[#00ADEC] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            Gráfico agrupado
          </button>
        </div>
      </div>

      <!-- Gráfico -->
      <div class="p-6 pt-0">
        <client-only>
          <VChart
            class="w-full h-[500px]"
            :option="chartOptions"
            autoresize
            @click="handleChartClick"
          />
        </client-only>
      </div>

      <!-- Modal de detalles (aparece al hacer clic en una barra) -->
      <div v-if="selectedClass" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white p-4 rounded-t-xl flex justify-between items-center">
            <h3 class="text-xl font-bold">{{ selectedClass.course_name }} {{ selectedClass.division_name }}</h3>
            <button @click="selectedClass = null" class="text-white hover:text-gray-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-6">
            <!-- Lista de los 5 primeros alumnos con más puntos del tag seleccionado -->
            <div v-if="topStudents.length > 0">
              <h4 class="font-semibold text-gray-800 text-lg mb-4">
                Top 5 Alumnos - {{ getTagNameByIndex(selectedTagIndex) }}
              </h4>
              
              <div class="space-y-3">
                <div v-for="(student, index) in topStudents" :key="index" 
                  class="bg-white border rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow flex justify-between items-center">
                  <div class="flex items-center">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full mr-3"
                      :class="getTagColorClass(selectedTagIndex)">
                      <span class="text-white font-bold">{{ index + 1 }}</span>
                    </div>
                    <div>
                      <p class="font-medium">{{ student.name }}</p>
                      <p class="text-sm text-gray-500">Participación: {{ student.percentage }}%</p>
                    </div>
                  </div>
                  <span class="font-bold" :class="getTagTextColorClass(selectedTagIndex)">
                    {{ student.points }} puntos
                  </span>
                </div>
              </div>

              <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">Estos alumnos representan el {{ studentsCoverage }}% del total de puntos de {{ getTagNameByIndex(selectedTagIndex) }} en la clase.</p>
              </div>
            </div>
            
            <!-- Mensaje de carga mientras se obtienen los datos -->
            <div v-else-if="loadingStudents" class="flex flex-col items-center justify-center py-8">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#00ADEC] mb-4"></div>
              <p class="text-gray-600">Cargando datos de alumnos...</p>
            </div>
            
            <!-- Mensaje si no hay datos disponibles -->
            <div v-else class="text-center py-8">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <p class="text-gray-600">No se encontraron datos de alumnos para esta categoría.</p>
            </div>
          </div>
          <div class="bg-gray-50 p-4 rounded-b-xl border-t border-gray-200 flex justify-end">
            <button @click="selectedClass = null" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mensaje cuando no hay datos -->
    <div v-else class="text-center bg-white p-8 rounded-xl shadow-md">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay datos disponibles</h3>
      <p class="text-gray-500 max-w-md mx-auto">No se encontraron datos para mostrar en el gráfico. Esto puede deberse a que no hay resultados CESC registrados o a que no hay estudiantes con los tags seleccionados.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components';

// Registrar componentes de ECharts
use([CanvasRenderer, BarChart, GridComponent, TooltipComponent, LegendComponent]);

// Props
const props = defineProps({
  categoria: {
    type: String,
    default: 'all'
  }
});

// Emit para el botón de volver
const emit = defineEmits(['return-to-categories']);

// Función para manejar el clic en el botón de volver
const handleReturn = () => {
  emit('return-to-categories');
};

// Estado
const graphData = ref([]);
const isLoading = ref(true);
const error = ref(null);
const chartType = ref('stacked'); // 'stacked', 'grouped', 'percentage'
const selectedClass = ref(null); // Para el modal de detalles
const selectedTagIndex = ref(0); // Índice del tag seleccionado en el gráfico
const topStudents = ref([]); // Lista de los 5 mejores estudiantes para el tag seleccionado
const loadingStudents = ref(false); // Estado de carga para los estudiantes
const studentsCoverage = ref(0); // Porcentaje de cobertura de los 5 estudiantes sobre el total de puntos

// Calcular estadísticas totales
const totalStudents = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.total_students, 0);
});

const totalPopular = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.tag_1_count, 0);
});

const totalRebutjat = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.tag_2_count, 0);
});

const totalAgressiu = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.tag_3_count, 0);
});

const totalProsocial = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.tag_4_count, 0);
});

const totalVictima = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.tag_5_count, 0);
});

// Lista filtrada de series según la categoría
const filteredSeries = computed(() => {
  const series = [];
  
  // Primera pasada: calcular totales para cada categoría para poder hacer porcentajes relativos
  const categoryTotals = graphData.value.map(item => {
    const socialTotal = item.tag_1_count + item.tag_4_count;
    const violentoTotal = item.tag_3_count;
    const afectadoTotal = item.tag_2_count + item.tag_5_count;
    const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
    
    return {
      socialTotal: socialTotal,
      violentoTotal: violentoTotal,
      afectadoTotal: afectadoTotal,
      allTotal: allTotal
    };
  });
  
  if (props.categoria === 'all' || props.categoria === 'social') {
    // Calculamos el total para la categoría 'social' usando la suma de ambos tags
    const socialTotals = graphData.value.map(item => item.tag_1_count + item.tag_4_count);
    
    series.push({
      name: 'Popular (A)',
      type: 'bar',
      stack: chartType.value === 'grouped' ? undefined : 'total',
      emphasis: { focus: 'series' },
      itemStyle: { color: '#22c55e' }, // Verde
      data: graphData.value.map((item, index) => {
        if (chartType.value === 'percentage') {
          // Para la categoría 'social', calculamos el porcentaje relativo a la suma de ambos tags
          if (props.categoria === 'social') {
            const total = item.tag_1_count + item.tag_4_count;
            return total > 0 ? (item.tag_1_count / total) * 100 : 0;
          } else {
            // Para 'all', calculamos respecto al total de todos los tags
            const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
            return allTotal > 0 ? (item.tag_1_count / allTotal) * 100 : 0;
          }
        }
        return item.tag_1_count;
      }),
      label: {
        show: true,
        position: 'inside',
        formatter: function(params) {
          const item = graphData.value[params.dataIndex];
          const tagCount = item.tag_1_count;
          
          if (chartType.value === 'percentage') {
            // Para calcular el porcentaje correcto según la categoría
            if (props.categoria === 'social') {
              const total = item.tag_1_count + item.tag_4_count;
              const percentage = total > 0 ? (tagCount / total) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            } else {
              const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
              const percentage = allTotal > 0 ? (tagCount / allTotal) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            }
          } else {
            // Para modos no porcentuales
            return tagCount > 0 ? tagCount : '';
          }
        }
      }
    });
    
    series.push({
      name: 'Prosocial (A)',
      type: 'bar',
      stack: chartType.value === 'grouped' ? undefined : 'total',
      emphasis: { focus: 'series' },
      itemStyle: { color: '#a855f7' }, // Púrpura
      data: graphData.value.map((item, index) => {
        if (chartType.value === 'percentage') {
          // Para la categoría 'social', calculamos el porcentaje relativo a la suma de ambos tags
          if (props.categoria === 'social') {
            const total = item.tag_1_count + item.tag_4_count;
            return total > 0 ? (item.tag_4_count / total) * 100 : 0;
          } else {
            // Para 'all', calculamos respecto al total de todos los tags
            const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
            return allTotal > 0 ? (item.tag_4_count / allTotal) * 100 : 0;
          }
        }
        return item.tag_4_count;
      }),
      label: {
        show: true,
        position: 'inside',
        formatter: function(params) {
          const item = graphData.value[params.dataIndex];
          const tagCount = item.tag_4_count;
          
          if (chartType.value === 'percentage') {
            // Para calcular el porcentaje correcto según la categoría
            if (props.categoria === 'social') {
              const total = item.tag_1_count + item.tag_4_count;
              const percentage = total > 0 ? (tagCount / total) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            } else {
              const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
              const percentage = allTotal > 0 ? (tagCount / allTotal) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            }
          } else {
            // Para modos no porcentuales
            return tagCount > 0 ? tagCount : '';
          }
        }
      }
    });
  }
  
  if (props.categoria === 'all' || props.categoria === 'violento') {
    series.push({
      name: 'Agressiu (B)',
      type: 'bar',
      stack: chartType.value === 'grouped' ? undefined : 'total',
      emphasis: { focus: 'series' },
      itemStyle: { color: '#ef4444' }, // Rojo
      data: graphData.value.map((item, index) => {
        if (chartType.value === 'percentage') {
          // Si es la única categoría, usamos el porcentaje relativo al total de tags
          const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
          return allTotal > 0 ? (item.tag_3_count / allTotal) * 100 : 0;
        }
        return item.tag_3_count;
      }),
      label: {
        show: true,
        position: 'inside',
        formatter: function(params) {
          const item = graphData.value[params.dataIndex];
          const tagCount = item.tag_3_count;
          
          if (chartType.value === 'percentage') {
            // En caso de 'violento', como es el único tag, mostramos el % respecto al total de tags
            if (props.categoria === 'violento') {
              // Usamos el total de tags en lugar del total de estudiantes
              const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
              const percentage = allTotal > 0 ? (tagCount / allTotal) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            } else {
              const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
              const percentage = allTotal > 0 ? (tagCount / allTotal) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            }
          } else {
            // Para modos no porcentuales
            return tagCount > 0 ? tagCount : '';
          }
        }
      }
    });
  }
  
  if (props.categoria === 'all' || props.categoria === 'afectado') {
    // Calculamos el total para la categoría 'afectado' usando la suma de ambos tags
    const afectadoTotals = graphData.value.map(item => item.tag_2_count + item.tag_5_count);
    
    series.push({
      name: 'Rebutjat (C)',
      type: 'bar',
      stack: chartType.value === 'grouped' ? undefined : 'total',
      emphasis: { focus: 'series' },
      itemStyle: { color: '#3b82f6' }, // Azul
      data: graphData.value.map((item, index) => {
        if (chartType.value === 'percentage') {
          // Para la categoría 'afectado', calculamos el porcentaje relativo a la suma de ambos tags
          if (props.categoria === 'afectado') {
            const total = item.tag_2_count + item.tag_5_count;
            return total > 0 ? (item.tag_2_count / total) * 100 : 0;
          } else {
            // Para 'all', calculamos respecto al total de todos los tags
            const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
            return allTotal > 0 ? (item.tag_2_count / allTotal) * 100 : 0;
          }
        }
        return item.tag_2_count;
      }),
      label: {
        show: true,
        position: 'inside',
        formatter: function(params) {
          const item = graphData.value[params.dataIndex];
          const tagCount = item.tag_2_count;
          
          if (chartType.value === 'percentage') {
            // Para calcular el porcentaje correcto según la categoría
            if (props.categoria === 'afectado') {
              const total = item.tag_2_count + item.tag_5_count;
              const percentage = total > 0 ? (tagCount / total) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            } else {
              const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
              const percentage = allTotal > 0 ? (tagCount / allTotal) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            }
          } else {
            // Para modos no porcentuales
            return tagCount > 0 ? tagCount : '';
          }
        }
      }
    });
    
    series.push({
      name: 'Víctima (C)',
      type: 'bar',
      stack: chartType.value === 'grouped' ? undefined : 'total',
      emphasis: { focus: 'series' },
      itemStyle: { color: '#f59e0b' }, // Amarillo
      data: graphData.value.map((item, index) => {
        if (chartType.value === 'percentage') {
          // Para la categoría 'afectado', calculamos el porcentaje relativo a la suma de ambos tags
          if (props.categoria === 'afectado') {
            const total = item.tag_2_count + item.tag_5_count;
            return total > 0 ? (item.tag_5_count / total) * 100 : 0;
          } else {
            // Para 'all', calculamos respecto al total de todos los tags
            const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
            return allTotal > 0 ? (item.tag_5_count / allTotal) * 100 : 0;
          }
        }
        return item.tag_5_count;
      }),
      label: {
        show: true,
        position: 'inside',
        formatter: function(params) {
          const item = graphData.value[params.dataIndex];
          const tagCount = item.tag_5_count;
          
          if (chartType.value === 'percentage') {
            // Para calcular el porcentaje correcto según la categoría
            if (props.categoria === 'afectado') {
              const total = item.tag_2_count + item.tag_5_count;
              const percentage = total > 0 ? (tagCount / total) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            } else {
              const allTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;
              const percentage = allTotal > 0 ? (tagCount / allTotal) * 100 : 0;
              return `${tagCount} (${percentage.toFixed(1)}%)`;
            }
          } else {
            // Para modos no porcentuales
            return tagCount > 0 ? tagCount : '';
          }
        }
      }
    });
  }
  
  return series;
});

// Lista filtrada de leyendas según la categoría
const filteredLegend = computed(() => {
  const legends = [];
  
  if (props.categoria === 'all' || props.categoria === 'social') {
    legends.push('Popular (A)', 'Prosocial (A)');
  }
  
  if (props.categoria === 'all' || props.categoria === 'violento') {
    legends.push('Agressiu (B)');
  }
  
  if (props.categoria === 'all' || props.categoria === 'afectado') {
    legends.push('Rebutjat (C)', 'Víctima (C)');
  }
  
  return legends;
});

// Manejar clic en el gráfico para mostrar detalles
const handleChartClick = async (params) => {
  if (params.componentType === 'series' && params.seriesType === 'bar') {
    const dataIndex = params.dataIndex;
    selectedClass.value = graphData.value[dataIndex];
    
    // Obtener el índice del tag según la serie seleccionada
    const seriesName = params.seriesName;
    selectedTagIndex.value = getTagIndexByName(seriesName);
    
    // Cargar los datos de los 5 mejores estudiantes para este tag
    await fetchTopStudentsByTag(
      selectedClass.value.course_id, 
      selectedClass.value.division_id, 
      selectedTagIndex.value + 1
    );
  }
};

// Obtener el índice del tag según su nombre
const getTagIndexByName = (tagName) => {
  switch (tagName) {
    case 'Popular (A)': return 0;
    case 'Rebutjat (C)': return 1;
    case 'Agressiu (B)': return 2;
    case 'Prosocial (A)': return 3;
    case 'Víctima (C)': return 4;
    default: return 0;
  }
};

// Obtener el nombre del tag según su índice
const getTagNameByIndex = (index) => {
  switch (index) {
    case 0: return 'Popular (A)';
    case 1: return 'Rebutjat (C)';
    case 2: return 'Agressiu (B)';
    case 3: return 'Prosocial (A)';
    case 4: return 'Víctima (C)';
    default: return 'Desconocido';
  }
};

// Obtener la clase de color de fondo para el tag según su índice
const getTagColorClass = (index) => {
  switch (index) {
    case 0: return 'bg-green-500';
    case 1: return 'bg-blue-500';
    case 2: return 'bg-red-500';
    case 3: return 'bg-purple-500';
    case 4: return 'bg-amber-500';
    default: return 'bg-gray-500';
  }
};

// Obtener la clase de color de texto para el tag según su índice
const getTagTextColorClass = (index) => {
  switch (index) {
    case 0: return 'text-green-600';
    case 1: return 'text-blue-600';
    case 2: return 'text-red-600';
    case 3: return 'text-purple-600';
    case 4: return 'text-amber-600';
    default: return 'text-gray-600';
  }
};

// Obtener los 5 mejores estudiantes para un tag específico
const fetchTopStudentsByTag = async (courseId, divisionId, tagId) => {
  loadingStudents.value = true;
  topStudents.value = [];
  
  try {
    const response = await fetch(
      `http://localhost:8000/api/cesc/top-students/${courseId}/${divisionId}/${tagId}`
    );
    
    if (!response.ok) {
      throw new Error(`Error al cargar los estudiantes: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Top estudiantes recibidos:', data);
    
    if (data && data.students) {
      // Tomar los 5 primeros estudiantes
      topStudents.value = data.students.slice(0, 5).map(student => ({
        name: student.name,
        points: student.points,
        percentage: ((student.points / getTotalPointsForTag(selectedTagIndex.value)) * 100).toFixed(1)
      }));
      
      // Calcular el porcentaje de cobertura de estos 5 estudiantes
      const totalPoints = getTotalPointsForTag(selectedTagIndex.value);
      const topStudentsPoints = topStudents.value.reduce((sum, student) => sum + student.points, 0);
      studentsCoverage.value = ((topStudentsPoints / totalPoints) * 100).toFixed(1);
    }
  } catch (err) {
    console.error('Error al cargar los datos de estudiantes:', err);
  } finally {
    loadingStudents.value = false;
  }
};

// Obtener el total de puntos para un tag específico
const getTotalPointsForTag = (tagIndex) => {
  if (!selectedClass.value) return 0;
  
  switch (tagIndex) {
    case 0: return selectedClass.value.tag_1_count;
    case 1: return selectedClass.value.tag_2_count;
    case 2: return selectedClass.value.tag_3_count;
    case 3: return selectedClass.value.tag_4_count;
    case 4: return selectedClass.value.tag_5_count;
    default: return 0;
  }
};

// Opciones del gráfico
const chartOptions = computed(() => {
  // Extraer nombres de cursos y divisiones para el eje X
  const categories = graphData.value.map(item => `${item.course_name} ${item.division_name}`);

  // Configuración básica del gráfico
  const baseConfig = {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      formatter: function(params) {
        const dataIndex = params[0].dataIndex;
        const item = graphData.value[dataIndex];
        let html = `<div style="font-weight:bold;margin-bottom:5px;">${item.course_name} ${item.division_name}</div>`;

        // Calcular los totales para cada categoría para poder mostrar porcentajes relativos
        const socialTotal = item.tag_1_count + item.tag_4_count;
        const afectadoTotal = item.tag_2_count + item.tag_5_count;
        const allTagsTotal = item.tag_1_count + item.tag_2_count + item.tag_3_count + item.tag_4_count + item.tag_5_count;

        params.forEach(param => {
          const color = param.color;
          const seriesName = param.seriesName;
          const value = param.value;
          const totalStudents = item.total_students;
          const tagCount = getTagCountByName(seriesName, item);

          if (chartType.value === 'percentage') {
            // Calculamos el porcentaje relativo según la categoría
            let categoryTotal;
            let percentage;
            
            if (props.categoria === 'social' && (seriesName === 'Popular (A)' || seriesName === 'Prosocial (A)')) {
              categoryTotal = socialTotal;
              percentage = categoryTotal > 0 ? (tagCount / categoryTotal) * 100 : 0;
            } 
            else if (props.categoria === 'afectado' && (seriesName === 'Rebutjat (C)' || seriesName === 'Víctima (C)')) {
              categoryTotal = afectadoTotal;
              percentage = categoryTotal > 0 ? (tagCount / categoryTotal) * 100 : 0;
            }
            else if (props.categoria === 'violento' && seriesName === 'Agressiu (B)') {
              // Para 'violento', mostramos el porcentaje respecto al total de estudiantes
              percentage = totalStudents > 0 ? (tagCount / totalStudents) * 100 : 0;
              categoryTotal = totalStudents;
            }
            else {
              // Para 'all', mostramos el porcentaje respecto al total de todos los tags
              categoryTotal = allTagsTotal;
              percentage = categoryTotal > 0 ? (tagCount / categoryTotal) * 100 : 0;
            }
            
            html += `<div style="display:flex;align-items:center;margin:5px 0;">
                      <span style="display:inline-block;width:10px;height:10px;background:${color};border-radius:50%;margin-right:5px;"></span>
                      <span>${seriesName}: ${percentage.toFixed(1)}% (${tagCount} de ${categoryTotal} puntos)</span>
                    </div>`;
          } else {
            // Para gráficos no porcentuales, mostramos solo el valor absoluto sin calcular porcentaje
            html += `<div style="display:flex;align-items:center;margin:5px 0;">
                      <span style="display:inline-block;width:10px;height:10px;background:${color};border-radius:50%;margin-right:5px;"></span>
                      <span>${seriesName}: ${value} puntos</span>
                    </div>`;
          }
        });

        return html;
      }
    },
    legend: {
      data: filteredLegend.value,
      bottom: 10
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '15%',
      top: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: categories,
      axisLabel: {
        rotate: 45,
        interval: 0,
        fontSize: 11,
        color: '#666'
      }
    },
    yAxis: {
      type: 'value',
      name: chartType.value === 'percentage' ? 'Porcentaje (%)' : 'Número de puntos',
      nameLocation: 'middle',
      nameGap: 40,
      axisLabel: {
        formatter: chartType.value === 'percentage' ? '{value}%' : '{value}'
      },
      max: chartType.value === 'percentage' ? 100 : null
    },
    series: filteredSeries.value
  };

  // Añadir línea de referencia para porcentajes
  if (chartType.value === 'percentage') {
    baseConfig.series.push({
      name: 'Línea de referencia (20%)',
      type: 'line',
      markLine: {
        silent: true,
        lineStyle: {
          color: '#ff6b6b',
          type: 'dashed',
          width: 1
        },
        data: [{ yAxis: 20, name: 'Umbral de atención' }]
      }
    });
  }

  return baseConfig;
});

// Cargar datos
const fetchData = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await fetch('http://localhost:8000/api/cesc/graficas-tags', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      }
    });

    if (!response.ok) {
      // Intentar obtener los detalles del error
      const errorText = await response.text();
      console.error('Error completo:', errorText);
      console.error('Status:', response.status);
      console.error('Headers:', [...response.headers.entries()]);
      throw new Error(`Error al cargar los datos: ${response.status}`);
    }

    const data = await response.json();
    console.log('Datos recibidos:', data);
    graphData.value = data;

    if (data.length === 0) {
      error.value = "No hay datos disponibles para mostrar";
    }
  } catch (err) {
    console.error('Error al cargar los datos:', err);
    error.value = err.message || 'Error al cargar los datos';
  } finally {
    isLoading.value = false;
  }
};

// Función auxiliar para obtener el contador correcto según el nombre del tag
const getTagCountByName = (tagName, item) => {
  switch (tagName) {
    case 'Popular (A)':
      return item.tag_1_count;
    case 'Rebutjat (C)':
      return item.tag_2_count;
    case 'Agressiu (B)':
      return item.tag_3_count;
    case 'Prosocial (A)':
      return item.tag_4_count;
    case 'Víctima (C)':
      return item.tag_5_count;
    default:
      return 0;
  }
};

// Cargar datos al montar el componente
onMounted(() => {
  fetchData();
});

// Observar cambios en la categoría para actualizar la vista
watch(() => props.categoria, (newCategoria) => {
  console.log('Categoría cambiada a:', newCategoria);
}, { immediate: true });
</script>
