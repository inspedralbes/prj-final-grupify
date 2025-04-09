<template>
  <div class="space-y-8 mt-8">
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
      <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white p-6">
        <h2 class="text-2xl font-bold mb-2">Comparativa de Tags por Clase</h2>
        <p class="opacity-90">Análisis de la distribución de estudiantes identificados como "Rebutjat" y "Víctima" en cada clase</p>
      </div>

      <!-- Tarjetas de resumen -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 flex items-start">
          <div class="bg-blue-500 text-white p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-blue-800 text-lg">Rebutjat (Tag ID: 2)</h3>
            <p class="text-blue-600 mt-1">Estudiantes que son rechazados por sus compañeros. Identificar estos casos es crucial para mejorar la integración social.</p>
          </div>
        </div>

        <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 flex items-start">
          <div class="bg-amber-500 text-white p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-amber-800 text-lg">Víctima (Tag ID: 5)</h3>
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
            <div class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-blue-500">Total Rebutjat</p>
              <p class="text-xl font-bold text-blue-600">{{ totalRebutjat }}</p>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm">
              <p class="text-sm text-amber-500">Total Víctima</p>
              <p class="text-xl font-bold text-amber-600">{{ totalVictima }}</p>
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
          <button
            @click="chartType = 'percentage'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="chartType === 'percentage' ? 'bg-[#00ADEC] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            Porcentajes
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

      <!-- Leyenda explicativa -->
      <div class="px-6 pb-6">
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
          <h3 class="font-semibold text-gray-700 mb-2">Interpretación del gráfico</h3>
          <ul class="list-disc pl-5 text-gray-600 space-y-1">
            <li>Cada barra representa una clase (curso y división)</li>
            <li>Los colores indican los diferentes tags: <span class="text-blue-600 font-medium">Rebutjat</span> y <span class="text-amber-600 font-medium">Víctima</span></li>
            <li>Haz clic en una barra para ver más detalles sobre esa clase</li>
            <li>Puedes cambiar entre diferentes tipos de visualización usando los botones superiores</li>
          </ul>
        </div>
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
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Total estudiantes</p>
                <p class="text-2xl font-bold text-gray-800">{{ selectedClass.total_students }}</p>
              </div>
              <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500">Total tags</p>
                <p class="text-2xl font-bold text-gray-800">{{ selectedClass.tag_2_count + selectedClass.tag_5_count }}</p>
              </div>
            </div>

            <div class="space-y-4">
              <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                <div class="flex justify-between items-center mb-2">
                  <h4 class="font-medium text-blue-800">Rebutjat</h4>
                  <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-medium">{{ selectedClass.tag_2_count }} estudiantes</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${(selectedClass.tag_2_count / selectedClass.total_students) * 100}%` }"></div>
                </div>
                <p class="text-blue-600 text-sm mt-2">{{ ((selectedClass.tag_2_count / selectedClass.total_students) * 100).toFixed(1) }}% de la clase</p>
              </div>

              <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                <div class="flex justify-between items-center mb-2">
                  <h4 class="font-medium text-amber-800">Víctima</h4>
                  <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded text-sm font-medium">{{ selectedClass.tag_5_count }} estudiantes</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-amber-500 h-2.5 rounded-full" :style="{ width: `${(selectedClass.tag_5_count / selectedClass.total_students) * 100}%` }"></div>
                </div>
                <p class="text-amber-600 text-sm mt-2">{{ ((selectedClass.tag_5_count / selectedClass.total_students) * 100).toFixed(1) }}% de la clase</p>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200">
              <h4 class="font-medium text-gray-700 mb-2">Recomendaciones</h4>
              <ul class="list-disc pl-5 text-gray-600 space-y-1">
                <li v-if="selectedClass.tag_2_count > 3">Esta clase tiene un número significativo de estudiantes rechazados. Considere implementar actividades de integración.</li>
                <li v-if="selectedClass.tag_5_count > 3">Hay varios estudiantes identificados como víctimas. Se recomienda una intervención para mejorar el clima del aula.</li>
                <li v-if="(selectedClass.tag_2_count + selectedClass.tag_5_count) / selectedClass.total_students > 0.3">El porcentaje de estudiantes con tags negativos es alto. Considere una evaluación más detallada del clima escolar.</li>
                <li v-if="selectedClass.tag_2_count <= 2 && selectedClass.tag_5_count <= 2">Los niveles de rechazo y victimización son bajos. Continúe con las estrategias actuales.</li>
              </ul>
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
import { ref, computed, onMounted } from 'vue';
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components';

