<template>
  <div class="min-h-screen bg-white">
    <DashboardNavTeacher class="w-full" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-semibold text-[#0080C0] text-center">
          RESULTATS CESC
        </h1>
      </div>
      
      <div v-if="isLoading" class="bg-white rounded-lg shadow-md p-8 flex flex-col items-center justify-center min-h-[300px]">
        Cargando...
      </div>
      
      <div v-else-if="error">{{ error }}</div>

      <div v-else>
        <p v-if="filtered.length === 0" class="text-center text-gray-600">
          No hi ha dades filtrades per a aquest curs i divisió.
        </p>

        <!-- Buscador por estudiante -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <label for="student-search" class="block text-sm font-medium text-gray-700 mb-1">Buscar estudiante</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input 
                  id="student-search" 
                  v-model="studentSearch" 
                  type="search" 
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nombre del estudiante..."
                />
              </div>
            </div>
            <div class="flex-1">
              <label for="tag-filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por etiqueta</label>
              <select 
                id="tag-filter" 
                v-model="selectedTag"
                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas las etiquetas</option>
                <option v-for="tag in uniqueTags" :key="tag" :value="tag">{{ tag }}</option>
              </select>
            </div>
          </div>
          <div v-if="searchActive" class="mt-3 flex justify-between items-center">
            <p class="text-sm text-gray-600">
              <span v-if="filteredResults.length === 0">No se encontraron estudiantes</span>
              <span v-else>Mostrando {{ filteredResults.length }} de {{ groupedResults.length }} estudiantes</span>
            </p>
            <button 
              @click="clearSearch" 
              class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar filtros
            </button>
          </div>
        </div>

        <!-- Selector de tipo de gráfico -->
        <div class="flex justify-center gap-4 mb-6">
          <button 
            @click="selectedChart = 'bar'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2"
            :class="selectedChart === 'bar' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Barras
          </button>
          <button 
            @click="selectedChart = 'pie'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2"
            :class="selectedChart === 'pie' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            Circular
          </button>
          <button 
            @click="selectedChart = 'radar'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2"
            :class="selectedChart === 'radar' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19l9-7-9-7v14z" />
            </svg>
            Radar
          </button>
        </div>

        <!-- Gráfico ECharts -->
        <div v-if="groupedResults.length > 0" class="mt-4 mb-8 bg-white rounded-xl shadow p-4">
          <v-chart 
            class="w-full h-[500px]" 
            :option="getChartOption" 
            :animation="true"
            :animation-duration="1000"
            :animation-easing="'cubicInOut'"
            @click="handleChartClick"
            autoresize 
          />
        </div>

        <!-- Detalles del estudiante seleccionado -->
        <div v-if="selectedStudent" class="mb-8 bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white p-4 flex justify-between items-center">
            <h3 class="text-xl font-bold">Perfil de {{ selectedStudent.fullName }}</h3>
            <button @click="selectedStudent = null" class="text-white hover:text-gray-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="p-6">
            <!-- Gráfico de radar para el estudiante -->
            <div class="h-64 mb-6">
              <v-chart 
                class="w-full h-full" 
                :option="getStudentRadarOption" 
                autoresize 
              />
            </div>
            
            <!-- Etiquetas del estudiante -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
              <div 
                v-for="tag in uniqueTags" 
                :key="tag" 
                class="bg-gray-50 rounded-lg p-4 flex items-center justify-between transition-all duration-300 hover:shadow-md"
                :class="{ 
                  'bg-red-50 border-red-200 border': tag === 'Agressiu' && (selectedStudent.tags[tag] || 0) > 0,
                  'bg-yellow-50 border-yellow-200 border': tag === 'Víctima' && (selectedStudent.tags[tag] || 0) > 0,
                  'bg-green-50 border-green-200 border': tag === 'Prosocial' && (selectedStudent.tags[tag] || 0) > 0,
                  'bg-blue-50 border-blue-200 border': tag === 'Popular' && (selectedStudent.tags[tag] || 0) > 0
                }"
              >
                <span class="font-medium" 
                  :class="{
                    'text-red-700': tag === 'Agressiu',
                    'text-yellow-700': tag === 'Víctima',
                    'text-green-700': tag === 'Prosocial',
                    'text-blue-700': tag === 'Popular',
                    'text-gray-700': !['Agressiu', 'Víctima', 'Prosocial', 'Popular'].includes(tag)
                  }"
                >
                  {{ tag }}
                </span>
                <span 
                  class="px-3 py-1 rounded-full text-center min-w-[40px]"
                  :class="{
                    'bg-red-100 text-red-800': tag === 'Agressiu',
                    'bg-yellow-100 text-yellow-800': tag === 'Víctima',
                    'bg-green-100 text-green-800': tag === 'Prosocial',
                    'bg-blue-100 text-blue-800': tag === 'Popular',
                    'bg-gray-100 text-gray-800': !['Agressiu', 'Víctima', 'Prosocial', 'Popular'].includes(tag)
                  }"
                >
                  {{ selectedStudent.tags[tag] || 0 }}
                </span>
              </div>
            </div>
            
            <!-- Análisis del perfil -->
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
              <h4 class="font-medium text-gray-800 mb-2">Análisis del perfil</h4>
              <p class="text-gray-600" v-if="isHighlightedStudent(selectedStudent, 'Agressiu')">
                <span class="font-medium text-red-600">Advertencia:</span> Este estudiante tiene la puntuación más alta en la etiqueta "Agressiu", lo que podría indicar problemas de comportamiento que requieren atención.
              </p>
              <p class="text-gray-600" v-else-if="isHighlightedStudent(selectedStudent, 'Víctima')">
                <span class="font-medium text-yellow-600">Advertencia:</span> Este estudiante tiene la puntuación más alta en la etiqueta "Víctima", lo que podría indicar que necesita apoyo y protección.
              </p>
              <p class="text-gray-600" v-else-if="selectedStudent.tags['Popular'] && selectedStudent.tags['Popular'] > 0">
                Este estudiante muestra una buena integración social con sus compañeros, siendo reconocido en la categoría "Popular".
              </p>
              <p class="text-gray-600" v-else-if="selectedStudent.tags['Prosocial'] && selectedStudent.tags['Prosocial'] > 0">
                Este estudiante muestra comportamientos prosociales positivos, siendo reconocido por ayudar a sus compañeros.
              </p>
              <p class="text-gray-600" v-else>
                Este estudiante no presenta ningún patrón destacable en las categorías evaluadas.
              </p>
            </div>
          </div>
        </div>
        
        <!-- Tabla de resultados agrupados -->
        <div v-if="groupedResults.length > 0" class="mt-4 overflow-x-auto bg-white rounded-xl shadow p-4">
          <h3 class="text-lg font-medium text-gray-800 mb-4">Tabla de resultados completos</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
              <thead class="bg-gradient-to-r from-blue-50 to-purple-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-b">
                    Nom Complet
                  </th>
                  <th v-for="(tag, index) in uniqueTags" :key="tag" 
                      class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider border-b"
                      :class="getTagHeaderColor(index)">
                    {{ tag }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="student in filteredResults" 
                    :key="student.fullName"
                    :class="{
                      'hover:bg-gray-50 transition-colors': !isHighlightedStudent(student),
                      'bg-red-50 hover:bg-red-100 transition-colors': isHighlightedStudent(student, 'Agressiu'),
                      'bg-yellow-50 hover:bg-yellow-100 transition-colors': isHighlightedStudent(student, 'Víctima'),
                      'bg-blue-50 hover:bg-blue-100 transition-colors': selectedStudent && selectedStudent.fullName === student.fullName
                    }"
                    @click="selectedStudent = student">
                  <td class="px-6 py-4 whitespace-nowrap font-medium">
                    <span :class="{
                      'text-red-700': isHighlightedStudent(student, 'Agressiu'),
                      'text-yellow-700': isHighlightedStudent(student, 'Víctima')
                    }">
                      {{ student.fullName }}
                      <span v-if="isHighlightedStudent(student, 'Agressiu')" 
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                        Agressiu
                      </span>
                      <span v-if="isHighlightedStudent(student, 'Víctima')" 
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                        Víctima
                      </span>
                    </span>
                  </td>
                  <td v-for="(tag, index) in uniqueTags" :key="tag"
                      class="px-6 py-4 whitespace-nowrap text-center">
                    <span v-if="student.tags[tag]" 
                          class="px-3 py-1 rounded-full"
                          :class="[
                            getTagBadgeClasses(index),
                            { 'font-bold': isHighlightedStudent(student) && (tag === 'Agressiu' || tag === 'Víctima') }
                          ]">
                      {{ student.tags[tag] }}
                    </span>
                    <span v-else>-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useCoursesStore } from "~/stores/coursesStore";
