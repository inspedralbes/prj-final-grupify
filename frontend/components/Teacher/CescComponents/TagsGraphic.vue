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
        <h2 class="text-2xl font-bold mb-2">Visualización de Datos CESC</h2>
        <p class="opacity-90">Análisis interactivo de perfiles de estudiantes</p>
      </div>

      <!-- Estadísticas principales en tarjetas modernas -->
      <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-5 shadow-lg border border-gray-100 transform transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-indigo-100 p-3 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-400 bg-gray-100 rounded-full px-2 py-1">Cursos</span>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-gray-800">{{ graphData.length }}</h3>
            <p class="text-sm text-gray-500 mt-1">Total de clases</p>
          </div>
          
          <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-5 shadow-lg border border-gray-100 transform transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-green-100 p-3 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-400 bg-gray-100 rounded-full px-2 py-1">Alumnos</span>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-gray-800">{{ totalStudents }}</h3>
            <p class="text-sm text-gray-500 mt-1">Total de estudiantes</p>
          </div>
          
          <div class="bg-gradient-to-br from-blue-50 to-white rounded-2xl p-5 shadow-lg border border-blue-100 transform transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-blue-100 p-3 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-blue-400 bg-blue-100 rounded-full px-2 py-1">Rebutjat</span>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-blue-600">{{ totalRebutjat }}</h3>
            <p class="text-sm text-blue-500 mt-1">Estudiantes</p>
          </div>
          
          <div class="bg-gradient-to-br from-amber-50 to-white rounded-2xl p-5 shadow-lg border border-amber-100 transform transition-all duration-300 hover:scale-105">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-amber-100 p-3 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-amber-400 bg-amber-100 rounded-full px-2 py-1">Víctima</span>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-amber-600">{{ totalVictima }}</h3>
            <p class="text-sm text-amber-500 mt-1">Estudiantes</p>
          </div>
        </div>
      </div>

      <!-- Buscador y selector de visualización -->
      <div class="px-6 pb-4">
        <!-- Buscador por clase -->
        <div class="mb-4 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <label for="class-search" class="block text-sm font-medium text-gray-700 mb-1">Buscar por clase</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input 
                  id="class-search" 
                  v-model="searchQuery" 
                  type="search" 
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Nombre del curso o división..."
                  @input="updateFilteredData"
                />
              </div>
            </div>
            <div class="flex-1">
              <label for="class-filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por etiqueta</label>
              <select 
                id="class-filter" 
                v-model="tagFilter"
                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                @change="updateFilteredData"
              >
                <option value="all">Todas las etiquetas</option>
                <option value="rebutjat">Solo Rebutjat</option>
                <option value="victima">Solo Víctima</option>
              </select>
            </div>
          </div>
          <div v-if="searchActive" class="mt-3 flex justify-between items-center">
            <p class="text-sm text-gray-600">
              <span v-if="filteredData.length === 0">No se encontraron resultados</span>
              <span v-else>Mostrando {{ filteredData.length }} de {{ graphData.length }} clases</span>
            </p>
            <button 
              @click="clearFilters" 
              class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar filtros
            </button>
          </div>
        </div>

        <!-- Selector de visualización -->
        <div class="flex justify-center flex-wrap gap-4 mb-4">
          <button
            @click="chartType = 'stacked'"
            class="px-6 py-3 rounded-xl text-sm font-medium transition-all duration-300 shadow-sm flex items-center gap-2"
            :class="chartType === 'stacked' ? 'bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white transform scale-105' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 5h14M5 19h14" />
            </svg>
            Apilado
          </button>
          <button
            @click="chartType = 'grouped'"
            class="px-6 py-3 rounded-xl text-sm font-medium transition-all duration-300 shadow-sm flex items-center gap-2"
            :class="chartType === 'grouped' ? 'bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white transform scale-105' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Agrupado
          </button>
          <button
            @click="chartType = 'percentage'"
            class="px-6 py-3 rounded-xl text-sm font-medium transition-all duration-300 shadow-sm flex items-center gap-2"
            :class="chartType === 'percentage' ? 'bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white transform scale-105' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
            </svg>
            Porcentajes
          </button>
        </div>
      </div>

      <!-- Gráfico con animaciones -->
      <div class="p-6 pt-0">
        <client-only>
          <VChart
            class="w-full h-[500px]"
            :option="chartOptions"
            autoresize
            @click="handleChartClick"
            :animation="true"
            :animation-duration="1500"
            :animation-easing="'elasticOut'"
          />
        </client-only>
      </div>

      <!-- Interacción -->
      <div class="px-6 pb-6 flex justify-center">
        <div class="bg-blue-50 p-3 rounded-xl border border-blue-100 inline-flex items-center text-blue-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Haz clic en cualquier barra para ver detalles
        </div>
      </div>

      <!-- Modal de detalles (aparece al hacer clic en una barra) -->
      <div v-if="selectedClass" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white p-4 rounded-t-xl flex justify-between items-center">
            <h3 class="text-xl font-bold">{{ selectedClass.course_name }} {{ selectedClass.division_name }}</h3>
            <button @click="selectedClass = null" class="text-white hover:text-gray-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="p-6">
            <!-- Estadísticas principales visuales -->
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div class="bg-gradient-to-br from-blue-50 to-white p-4 rounded-lg shadow-sm border border-blue-100 transform transition-all duration-300 hover:shadow-md">
                <div class="flex justify-between items-center">
                  <div class="bg-blue-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z" />
                    </svg>
                  </div>
                  <span class="text-xs font-medium text-blue-500 bg-blue-50 px-2 py-1 rounded-full">Alumnos</span>
                </div>
                <p class="text-sm text-blue-600 mt-2">Total estudiantes</p>
                <p class="text-3xl font-bold text-blue-800">{{ selectedClass.total_students }}</p>
              </div>
              <div class="bg-gradient-to-br from-amber-50 to-white p-4 rounded-lg shadow-sm border border-amber-100 transform transition-all duration-300 hover:shadow-md">
                <div class="flex justify-between items-center">
                  <div class="bg-amber-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <span class="text-xs font-medium text-amber-500 bg-amber-50 px-2 py-1 rounded-full">Tags</span>
                </div>
                <p class="text-sm text-amber-600 mt-2">Total etiquetas</p>
                <p class="text-3xl font-bold text-amber-800">{{ selectedClass.tag_2_count + selectedClass.tag_5_count }}</p>
              </div>
            </div>

            <!-- Mini gráfico dinámico para visualizar la proporción -->
            <div class="bg-white p-4 rounded-lg border border-gray-200 mb-6">
              <h4 class="text-lg font-medium text-gray-800 mb-3">Distribución de etiquetas</h4>
              <div class="h-8 w-full bg-gray-100 rounded-full overflow-hidden flex">
                <div 
                  class="h-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium"
                  :style="{ width: `${(selectedClass.tag_2_count / (selectedClass.tag_2_count + selectedClass.tag_5_count || 1)) * 100}%` }"
                >
                  {{ selectedClass.tag_2_count > 0 ? 'Rebutjat' : '' }}
                </div>
                <div 
                  class="h-full bg-amber-500 flex items-center justify-center text-white text-xs font-medium"
                  :style="{ width: `${(selectedClass.tag_5_count / (selectedClass.tag_2_count + selectedClass.tag_5_count || 1)) * 100}%` }"
                >
                  {{ selectedClass.tag_5_count > 0 ? 'Víctima' : '' }}
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>Rebutjat: {{ ((selectedClass.tag_2_count / selectedClass.total_students) * 100).toFixed(1) }}%</span>
                <span>Víctima: {{ ((selectedClass.tag_5_count / selectedClass.total_students) * 100).toFixed(1) }}%</span>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Sección de Rebutjat -->
              <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                <div class="flex justify-between items-center mb-2">
                  <h4 class="font-medium text-blue-800">Rebutjat</h4>
                  <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-medium">{{ selectedClass.tag_2_count }} estudiantes</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: `${(selectedClass.tag_2_count / selectedClass.total_students) * 100}%` }"></div>
                </div>
                <p class="text-blue-600 text-sm mt-2">{{ ((selectedClass.tag_2_count / selectedClass.total_students) * 100).toFixed(1) }}% de la clase</p>
                
                <!-- Lista de alumnos en riesgo de Rebutjat -->
                <div v-if="selectedClass.at_risk_students && selectedClass.at_risk_students.rebutjat_students.length > 0" class="mt-4">
                  <h5 class="font-medium text-blue-800 mb-2">Alumnos con mayor riesgo:</h5>
                  <div class="bg-white rounded-lg shadow-sm p-2">
                    <ul class="divide-y divide-gray-100">
                      <li v-for="(student, index) in selectedClass.at_risk_students.rebutjat_students" :key="index" class="py-2 flex justify-between items-center">
                        <div class="flex items-center">
                          <span class="bg-blue-100 text-blue-800 w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium mr-2">{{ index + 1 }}</span>
                          <span class="text-gray-800">{{ student.name }} <span class="text-gray-500 text-sm">(ID: {{ student.student_id }})</span></span>
                        </div>
                        <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-sm font-medium">{{ student.vote_count }} votos</span>
                      </li>
                    </ul>
                  </div>
                </div>
                <div v-else class="mt-2 text-blue-600 text-sm italic">
                  No hay datos específicos de alumnos en riesgo.
                </div>
              </div>

              <!-- Sección de Víctima -->
              <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
                <div class="flex justify-between items-center mb-2">
                  <h4 class="font-medium text-amber-800">Víctima</h4>
                  <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded text-sm font-medium">{{ selectedClass.tag_5_count }} estudiantes</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                  <div class="bg-amber-500 h-2.5 rounded-full" :style="{ width: `${(selectedClass.tag_5_count / selectedClass.total_students) * 100}%` }"></div>
                </div>
                <p class="text-amber-600 text-sm mt-2">{{ ((selectedClass.tag_5_count / selectedClass.total_students) * 100).toFixed(1) }}% de la clase</p>
                
                <!-- Lista de alumnos en riesgo de Víctima -->
                <div v-if="selectedClass.at_risk_students && selectedClass.at_risk_students.victima_students.length > 0" class="mt-4">
                  <h5 class="font-medium text-amber-800 mb-2">Alumnos con mayor riesgo:</h5>
                  <div class="bg-white rounded-lg shadow-sm p-2">
                    <ul class="divide-y divide-gray-100">
                      <li v-for="(student, index) in selectedClass.at_risk_students.victima_students" :key="index" class="py-2 flex justify-between items-center">
                        <div class="flex items-center">
                          <span class="bg-amber-100 text-amber-800 w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium mr-2">{{ index + 1 }}</span>
                          <span class="text-gray-800">{{ student.name }} <span class="text-gray-500 text-sm">(ID: {{ student.student_id }})</span></span>
                        </div>
                        <span class="bg-amber-50 text-amber-700 px-2 py-1 rounded text-sm font-medium">{{ student.vote_count }} votos</span>
                      </li>
                    </ul>
                  </div>
                </div>
                <div v-else class="mt-2 text-amber-600 text-sm italic">
                  No hay datos específicos de alumnos en riesgo.
                </div>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end">
              <button @click="selectedClass = null" class="px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all duration-300 transform hover:scale-105 shadow-md font-medium flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cerrar
              </button>
            </div>
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

