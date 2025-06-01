<script setup>
import { ref, computed, onMounted, markRaw } from 'vue';
import { useCoursesStore } from "~/stores/coursesStore";
import { useRelationshipsStore } from "~/stores/relationships";
import { useStudentsStore } from "~/stores/studentsStore";
import DashboardNavOrientador from "@/components/Orientador/DashboardNavOrientador.vue";
import { useRouter } from "vue-router";
import { use } from "echarts/core";
import { CanvasRenderer } from "echarts/renderers";
import { BarChart, PieChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  DatasetComponent,
  TransformComponent,
  ToolboxComponent
} from "echarts/components";
import VChart from "vue-echarts";


// Registrar componentes de ECharts
use([
  CanvasRenderer,
  BarChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  DatasetComponent,
  TransformComponent,
  ToolboxComponent
]);

// Referencias para los gráficos de ECharts
const pieChartRef = ref(null);
const barChartRef = ref(null);

// Función para descargar el gráfico circular como imagen PNG
const downloadPieChart = () => {
  if (!pieChartRef.value) return;
  
  const chart = pieChartRef.value.chart;
  if (!chart) return;
  
  // Obtener la imagen como data URL
  const dataURL = chart.getDataURL({
    type: 'png',
    pixelRatio: 2, // Para mejor calidad en pantallas de alta resolución
    backgroundColor: '#fff'
  });
  
  // Crear un enlace para descargar la imagen
  const link = document.createElement('a');
  link.download = 'grafic roles sociograma.png';
  link.href = dataURL;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Función para descargar el gráfico de barras como imagen PNG
const downloadBarChart = () => {
  if (!barChartRef.value) return;
  
  const chart = barChartRef.value.chart;
  if (!chart) return;
  
  // Obtener la imagen como data URL
  const dataURL = chart.getDataURL({
    type: 'png',
    pixelRatio: 2, // Para mejor calidad en pantallas de alta resolución
    backgroundColor: '#fff'
  });
  
  // Crear un enlace para descargar la imagen
  const link = document.createElement('a');
  link.download = 'grafic roles sociograma.png';
  link.href = dataURL;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};


const router = useRouter();
const coursesStore = useCoursesStore();
const relationshipsStore = useRelationshipsStore();
const studentsStore = useStudentsStore();

// Función para volver a la página anterior
const goBack = () => {
  router.push("/orientador/sociograma/comparative");
};


const isLoading = ref(true);
const error = ref(null);
const allCourses = ref([]);
const comparativeData = ref([]);


// Opciones para el gráfico de barras
const barChartOptions = computed(() => {
  // Si no hay datos, retornamos opciones vacías
  if (comparativeData.value.length === 0) {
    return {
      title: {
        text: 'No hi ha dades disponibles'
      }
    };
  }
 
  // Preparar los datos para el gráfico
  const courseNames = comparativeData.value.map(item => item.course);
  const popularValues = comparativeData.value.map(item => item.Populars || 0);
  const isolatedValues = comparativeData.value.map(item => item.Aïllats || 0);
  const neutralValues = comparativeData.value.map(item => item.Neutrals || 0);
 
  return {
    title: {
      text: 'Comparativa per Cursos',
      left: 'center',
      textStyle: {
        color: '#0080C0'
      }
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'shadow'
      }
    },
    legend: {
      data: ['Populars', 'Aïllats', 'Neutrals'],
      top: 'bottom'
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '10%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: courseNames,
      axisLabel: {
        rotate: 45,
        interval: 0
      }
    },
    yAxis: {
      type: 'value'
    },
    series: [
      {
        name: 'Populars',
        type: 'bar',
        stack: 'total',
        emphasis: {
          focus: 'series'
        },
        data: popularValues,
        itemStyle: {
          color: '#4CAF50'
        }
      },
      {
        name: 'Aïllats',
        type: 'bar',
        stack: 'total',
        emphasis: {
          focus: 'series'
        },
        data: isolatedValues,
        itemStyle: {
          color: '#F44336'
        }
      },
      {
        name: 'Neutrals',
        type: 'bar',
        stack: 'total',
        emphasis: {
          focus: 'series'
        },
        data: neutralValues,
        itemStyle: {
          color: '#2196F3'
        }
      }
    ]
  };
});