import { useResultatCescStore } from "~/stores/resultatsCescStore";
import { useStudentsStore } from "~/stores/studentsStore";
import { useRoute } from "vue-router";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart, PieChart, RadarChart } from 'echarts/charts';
import { 
  GridComponent, 
  TooltipComponent, 
  LegendComponent, 
  TitleComponent,
  ToolboxComponent,
  DataZoomComponent
} from 'echarts/components';

// Registrar componentes de ECharts
use([
  CanvasRenderer, 
  BarChart, 
  PieChart,
  RadarChart,
  GridComponent, 
  TooltipComponent, 
  LegendComponent,
  TitleComponent,
  ToolboxComponent,
  DataZoomComponent
]);

// Variables para búsqueda y filtrado
const studentSearch = ref('');
const selectedTag = ref('');
const selectedChart = ref('bar'); // 'bar', 'pie', 'radar'
const selectedStudent = ref(null); // Para mostrar detalles al hacer clic

// Variable computada para saber si hay búsqueda activa
const searchActive = computed(() => {
  return studentSearch.value.trim() !== '' || selectedTag.value !== '';
});

// Filtrar resultados según la búsqueda
const filteredResults = computed(() => {
  if (!searchActive.value) return groupedResults.value;
  
  return groupedResults.value.filter(student => {
    const nameMatches = studentSearch.value === '' || 
      student.fullName.toLowerCase().includes(studentSearch.value.toLowerCase());
    
    const tagMatches = selectedTag.value === '' || 
      (student.tags[selectedTag.value] && student.tags[selectedTag.value] > 0);
    
    return nameMatches && tagMatches;
  });
});