// Estado
const graphData = ref([]);
const filteredData = ref([]);
const isLoading = ref(true);
const error = ref(null);
const chartType = ref('stacked'); // 'stacked', 'grouped', 'percentage'
const selectedClass = ref(null); // Para el modal de detalles

// Variables para el buscador y filtros
const searchQuery = ref('');
const tagFilter = ref('all');
const searchActive = computed(() => searchQuery.value.trim() !== '' || tagFilter.value !== 'all');

// Función para actualizar datos filtrados
const updateFilteredData = () => {
  const query = searchQuery.value.toLowerCase().trim();
  
  filteredData.value = graphData.value.filter(item => {
    const matchesSearch = query === '' || 
      item.course_name.toLowerCase().includes(query) || 
      item.division_name.toLowerCase().includes(query);

    let matchesTag = true;
    if (tagFilter.value === 'rebutjat') {
      matchesTag = item.tag_2_count > 0;
    } else if (tagFilter.value === 'victima') {
      matchesTag = item.tag_5_count > 0;
    }

    return matchesSearch && matchesTag;
  });
};

// Limpiar filtros
const clearFilters = () => {
  searchQuery.value = '';
  tagFilter.value = 'all';
  updateFilteredData();
};

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
    const dataToUse = searchActive.value ? filteredData.value : graphData.value;
    selectedClass.value = dataToUse[dataIndex];
    console.log('Clase seleccionada:', selectedClass.value);
  }
};