// Opciones para el gráfico circular de resumen
const pieChartOptions = computed(() => {
  if (!categoryTotals.value) {
    return {
      title: {
        text: 'No hi ha dades disponibles'
      }
    };
  }
 
  return {
    title: {
      text: 'Distribució General',
      left: 'center',
      textStyle: {
        color: '#0080C0'
      }
    },
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'horizontal',
      bottom: 'bottom',
    },
    series: [
      {
        name: 'Distribució',
        type: 'pie',
        radius: ['40%', '70%'], // Gráfico de anillo
        avoidLabelOverlap: false,
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
          label: {
            show: true,
            fontSize: '18',
            fontWeight: 'bold'
          }
        },
        labelLine: {
          show: true
        },
        data: [
          {
            value: categoryTotals.value.Populars,
            name: 'Populars',
            itemStyle: { color: '#4CAF50' }
          },
          {
            value: categoryTotals.value.Aïllats,
            name: 'Aïllats',
            itemStyle: { color: '#F44336' }
          },
          {
            value: categoryTotals.value.Neutrals,
            name: 'Neutrals',
            itemStyle: { color: '#2196F3' }
          }
        ]
      }
    ]
  };
});


// Cargar todos los datos necesarios
onMounted(async () => {
  try {
    await coursesStore.fetchCourses();
    await studentsStore.fetchStudents();
    await relationshipsStore.fetchRelationships();
   
    allCourses.value = coursesStore.courses;
    // Generar los datos comparativos para todos los cursos
    generateComparativeData();
   
  } catch (err) {
    console.error("Error en carregar les dades:", err);
    error.value = "Error en carregar les dades: " + err.message;
  } finally {
    isLoading.value = false;
  }
});


// Función para generar datos comparativos
const generateComparativeData = () => {
  // Si no hay cursos, no hacemos nada
  if (!allCourses.value || allCourses.value.length === 0) return;

  const dataByCategory = {
    'Populars': [],
    'Aïllats': [],
    'Neutrals': []
  };
 
  // Para cada curso, obtener los datos de popularidad
  allCourses.value.forEach(course => {
    if (!course.courseName || !course.division || !course.division.name) return;
   
    // Usamos la función que ya tienes
    const popularityData = relationshipsStore.getPopularityDataByCourseAndDivision(
      course.courseName,
      course.division.name
    );
   
    // Si no hay datos, continuamos con el siguiente curso
    if (!popularityData.value || popularityData.value.length === 0) return;
   
    const courseName = `${course.courseName} ${course.division.name}`;
   
    // Mapear etiquetas en español a catalán
    const labelMapping = {
      'Populares': 'Populars',
      'Aislados': 'Aïllats',
      'Neutrales': 'Neutrals'
    };
   
    // Agregamos los datos por categoría
    popularityData.value.forEach(item => {
      const catalanLabel = labelMapping[item.label] || item.label;
     
      dataByCategory[catalanLabel].push({
        course: courseName,
        value: item.count
      });
    });
  });
 
  // Transformar para el gráfico
  const chartData = [];
  Object.keys(dataByCategory).forEach(category => {
    dataByCategory[category].forEach(item => {
      const existingItem = chartData.find(x => x.course === item.course);
      if (existingItem) {
        existingItem[category] = item.value;
      } else {
        chartData.push({
          course: item.course,
          [category]: item.value
        });
      }
    });
  });
 
  comparativeData.value = chartData;
  
  // Almacenar en localStorage que el orientador quiere ver todos los datos
  localStorage.setItem('orientadorViewAllData', 'true');
};


// Calcular totales por categoría
const categoryTotals = computed(() => {
  const totals = {
    Populars: 0,
    Aïllats: 0,
    Neutrals: 0
  };
 
  comparativeData.value.forEach(item => {
    if (item.Populars) totals.Populars += item.Populars;
    if (item.Aïllats) totals.Aïllats += item.Aïllats;
    if (item.Neutrals) totals.Neutrals += item.Neutrals;
  });
 
  return totals;
});
</script>