// Limpiar búsqueda
const clearSearch = () => {
  studentSearch.value = '';
  selectedTag.value = '';
};

// Manejar clic en el gráfico
const handleChartClick = (params) => {
  if (params.componentType === 'series') {
    if (selectedChart.value === 'bar' || selectedChart.value === 'radar') {
      const studentName = params.name;
      selectedStudent.value = groupedResults.value.find(student => student.fullName === studentName);
    } else if (selectedChart.value === 'pie') {
      const tagName = params.name;
      // Mostrar información relacionada con la etiqueta
      // Por ejemplo, podríamos filtrar automáticamente por esa etiqueta
      selectedTag.value = tagName;
    }
    
    console.log('Seleccionado:', params);
  }
};

// Mapeo de colores "base" para cada etiqueta
const baseColors = {
  'Popular': '#22c55e',
  'Rebutjat': '#3b82f6',
  'Agressiu': '#dc2626',
  'Prosocial': '#8b5cf6',
  'Víctima': '#f59e0b'
};

// Opciones dinámicas del gráfico según el tipo seleccionado
const getChartOption = computed(() => {
  const maxAggressive = maxAggressiveScore.value;
  const maxVictim = maxVictimScore.value;
  const dataToUse = searchActive.value ? filteredResults.value : groupedResults.value;
  
  // Opción común: título
  const title = {
    text: `${course.value ? course.value.courseName + ' ' + course.value.division.name : 'Clase'} - Análisis CESC`,
    left: 'center',
    top: 0,
    textStyle: {
      fontSize: 18,
      fontWeight: 'bold',
      color: '#0080C0'
    }
  };
  
  // Opción común: tooltip
  const tooltip = {
    trigger: selectedChart.value === 'radar' ? 'item' : 'axis',
    axisPointer: selectedChart.value === 'bar' ? { type: 'shadow' } : undefined,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    borderColor: 'rgba(255, 255, 255, 0.2)',
    borderWidth: 1,
    padding: 10,
    textStyle: {
      color: '#fff',
      fontSize: 12
    },
    formatter: function(params) {
      if (selectedChart.value === 'pie') {
        return `<div style="font-weight:bold;">${params.name}</div>
                <div style="display:flex;align-items:center;margin:5px 0;">
                  <span style="display:inline-block;width:10px;height:10px;background:${params.color};border-radius:50%;margin-right:5px;"></span>
                  <span>${params.value} estudiantes (${params.percent}%)</span>
                </div>`;
      } else if (selectedChart.value === 'radar') {
        return `<div style="font-weight:bold;">${params.name}</div>
                ${params.value.map((val, idx) => {
                  return `<div style="margin:3px 0;">
                    <span>${uniqueTags.value[idx]}: ${val}</span>
                  </div>`;
                }).join('')}`;
      } else {
        // Gráfico de barras
        const dataIndex = params[0].dataIndex;
        const student = dataToUse[dataIndex];
        let html = `<div style="font-weight:bold;margin-bottom:5px;">${student.fullName}</div>`;

        params.forEach(param => {
          const color = param.color;
          const seriesName = param.seriesName;
          const value = param.value;

          html += `<div style="display:flex;align-items:center;margin:5px 0;">
                    <span style="display:inline-block;width:10px;height:10px;background:${color};border-radius:50%;margin-right:5px;"></span>
                    <span>${seriesName}: ${value}</span>
                  </div>`;
        });
        
        return html;
      }
    }
  };
  
  // Opción común: toolbox (herramientas interactivas)
  const toolbox = {
    feature: {
      saveAsImage: { title: 'Guardar imagen' },
      dataView: { title: 'Ver datos', lang: ['Ver datos', 'Cerrar', 'Actualizar'] },
      restore: { title: 'Restaurar' },
      dataZoom: { title: { zoom: 'Zoom', back: 'Restaurar zoom' } }
    }
  };
  
  // Opciones específicas según el tipo de gráfico
  if (selectedChart.value === 'bar') {
    const series = uniqueTags.value.map(tag => {
      const baseColor = baseColors[tag] || '#64748b';

      return {
        name: tag,
        type: 'bar',
        stack: 'total',
        color: baseColor,
        barMaxWidth: '50%',
        // Añadir etiquetas al gráfico de barras
        label: {
          show: true,
          position: 'insideRight',
          formatter: (params) => {
            return params.value > 0 ? params.value : '';
          },
          fontSize: 12,
          color: '#fff'
        },
        emphasis: {
          focus: 'series',
          itemStyle: {
            shadowBlur: 10,
            shadowColor: 'rgba(0, 0, 0, 0.3)'
          }
        },
        itemStyle: {
          color: (params) => {
            const student = dataToUse[params.dataIndex];
            const value = student.tags[tag] || 0;

            // Colores especiales para máximos
            if (tag === 'Agressiu' && value === maxAggressive && value > 0) {
              return '#ef4444'; // Rojo intenso
            } else if (tag === 'Víctima' && value === maxVictim && value > 0) {
              return '#eab308'; // Amarillo intenso
            }
            // Color normal
            return baseColor;
          },
          borderRadius: [0, 4, 4, 0]
        },
        data: dataToUse.map(student => student.tags[tag] || 0)
      };
    });

    return {
      animation: true,
      title,
      tooltip,
      toolbox,
      legend: {
        data: uniqueTags.value,
        bottom: 10
      },
      grid: { 
        left: '3%', 
        right: '4%', 
        bottom: '15%', 
        top: '15%', 
        containLabel: true 
      },
      xAxis: {
        type: 'category',
        data: dataToUse.map(student => student.fullName),
        axisLabel: {
          rotate: 45,
          fontSize: 11,
          color: '#666',
          overflow: 'truncate',
          width: 90
        }
      },
      yAxis: { 
        type: 'value',
        name: 'Número de votos',
        nameLocation: 'middle',
        nameGap: 40
      },
      dataZoom: [
        {
          type: 'slider',
          show: dataToUse.length > 10,
          start: 0,
          end: dataToUse.length > 10 ? 100 * (10 / dataToUse.length) : 100,
          height: 20
        }
      ],
      series
    };
  } 
  else if (selectedChart.value === 'pie') {
    // Para el gráfico circular, mostrar la distribución de etiquetas
    // Sumar todos los votos por etiqueta
    const tagTotals = {};
    uniqueTags.value.forEach(tag => {
      tagTotals[tag] = dataToUse.reduce((sum, student) => sum + (student.tags[tag] || 0), 0);
    });
    
    // Crear series para el gráfico circular
    const pieData = Object.entries(tagTotals).map(([tag, count]) => ({
      name: tag,
      value: count
    }));
    
    return {
      animation: true,
      title,
      tooltip,
      toolbox,
      legend: {
        orient: 'vertical',
        right: 10,
        top: 'center',
        data: uniqueTags.value
      },
      series: [{
        name: 'Distribución de etiquetas',
        type: 'pie',
        radius: ['40%', '70%'],
        center: ['40%', '50%'],
        avoidLabelOverlap: true,
        itemStyle: {
          borderRadius: 10,
          borderColor: '#fff',
          borderWidth: 2
        },
        label: {
          show: true,
          formatter: '{b}: {c} ({d}%)'
        },
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        },
        data: pieData
      }]
    };
  } 
  else if (selectedChart.value === 'radar') {
    // Para el gráfico de radar, mostrar el perfil de cada estudiante
    return {
      animation: true,
      title,
      tooltip,
      toolbox,
      legend: {
        data: dataToUse.map(student => student.fullName),
        bottom: 10,
        type: 'scroll',
        pageIconSize: 12,
        pageTextStyle: {
          color: '#666'
        }
      },
      radar: {
        indicator: uniqueTags.value.map(tag => ({
          name: tag,
          max: Math.max(...dataToUse.map(student => student.tags[tag] || 0)) + 2
        })),
        radius: '65%',
        center: ['50%', '50%'],
        name: {
          textStyle: {
            color: '#666',
            fontSize: 12
          }
        },
        axisLine: {
          lineStyle: {
            color: 'rgba(0, 0, 0, 0.1)'
          }
        },
        splitLine: {
          lineStyle: {
            color: 'rgba(0, 0, 0, 0.1)'
          }
        }
      },
      series: [{
        name: 'Perfiles de estudiantes',
        type: 'radar',
        emphasis: {
          lineStyle: {
            width: 4
          }
        },
        data: dataToUse.map(student => ({
          value: uniqueTags.value.map(tag => student.tags[tag] || 0),
          name: student.fullName,
          areaStyle: {
            opacity: 0.1
          }
        }))
      }]
    };
  }
  
  // Por defecto, retornar gráfico de barras si selectedChart no coincide
  return {};
});