// Opciones del gráfico
const chartOptions = computed(() => {
  const dataToUse = searchActive.value ? filteredData.value : graphData.value;
  
  // Extraer nombres de cursos y divisiones para el eje X
  const categories = dataToUse.map(item => `${item.course_name} ${item.division_name}`);

  // Datos para las series
  const rebutjatData = dataToUse.map(item => {
    if (chartType.value === 'percentage') {
      return item.total_students > 0 ? (item.tag_2_count / item.total_students) * 100 : 0;
    }
    return item.tag_2_count;
  });

  const victimaData = dataToUse.map(item => {
    if (chartType.value === 'percentage') {
      return item.total_students > 0 ? (item.tag_5_count / item.total_students) * 100 : 0;
    }
    return item.tag_5_count;
  });

  // Configuración básica del gráfico
  const baseConfig = {
    animation: true,
    animationThreshold: 1000,
    animationDuration: 1000,
    animationEasing: 'cubicInOut',
    animationDelay: function (idx) {
      return idx * 120;
    },
    animationDurationUpdate: 800,
    animationEasingUpdate: 'quinticInOut',
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' },
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      borderColor: 'rgba(255, 255, 255, 0.2)',
      borderWidth: 1,
      padding: 10,
      textStyle: {
        color: '#fff',
        fontSize: 12
      },
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

        // Añadir información sobre alumnos en riesgo si está disponible
        if (item.at_risk_students) {
          if (item.at_risk_students.rebutjat_students && item.at_risk_students.rebutjat_students.length > 0) {
            html += `<div style="margin-top:8px;border-top:1px solid #eee;padding-top:5px;">
                      <div style="font-weight:bold;margin-bottom:3px;color:#3b82f6;">Top Rebutjat:</div>`;
            
            item.at_risk_students.rebutjat_students.slice(0, 2).forEach((student, idx) => {
              html += `<div style="margin-left:5px;font-size:0.9em;">${student.name} (${student.vote_count} votos)</div>`;
            });
            
            html += `</div>`;
          }
          
          if (item.at_risk_students.victima_students && item.at_risk_students.victima_students.length > 0) {
            html += `<div style="margin-top:8px;border-top:1px solid #eee;padding-top:5px;">
                      <div style="font-weight:bold;margin-bottom:3px;color:#f59e0b;">Top Víctima:</div>`;
            
            item.at_risk_students.victima_students.slice(0, 2).forEach((student, idx) => {
              html += `<div style="margin-left:5px;font-size:0.9em;">${student.name} (${student.vote_count} votos)</div>`;
            });
            
            html += `</div>`;
          }
        }

        html += `<div style="margin-top:8px;text-align:center;font-style:italic;font-size:0.9em;color:#666;">Click para más detalles</div>`;
        
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
          focus: 'series',
          itemStyle: {
            shadowBlur: 10,
            shadowColor: 'rgba(59, 130, 246, 0.5)'
          }
        },
        itemStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: '#60a5fa' },
              { offset: 1, color: '#3b82f6' }
            ]
          },
          borderRadius: [4, 4, 0, 0]
        },
        data: rebutjatData
      },
      {
        name: 'Víctima',
        type: 'bar',
        stack: chartType.value === 'grouped' ? undefined : 'total',
        emphasis: {
          focus: 'series',
          itemStyle: {
            shadowBlur: 10,
            shadowColor: 'rgba(245, 158, 11, 0.5)'
          }
        },
        itemStyle: {
          color: {
            type: 'linear',
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: '#fbbf24' },
              { offset: 1, color: '#f59e0b' }
            ]
          },
          borderRadius: [4, 4, 0, 0]
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
    filteredData.value = [...data]; // Inicializar los datos filtrados con todos los datos

    if (data.length === 0) {
      error.value = "No hay datos disponibles para mostrar";
    }
    
    console.log('Datos cargados:', graphData.value);
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

// Actualizar filteredData cuando cambian los datos o cuando se monta el componente
watch([graphData, searchQuery, tagFilter], () => {
  updateFilteredData();
});
</script>