// Registrar componentes de ECharts
use([CanvasRenderer, BarChart, GridComponent, TooltipComponent, LegendComponent]);

// Estado
const graphData = ref([]);
const isLoading = ref(true);
const error = ref(null);
const chartType = ref('stacked'); // 'stacked', 'grouped', 'percentage'
const selectedClass = ref(null); // Para el modal de detalles

// Calcular estadísticas totales
const totalStudents = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.total_students, 0);
});

const totalRebutjat = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.tag_2_count, 0);
});

const totalVictima = computed(() => {
  return graphData.value.reduce((sum, item) => sum + item.tag_5_count, 0);
});

// Manejar clic en el gráfico para mostrar detalles
const handleChartClick = (params) => {
  if (params.componentType === 'series' && params.seriesType === 'bar') {
    const dataIndex = params.dataIndex;
    selectedClass.value = graphData.value[dataIndex];
  }
};

// Opciones del gráfico
const chartOptions = computed(() => {
  // Extraer nombres de cursos y divisiones para el eje X
  const categories = graphData.value.map(item => `${item.course_name} ${item.division_name}`);

  // Datos para las series
  const rebutjatData = graphData.value.map(item => {
    if (chartType.value === 'percentage') {
      return item.total_students > 0 ? (item.tag_2_count / item.total_students) * 100 : 0;
    }
    return item.tag_2_count;
  });

  const victimaData = graphData.value.map(item => {
    if (chartType.value === 'percentage') {
      return item.total_students > 0 ? (item.tag_5_count / item.total_students) * 100 : 0;
    }
    return item.tag_5_count;
  });

  // Configuración básica del gráfico
  const baseConfig = {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      formatter: function(params) {
        const dataIndex = params[0].dataIndex;
        const item = graphData.value[dataIndex];
        let html = `<div style="font-weight:bold;margin-bottom:5px;">${item.course_name} ${item.division_name}</div>`;

        params.forEach(param => {
          const color = param.color;
          const seriesName = param.seriesName;
          const value = param.value;
          const totalStudents = item.total_students;

          if (chartType.value === 'percentage') {
            html += `<div style="display:flex;align-items:center;margin:5px 0;">
                      <span style="display:inline-block;width:10px;height:10px;background:${color};border-radius:50%;margin-right:5px;"></span>
                      <span>${seriesName}: ${value.toFixed(1)}% (${chartType.value === 'percentage' ? Math.round(value * totalStudents / 100) : value} de ${totalStudents} estudiantes)</span>
                    </div>`;
          } else {
            const percentage = totalStudents > 0 ? ((value / totalStudents) * 100).toFixed(1) : 0;
            html += `<div style="display:flex;align-items:center;margin:5px 0;">
                      <span style="display:inline-block;width:10px;height:10px;background:${color};border-radius:50%;margin-right:5px;"></span>
                      <span>${seriesName}: ${value} (${percentage}% de ${totalStudents} estudiantes)</span>
                    </div>`;
          }
        });

        return html;
      }
    },
    legend: {
      data: ['Rebutjat', 'Víctima'],
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
      name: chartType.value === 'percentage' ? 'Porcentaje (%)' : 'Número de estudiantes',
      nameLocation: 'middle',
      nameGap: 40,
      axisLabel: {
        formatter: chartType.value === 'percentage' ? '{value}%' : '{value}'
      },
      max: chartType.value === 'percentage' ? 100 : null
    },
    series: [
      {
        name: 'Rebutjat',
        type: 'bar',
        stack: chartType.value === 'grouped' ? undefined : 'total',
        emphasis: {
          focus: 'series'
        },
        itemStyle: {
          color: '#3b82f6' // Azul
        },
        data: rebutjatData
      },
      {
        name: 'Víctima',
        type: 'bar',
        stack: chartType.value === 'grouped' ? undefined : 'total',
        emphasis: {
          focus: 'series'
        },
        itemStyle: {
          color: '#f59e0b' // Amarillo
        },
        data: victimaData
      }
    ]
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
    const response = await fetch('http://localhost:8000/api/cesc/graficas-tags');

    if (!response.ok) {
      throw new Error(`Error al cargar los datos: ${response.status}`);
    }

    const data = await response.json();
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

// Cargar datos al montar el componente
onMounted(() => {
  fetchData();
});
</script>