const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);
const coursesStore = useCoursesStore();
const studentsStore = useStudentsStore();
const resultatsCescStore = useResultatCescStore();

// Color combinations for tags
const tagColors = [
  { bg: 'bg-green-100', text: 'text-green-800' },
  { bg: 'bg-blue-100', text: 'text-blue-800' },
  { bg: 'bg-red-100', text: 'text-red-800' },
  { bg: 'bg-purple-100', text: 'text-purple-800' },
  { bg: 'bg-yellow-100', text: 'text-yellow-800' },

];

// Function to get tag header color
const getTagHeaderColor = (index) => {
  const colorIndex = index % tagColors.length;
  return tagColors[colorIndex].text;
};

// Function to get tag badge classes
const getTagBadgeClasses = (index) => {
  const colorIndex = index % tagColors.length;
  return `${tagColors[colorIndex].bg} ${tagColors[colorIndex].text}`;
};

// Computed properties para encontrar los máximos valores
const maxAggressiveScore = computed(() => {
  let max = 0;
  groupedResults.value.forEach(student => {
    const score = student.tags['Agressiu'] || 0;
    if (score > max) max = score;
  });
  return max;
});

const maxVictimScore = computed(() => {
  let max = 0;
  groupedResults.value.forEach(student => {
    const score = student.tags['Víctima'] || 0;
    if (score > max) max = score;
  });
  return max;
});