<template>
  <div class="min-h-screen bg-white">
    <DashboardNavOrientador class="w-full" />


    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Botón fijo en esquina para móviles -->
      <div class="fixed bottom-4 right-4 z-10 md:hidden">
        <button
          @click="goBack"
          class="flex items-center justify-center w-14 h-14 rounded-full shadow-lg bg-[#0080C0] hover:bg-[#006699] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0080C0] text-white"
          aria-label="Tornar"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
        </button>
      </div>
      
      <!-- Encabezado adaptativo -->
      <div class="flex flex-col sm:flex-row items-center sm:justify-between mb-6">
        <!-- Botón visible solo en pantallas más grandes -->
        <button
          @click="goBack"
          class="hidden md:inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#0080C0] hover:bg-[#006699] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0080C0] mb-4 sm:mb-0"
        >
          <svg
            class="w-5 h-5 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
          Tornar
        </button>

        <div class="text-center flex-1">
          <h1 class="text-2xl md:text-3xl font-semibold text-[#0080C0]">
            GRÀFICS COMPARATIUS DE SOCIOGRAMES
          </h1>
          <p class="text-sm sm:text-base text-gray-600 mt-2">
            Anàlisi comparatiu dels nivells de popularitat, aïllament i neutralitat de tots els cursos
          </p>
        </div>
        
        <div class="hidden md:block w-[100px]"></div>
        <!-- Espacio para equilibrar el layout solo en desktop -->
      </div>


      <!-- Estado de carga -->
      <div
        v-if="isLoading"
        class="bg-white rounded-lg shadow-md p-8 flex flex-col items-center justify-center min-h-[300px]"
      >
        <div class="relative w-16 h-16">
          <div
            class="absolute inset-0 rounded-full border-4 border-[#0080C0] border-t-transparent animate-spin"
          ></div>
        </div>
        <p class="mt-4 text-base text-gray-600">
          Carregant dades comparatives...
        </p>
      </div>


      <!-- Estado de error -->
      <div
        v-else-if="error"
        class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm flex items-center"
      >
        <svg
          class="w-6 h-6 text-red-500 mr-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-red-700">{{ error }}</p>
      </div>


      <!-- Contenido principal -->
      <div v-else-if="comparativeData.length > 0" class="space-y-8">
        <!-- Resumen general -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">Resum General</h2>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div
              v-for="(value, key) in categoryTotals"
              :key="key"
              class="bg-gray-50 rounded-lg p-3 sm:p-4 text-center border-t-4"
              :style="{ borderTopColor: key === 'Populars' ? '#4CAF50' : key === 'Aïllats' ? '#F44336' : '#2196F3' }"
            >
              <h3 class="text-sm sm:text-base font-medium text-gray-800">{{ key }}</h3>
              <p class="text-2xl sm:text-3xl font-bold mt-1 sm:mt-2"
                 :style="{ color: key === 'Populars' ? '#4CAF50' : key === 'Aïllats' ? '#F44336' : '#2196F3' }"
              >
                {{ value }}
              </p>
            </div>
          </div>
         
          <!-- Gráfico circular de resumen -->
          <div class="h-64 sm:h-72 md:h-80 relative">
            <v-chart ref="pieChartRef" class="w-full h-full" :option="pieChartOptions" autoresize />
            <button 
              @click="downloadPieChart" 
              class="absolute top-2 right-2 px-3 py-1.5 bg-[#00ADEC] hover:bg-[#0099CC] text-white font-medium rounded-md shadow flex items-center"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Descargar
            </button>
          </div>
        </div>
       
        <!-- Gráfico comparativo por curso -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">Comparativa per Cursos</h2>
          <div class="h-72 sm:h-80 md:h-96 relative">
            <v-chart ref="barChartRef" class="w-full h-full" :option="barChartOptions" autoresize />
            <button 
              @click="downloadBarChart" 
              class="absolute top-2 right-2 px-3 py-1.5 bg-[#00ADEC] hover:bg-[#0099CC] text-white font-medium rounded-md shadow flex items-center"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Descargar
            </button>
          </div>
        </div>
       
        <!-- Tabla de datos detallados -->
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">Dades Detallades</h2>
          
          <!-- Tabla responsiva -->
          <div class="overflow-x-auto -mx-4 sm:mx-0 rounded-lg">
            <div class="inline-block min-w-full align-middle">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Curs
                    </th>
                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Populars
                    </th>
                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Aïllats
                    </th>
                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Neutrals
                    </th>
                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(item, index) in comparativeData" :key="index">
                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                      {{ item.course }}
                    </td>
                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ item.Populars || 0 }}
                      </span>
                    </td>
                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ item.Aïllats || 0 }}
                      </span>
                    </td>
                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ item.Neutrals || 0 }}
                      </span>
                    </td>
                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                      {{ (item.Populars || 0) + (item.Aïllats || 0) + (item.Neutrals || 0) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Nota para móviles -->
          <p class="text-xs text-gray-500 italic mt-3 block md:hidden">
            Desliza horizontalmente para ver más datos
          </p>
        </div>
      </div>
     
      <!-- Sin datos -->
      <div v-else class="bg-white rounded-lg shadow-md p-8 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900">No s'han trobat dades comparatives</h3>
        <p class="mt-2 text-gray-600">
          No hi ha dades disponibles per mostrar els gràfics comparatius. Assegura't que els sociogrames estan completats per als cursos.
        </p>
      </div>
    </div>
  </div>
</template>