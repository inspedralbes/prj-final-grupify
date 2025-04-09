<template>
  <div class="space-y-8 mt-8">
    <!-- Pantalla de carga -->
    <div v-if="isLoading" class="flex flex-col justify-center items-center h-64 bg-white rounded-xl shadow-md p-8">
      <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-[#00ADEC] mb-4"></div>
      <p class="text-gray-600 font-medium">Carregant dades...</p>
    </div>

    <!-- Mensaje de error -->
    <div v-else-if="error" class="text-center bg-red-50 text-red-600 p-6 rounded-xl shadow-md border border-red-200">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <h3 class="text-lg font-semibold mb-2">Error en carregar les dades</h3>
      <p>{{ error }}</p>
    </div>

    <!-- Contenido principal cuando hay datos -->
    <div v-else class="bg-white rounded-2xl shadow-xl overflow-hidden">
      <!-- Título y filtros -->
      <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white p-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
          <h2 class="text-xl font-bold mb-4 md:mb-0">Evolució de Competències</h2>
          <div v-if="availableYears.length > 1" class="flex flex-wrap items-center gap-4">
            <!-- Selector desplegable para competencias -->
            <div class="relative">
              <button 
                @click="toggleDropdown($event)"
                class="flex items-center bg-white text-gray-800 rounded-lg px-4 py-2 border-0 focus:outline-none focus:ring-2 focus:ring-white"
              >
                <span class="mr-2">{{ dropdownLabel }}</span>
                <svg 
                  xmlns="http://www.w3.org/2000/svg" 
                  class="h-5 w-5 transition-transform" 
                  :class="isDropdownOpen ? 'transform rotate-180' : ''" 
                  fill="none" 
                  viewBox="0 0 24 24" 
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <!-- Menú desplegable -->
              <div 
                v-if="isDropdownOpen" 
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg overflow-hidden z-10"
              >
                <div class="max-h-60 overflow-y-auto py-1">
                  <div 
                    class="px-4 py-2 cursor-pointer hover:bg-gray-100 flex items-center text-gray-800"
                    @click.stop="toggleCompetencia('todas')"
                  >
                    <input 
                      type="checkbox" 
                      :checked="selectedCompetencias.includes('todas')" 
                      class="mr-2 h-4 w-4 rounded text-blue-500"
                      @click.stop
                    />
                    <span>Totes</span>
                  </div>
                  <div 
                    v-for="comp in competenciasList" 
                    :key="comp.id" 
                    class="px-4 py-2 cursor-pointer hover:bg-gray-100 flex items-center text-gray-800"
                    @click.stop="toggleCompetencia(comp.id)"
                  >
                    <input 
                      type="checkbox" 
                      :checked="selectedCompetencias.includes(comp.id)" 
                      class="mr-2 h-4 w-4 rounded text-blue-500"
                      @click.stop
                    />
                    <span>{{ comp.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráfico -->
      <div class="p-6">
        <client-only>
          <VChart
            class="w-full h-[500px]"
            :option="chartOptions"
            autoresize
          />
        </client-only>
      </div>

      <!-- Leyenda explicativa -->
      <div class="px-6 pb-6">
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
          <h3 class="font-semibold text-gray-700 mb-2">Interpretació del gràfic</h3>
          <ul class="list-disc pl-5 text-gray-600 space-y-1">
            <li>El gràfic mostra l'evolució de les competències de l'alumne al llarg dels anys</li>
            <li>Cada línia representa una competència diferent</li>
            <li>Els valors estan en escala de 0 a 10, on 10 representa el màxim nivell</li>
            <li>Pots seleccionar quines competències mostrar utilitzant el filtre superior</li>
            <li>Les dades s'actualitzen quan completes el formulari anual d'autoavaluació</li>
          </ul>
        </div>
      </div>

      <!-- Tabla de resumen -->
      <div class="px-6 pb-6">
        <h3 class="font-semibold text-gray-700 mb-4">Resum de Competències</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Competència</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Últim valor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evolució (vs anterior)</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(comp, index) in competenciasList" :key="index">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ comp.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ getLastValue(comp.id) !== null ? getLastValue(comp.id).toFixed(1) : 'Sense dades' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span 
                      v-if="getEvolucion(comp.id) !== null"
                      :class="getEvolucion(comp.id) > 0 ? 'text-green-600' : getEvolucion(comp.id) < 0 ? 'text-red-600' : 'text-gray-500'"
                    >
                      <template v-if="getEvolucion(comp.id) > 0">
                        <span class="inline-flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                          </svg>
                          +{{ getEvolucion(comp.id).toFixed(1) }}
                        </span>
                      </template>
                      <template v-else-if="getEvolucion(comp.id) < 0">
                        <span class="inline-flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                          {{ getEvolucion(comp.id).toFixed(1) }}
                        </span>
                      </template>
                      <template v-else>
                        <span class="inline-flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                          </svg>
                          0.0
                        </span>
                      </template>
                    </span>
                    <span v-else class="text-gray-400">Sense dades prèvies</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { LineChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, LegendComponent, DataZoomComponent } from 'echarts/components';

// Registrar componentes de ECharts
use([CanvasRenderer, LineChart, GridComponent, TooltipComponent, LegendComponent, DataZoomComponent]);

// Estado
const isLoading = ref(true);
const error = ref(null);
const userId = ref('user123'); // Esto vendría de props o del store
const formId = ref('form456'); // Esto vendría de props o del store
const selectedCompetencias = ref(['todas']);
const availableYears = ref([]);
const competenciasData = ref([]);

// Lista de las 8 competencias
const competenciasList = ref([
  { id: 'comp1', name: 'Comunicació' },
  { id: 'comp2', name: 'Treball en equip' },
  { id: 'comp3', name: 'Resolució de problemes' },
  { id: 'comp4', name: 'Creativitat' },
  { id: 'comp5', name: 'Lideratge' },
  { id: 'comp6', name: 'Gestió del temps' },
  { id: 'comp7', name: 'Adaptabilitat' },
  { id: 'comp8', name: 'Pensament crític' }
]);

// Opciones del gráfico
const chartOptions = computed(() => {
  // Colores para las diferentes competencias
  const colors = [
    '#3B82F6', '#10B981', '#F59E0B', '#EC4899', 
    '#8B5CF6', '#EF4444', '#6366F1', '#14B8A6'
  ];
  
  // Filtrar las competencias según la selección
  const filteredCompetencias = selectedCompetencias.value.includes('todas') 
    ? competenciasList.value 
    : competenciasList.value.filter(comp => selectedCompetencias.value.includes(comp.id));
  
  // Configurar series para cada competencia
  const series = filteredCompetencias.map((comp, index) => {
    return {
      name: comp.name,
      type: 'line',
      data: availableYears.value.map(year => {
        const foundData = competenciasData.value.find(d => d.year === year && d.competenciaId === comp.id);
        return foundData ? foundData.value : null;
      }),
      symbolSize: 8,
      symbol: 'circle',
      smooth: true,
      lineStyle: {
        width: 3
      },
      itemStyle: {
        color: colors[index % colors.length]
      }
    };
  });

  return {
    tooltip: {
      trigger: 'axis',
      formatter: function(params) {
        let tooltipContent = `<div style="font-weight:bold;margin-bottom:5px;">${params[0].name}</div>`;
        
        params.forEach(param => {
          tooltipContent += `
            <div style="display:flex;align-items:center;margin:5px 0;">
              <span style="display:inline-block;width:10px;height:10px;background:${param.color};border-radius:50%;margin-right:5px;"></span>
              <span>${param.seriesName}: ${param.value !== null ? param.value.toFixed(1) : 'Sense dades'}</span>
            </div>
          `;
        });
        
        return tooltipContent;
      }
    },
    legend: {
      data: filteredCompetencias.map(comp => comp.name),
      bottom: 0,
      type: 'scroll',
      padding: [0, 20]
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
      boundaryGap: false,
      data: availableYears.value,
      axisLabel: {
        rotate: 0,
        interval: 0,
        fontSize: 11,
        color: '#666'
      }
    },
    yAxis: {
      type: 'value',
      name: 'Nivell de competència',
      nameLocation: 'middle',
      nameGap: 40,
      min: 0,
      max: 10,
      interval: 1
    },
    series: series
  };
});

// Función para obtener el último valor de una competencia
const getLastValue = (competenciaId) => {
  if (availableYears.value.length === 0) return null;
  
  const lastYear = availableYears.value[availableYears.value.length - 1];
  const lastData = competenciasData.value.find(d => d.year === lastYear && d.competenciaId === competenciaId);
  
  return lastData ? lastData.value : null;
};

// Función para calcular la evolución de una competencia respecto al año anterior
const getEvolucion = (competenciaId) => {
  if (availableYears.value.length < 2) return null;
  
  const lastYear = availableYears.value[availableYears.value.length - 1];
  const prevYear = availableYears.value[availableYears.value.length - 2];
  
  const lastData = competenciasData.value.find(d => d.year === lastYear && d.competenciaId === competenciaId);
  const prevData = competenciasData.value.find(d => d.year === prevYear && d.competenciaId === competenciaId);
  
  if (!lastData || !prevData) return null;
  
  return lastData.value - prevData.value;
};

// Función para generar datos aleatorios para testing
const generateRandomData = () => {
  // Generar años disponibles (año actual y 3 años anteriores)
  const currentYear = new Date().getFullYear();
  const years = [
    currentYear.toString(), 
    (currentYear - 1).toString(), 
    (currentYear - 2).toString(),
    (currentYear - 3).toString()
  ];
  
  availableYears.value = years;
  
  // Generar datos para cada año y cada competencia
  const data = [];
  
  years.forEach(year => {
    competenciasList.value.forEach(comp => {
      // Simular progresión a lo largo de los años (valores ligeramente crecientes)
      const yearIndex = years.indexOf(year);
      const baseValue = 5 + Math.random() * 2; // Base entre 5 y 7
      const yearFactor = (years.length - 1 - yearIndex) * 0.5; // Factor decreciente por año antiguo
      
      data.push({
        year: year,
        competenciaId: comp.id,
        value: Math.min(10, Math.max(0, parseFloat((baseValue + yearFactor + (Math.random() - 0.5)).toFixed(1))))
      });
    });
  });
  
  competenciasData.value = data;
};

// Función para cargar datos reales (a implementar cuando se conecte con el backend)
const fetchRealData = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await fetch(`http://tu-api.com/competencias/${userId.value}/${formId.value}`);

    if (!response.ok) {
      throw new Error(`Error en carregar les dades: ${response.status}`);
    }

    const data = await response.json();
    
    // Procesar los datos recibidos del backend
    availableYears.value = data.years;
    competenciasData.value = data.competenciasData;
    
  } catch (err) {
    console.error('Error en carregar les dades:', err);
    error.value = err.message || 'Error en carregar les dades';
  } finally {
    isLoading.value = false;
  }
};

// Variables para el desplegable
const isDropdownOpen = ref(false);

// Etiqueta para el botón del desplegable
const dropdownLabel = computed(() => {
  if (selectedCompetencias.value.includes('todas')) {
    return 'Totes les competències';
  } else if (selectedCompetencias.value.length === 1) {
    const competencia = competenciasList.value.find(c => c.id === selectedCompetencias.value[0]);
    return competencia ? competencia.name : 'Seleccionar competència';
  } else if (selectedCompetencias.value.length > 1) {
    return `${selectedCompetencias.value.length} competències`;
  } else {
    return 'Seleccionar competència';
  }
});

// Función para abrir/cerrar el desplegable
const toggleDropdown = (event) => {
  // Importante: detener la propagación del evento para evitar
  // que se cierre inmediatamente debido al event listener global
  event.stopPropagation();
  isDropdownOpen.value = !isDropdownOpen.value;
};

// Modificación de la función toggleCompetencia
const toggleCompetencia = (compId) => {
  if (compId === 'todas') {
    // Si se selecciona 'todas'
    selectedCompetencias.value = ['todas'];
  } else {
    // Si se selecciona una competencia específica
    if (selectedCompetencias.value.includes('todas')) {
      // Si 'todas' estaba seleccionado, quitar 'todas' y poner solo la competencia actual
      selectedCompetencias.value = [compId];
    } else if (selectedCompetencias.value.includes(compId) && selectedCompetencias.value.length === 1) {
      // Si es la única competencia seleccionada y se hace clic de nuevo, volver a 'todas'
      selectedCompetencias.value = ['todas'];
    } else if (selectedCompetencias.value.includes(compId)) {
      // Si ya estaba seleccionada y hay varias seleccionadas, quitarla
      selectedCompetencias.value = selectedCompetencias.value.filter(id => id !== compId);
    } else {
      // Si no estaba seleccionada, añadirla
      selectedCompetencias.value.push(compId);
    }
  }
  
  // Si no hay nada seleccionado, poner 'todas' por defecto
  if (selectedCompetencias.value.length === 0) {
    selectedCompetencias.value = ['todas'];
  }
  
  // Si todas las competencias están seleccionadas individualmente, cambiar a 'todas'
  if (selectedCompetencias.value.length === competenciasList.value.length) {
    selectedCompetencias.value = ['todas'];
  }
};

// Cerrar el desplegable al hacer clic fuera
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (isDropdownOpen.value) {
      const dropdown = document.querySelector('.relative');
      if (dropdown && !dropdown.contains(e.target)) {
        isDropdownOpen.value = false;
      }
    }
  });
  
  // Para pruebas, usamos los datos generados
  generateRandomData();
  isLoading.value = false;
  
  // Cuando se conecte al backend, usar esta función en lugar de la generación de datos de prueba
  // fetchRealData();
});
</script>