// Function to check if a student should be highlighted
const isHighlightedStudent = (student, type = null) => {
  const aggressiveScore = student.tags['Agressiu'] || 0;
  const victimScore = student.tags['Víctima'] || 0;

  if (type === 'Agressiu') {
    return aggressiveScore === maxAggressiveScore.value && aggressiveScore > 0;
  } else if (type === 'Víctima') {
    return victimScore === maxVictimScore.value && victimScore > 0;
  }
  
  return (aggressiveScore === maxAggressiveScore.value && aggressiveScore > 0) ||
         (victimScore === maxVictimScore.value && victimScore > 0);
};

classId.value = route.params.classId;

// Initialize component
onMounted(async () => {
  try {
    if (!classId.value) throw new Error("classId no trobat");

    await coursesStore.fetchCourses();
    course.value = coursesStore.courses.find(c => c.classId == classId.value);
    if (!course.value) throw new Error("Curs no trobat");

    await studentsStore.fetchStudents();
    students.value = studentsStore.students.filter(
      student =>
        student.course === course.value.courseName &&
        student.division === course.value.division.name
    );
    await resultatsCescStore.fetchResults();
  } catch (err) {
    console.error("Error en carregar les dades:", err);
    error.value = "Error en carregar les dades";
  } finally {
    isLoading.value = false;
  }
});

// Filtrar datos en base al curso y la división
const filtered = computed(() => {
  if (!course.value) return [];

  return resultatsCescStore.getCescByCourseAndDivision(
    course.value.courseName,
    course.value.division.name
  );
});

// Gráfico de radar para el estudiante seleccionado
const getStudentRadarOption = computed(() => {
  if (!selectedStudent.value) return {};
  
  return {
    title: {
      text: 'Perfil de ' + selectedStudent.value.fullName,
      textStyle: {
        fontSize: 14,
        fontWeight: 'normal',
        color: '#666'
      }
    },
    radar: {
      indicator: uniqueTags.value.map(tag => ({
        name: tag,
        max: Math.max(
          ...groupedResults.value.map(student => student.tags[tag] || 0),
          selectedStudent.value.tags[tag] || 0
        ) + 1
      })),
      radius: '70%',
      splitNumber: 4,
      axisName: {
        color: '#666',
        fontSize: 12
      },
      splitArea: {
        areaStyle: {
          color: ['#f5f7fa', '#e4e7ed', '#d4d7de', '#c6cad3'],
          shadowColor: 'rgba(0, 0, 0, 0.2)',
          shadowBlur: 10
        }
      }
    },
    series: [{
      name: 'Perfil del estudiante',
      type: 'radar',
      data: [{
        value: uniqueTags.value.map(tag => selectedStudent.value.tags[tag] || 0),
        name: selectedStudent.value.fullName,
        symbol: 'circle',
        symbolSize: 6,
        lineStyle: {
          width: 2
        },
        areaStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 1,
            y2: 1,
            colorStops: [{
              offset: 0, color: 'rgba(0, 128, 192, 0.7)' // color inicial
            }, {
              offset: 1, color: 'rgba(0, 173, 236, 0.5)' // color final
            }]
          }
        },
        itemStyle: {
          color: '#0080C0'
        }
      }]
    }]
  };
});

// Obtener tags únicos
const uniqueTags = computed(() => {
  const tags = new Set(filtered.value.map(item => item.tag_name));
  return Array.from(tags);
});

// Agrupar resultados por estudiante
const groupedResults = computed(() => {
  const groupedByStudent = {};
  
  filtered.value.forEach(item => {
    const fullName = `${item.peer_name} ${item.peer_last_name}`;
    
    if (!groupedByStudent[fullName]) {
      groupedByStudent[fullName] = {
        fullName,
        tags: {}
      };
    }
    
    groupedByStudent[fullName].tags[item.tag_name] = item.vote_count;
  });
  
  return Object.values(groupedByStudent);
});
</script>

