<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <DashboardNavOrientador class="w-full sticky top-0 z-10" />
    <RiskAlertPopup
      v-model="showRiskAlert"
      :aggressive-students="topAggressiveStudents"
      :victim-students="topVictimStudents"
      :rejected-students="topRejectedStudents"
      @create-report="createPdfReport"
      @view-student="viewStudent"
    />
    <div class="cesc-results-container py-8 flex-1">
      <!-- Cabecera mejorada y centrada -->
      <div class="cesc-header">
        <div class="flex flex-col sm:flex-row justify-between items-center text-center sm:text-left">
          <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-[#0080C0]">
              RESULTATS CESC
            </h1>
            <p class="mt-2 text-gray-600" v-if="course">
              {{ course.courseName }} {{ course.division.name }}
            </p>
          </div>
          <!-- Badge con estadísticas -->
          <div class="flex space-x-3 justify-center">
            <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
              {{ students.length }} Alumnes
            </div>
            <div class="bg-purple-50 text-purple-700 px-3 py-1 rounded-full text-sm font-medium">
              {{ uniqueTags.length }} Categories
            </div>
          </div>
        </div>
      </div>
      
      <!-- Estado de carga -->
      <div v-if="isLoading" class="cesc-results-card p-8 flex flex-col items-center justify-center min-h-[300px]">
        <div class="w-16 h-16 border-4 border-t-[#0080C0] border-[#0080C0]/30 rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant resultats CESC...</p>
      </div>
      
      <!-- Estado de error -->
      <div v-else-if="error" class="bg-red-50 text-red-700 p-6 rounded-lg shadow-md border border-red-200 cesc-results-card">
        <div class="flex items-center mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="font-semibold">Error al carregar les dades</h3>
        </div>
        <p>{{ error }}</p>
      </div>

      <!-- Contenido principal cuando hay datos -->
      <div v-else class="space-y-6">
        <!-- Mensaje de datos vacíos -->
        <div v-if="filtered.length === 0" class="cesc-results-card p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No hi ha dades disponibles per a aquesta classe</h3>
          <p class="text-gray-600 max-w-md mx-auto mb-4">
            No s'han trobat resultats CESC per a {{ course?.courseName }} {{ course?.division.name }}.
          </p>
          <p class="text-sm text-red-600">
            Assegura't d'executar primer "Calcular Resultats" al panell d'administració CESC.
          </p>
        </div>

        <!-- Selector de visualización mejorado y centrado -->
        <div v-if="groupedResults.length > 0" class="cesc-visualization">
          <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 sm:mb-0">Visualització de Resultats</h3>
            <div class="flex flex-wrap gap-2 justify-center">
              <button
                @click="chartType = 'stacked'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:scale-105 flex items-center"
                :class="chartType === 'stacked' ? 'bg-[#0080C0] text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                </svg>
                Gràfic apilat
              </button>
              <button
                @click="chartType = 'grouped'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:scale-105 flex items-center"
                :class="chartType === 'grouped' ? 'bg-[#0080C0] text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2 10a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1H3a1 1 0 01-1-1v-6zm6-4a1 1 0 011-1h2a1 1 0 011 1v10a1 1 0 01-1 1H9a1 1 0 01-1-1V6zm6-2a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                </svg>
                Gràfic agrupat
              </button>
              <button
                @click="chartType = 'radar'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:scale-105 flex items-center"
                :class="chartType === 'radar' ? 'bg-[#0080C0] text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                </svg>
                Gràfic radar
              </button>
            </div>
          </div>

          <!-- Gráfico principal mejorado -->
          <div class="mt-2 border border-gray-200 rounded-lg p-4 bg-gray-50">
            <div class="flex flex-col lg:flex-row">
              <!-- Panel de filtros a la izquierda -->
              <div class="lg:w-1/4 p-3">
                <h4 class="font-medium text-gray-700 mb-3">Filtres</h4>
                <div class="space-y-4">
                  <!-- Selector de estudiante para destacar -->
                  <div>
                    <label class="block text-sm text-gray-600 mb-1">Destacar alumne:</label>
                    <select 
                      v-model="highlightedStudent" 
                      class="w-full p-2 border border-gray-300 rounded-md text-sm"
                    >
                      <option :value="null">Tots els alumnes</option>
                      <option v-for="student in groupedResults" :key="student.fullName" :value="student.fullName">
                        {{ student.fullName }}
                      </option>
                    </select>
                  </div>
                  
                  <!-- Selector de tags a mostrar -->
                  <div>
                    <label class="block text-sm text-gray-600 mb-1">Tags a mostrar:</label>
                    <div class="space-y-1">
                      <div v-for="tag in allPossibleTags" :key="tag" class="flex items-center">
                        <input 
                          type="checkbox" 
                          :id="'tag-' + tag" 
                          v-model="selectedTags" 
                          :value="tag" 
                          class="mr-2"
                        />
                        <label :for="'tag-' + tag" class="text-sm text-gray-700">{{ tag }}</label>
                      </div>
                    </div>
                  </div>
                  

                </div>
              </div>

              <!-- Gráfico principal a la derecha -->
              <div class="lg:w-3/4">
                <client-only>
                  <v-chart class="w-full h-[500px]" :option="chartOption" autoresize @click="handleChartClick" />
                </client-only>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla de resultados -->
        <div v-if="groupedResults.length > 0" class="cesc-table-container">
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
            <div class="flex flex-col sm:flex-row justify-between items-center">
              <h3 class="text-lg font-semibold text-gray-800 mb-3 sm:mb-0">Resultats Detallats</h3>
              <div>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Cercar alumne..." 
                    class="pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 absolute top-3 left-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                    <button @click="sortBy('fullName')" class="flex items-center focus:outline-none">
                      Alumne
                      <svg v-if="sortColumn === 'fullName'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                      </svg>
                    </button>
                  </th>
                  <th v-for="(tag, index) in uniqueTags" :key="tag" 
                      class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider"
                      :class="getTagHeaderColor(index)">
                    <button @click="sortBy(tag)" class="flex items-center justify-center mx-auto focus:outline-none">
                      {{ tag }}
                      <svg v-if="sortColumn === tag" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                      </svg>
                    </button>
                  </th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                    <button @click="sortBy('total')" class="flex items-center justify-center mx-auto focus:outline-none">
                      Total
                      <svg v-if="sortColumn === 'total'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                      </svg>
                    </button>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="student in sortedAndFilteredStudents" 
                    :key="student.fullName"
                    :class="{
                      'bg-white hover:bg-gray-50 transition-colors': !isHighlightedRowByRank(student),
                      'bg-red-50 hover:bg-red-100 transition-colors': isHighlightedRowByRank(student, 'Agressiu'),
                      'bg-yellow-50 hover:bg-yellow-100 transition-colors': isHighlightedRowByRank(student, 'Víctima'),
                      'bg-blue-50 hover:bg-blue-100 transition-colors': isHighlightedRowByRank(student, 'Rebutjat'),
                      'bg-purple-50 hover:bg-purple-100 transition-colors': highlightedStudent === student.fullName && !isHighlightedRowByRank(student)
                    }">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-medium">
                        {{ student.fullName.substring(0, 2).toUpperCase() }}
                      </div>
                      <div class="ml-3">
                        <div :class="{
                          'font-medium text-gray-900': !isHighlightedRowByRank(student),
                          'font-semibold text-red-800': isHighlightedRowByRank(student, 'Agressiu'),
                          'font-semibold text-yellow-800': isHighlightedRowByRank(student, 'Víctima'),
                          'font-semibold text-blue-800': isHighlightedRowByRank(student, 'Rebutjat')
                        }">
                          {{ student.fullName }}
                        </div>
                        <div class="flex space-x-1 mt-1">
                          <span v-if="isHighlightedRowByRank(student, 'Agressiu')" 
                                class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Agressiu (Top 2)
                          </span>
                          <span v-if="isHighlightedRowByRank(student, 'Víctima')" 
                                class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Víctima (Top 2)
                          </span>
                          <span v-if="isHighlightedRowByRank(student, 'Rebutjat')" 
                                class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Rebutjat (Top 2)
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td v-for="(tag, index) in uniqueTags" :key="tag"
                      class="px-6 py-4 whitespace-nowrap text-center">
                    <span v-if="student.tags[tag]" 
                          class="px-3 py-1 rounded-full"
                          :class="[
                            getTagBadgeClasses(index),
                            { 'font-bold': isHighlightedRowByRank(student) && (
                              (tag === 'Agressiu' && isHighlightedRowByRank(student, 'Agressiu')) || 
                              (tag === 'Víctima' && isHighlightedRowByRank(student, 'Víctima')) ||
                              (tag === 'Rebutjat' && isHighlightedRowByRank(student, 'Rebutjat'))
                            ) }
                          ]">
                      {{ student.tags[tag] }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full font-medium">
                      {{ studentTotal(student) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Alerta de estudiantes que necesitan ayuda -->
    <div v-if="showAlertModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
      <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-auto">
        <div class="bg-gradient-to-r from-red-600 to-yellow-600 text-white p-4 rounded-t-xl flex justify-between items-center">
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-xl font-bold">Alumnes que necessiten atenció</h3>
          </div>
          <button @click="showAlertModal = false" class="text-white hover:text-gray-200 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <p class="text-gray-600 mb-4">S'han identificat els següents alumnes que podrien necessitar atenció especial:</p>
          
          <!-- Alumnos agresivos -->
          <div v-if="topAggressiveStudents.length > 0" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <h4 class="text-red-800 font-semibold flex items-center mb-2">
              <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
              Alumnes amb comportament agressiu (Top 2)
            </h4>
            <ul class="list-disc pl-5 text-red-700">
              <li v-for="student in topAggressiveStudents" :key="student.fullName" class="mb-1">
                <span class="font-medium">{{ student.fullName }}</span> - 
                <span class="font-bold">{{ student.tags['Agressiu'] }} punts</span>
              </li>
            </ul>
          </div>
          
          <!-- Alumnos víctimas -->
          <div v-if="topVictimStudents.length > 0" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h4 class="text-yellow-800 font-semibold flex items-center mb-2">
              <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
              Alumnes identificats com a víctimes (Top 2)
            </h4>
            <ul class="list-disc pl-5 text-yellow-700">
              <li v-for="student in topVictimStudents" :key="student.fullName" class="mb-1">
                <span class="font-medium">{{ student.fullName }}</span> - 
                <span class="font-bold">{{ student.tags['Víctima'] }} punts</span>
              </li>
            </ul>
          </div>
          
          <!-- Alumnos rechazados -->
          <div v-if="topRejectedStudents.length > 0" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="text-blue-800 font-semibold flex items-center mb-2">
              <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
              Alumnes amb rebuig social (Top 2)
            </h4>
            <ul class="list-disc pl-5 text-blue-700">
              <li v-for="student in topRejectedStudents" :key="student.fullName" class="mb-1">
                <span class="font-medium">{{ student.fullName }}</span> - 
                <span class="font-bold">{{ student.tags['Rebutjat'] }} punts</span>
              </li>
            </ul>
          </div>
          
          <div class="mt-6 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
            <h4 class="text-indigo-800 font-semibold mb-2">Recomanacions generals:</h4>
            <ul class="list-disc pl-5 text-indigo-700 space-y-1">
              <li>Programar sessions individuals amb aquests alumnes.</li>
              <li>Considerar la implementació d'activitats d'integració i cohesió grupal.</li>
              <li>Fer un seguiment més detallat del seu comportament i evolució.</li>
              <li>Consultar amb l'equip d'orientació per obtenir més recursos.</li>
            </ul>
          </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-b-xl border-t border-gray-200 flex justify-between">
          <button @click="createPdfReport" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Descarregar Informe
          </button>
          <button @click="showAlertModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
            Entès
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de detalles de estudiante mejorado -->
    <div v-if="selectedStudent" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
      <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto mx-auto">
        <div class="bg-gradient-to-r from-[#0080C0] to-[#005687] text-white p-6 rounded-t-xl flex justify-between items-center">
          <div>
            <h3 class="text-2xl font-bold">{{ selectedStudent.fullName }}</h3>
            <p class="text-blue-100 mt-1">{{ course?.courseName }} {{ course?.division.name }}</p>
          </div>
          <button @click="selectedStudent = null" class="text-white hover:text-gray-200 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <!-- Resumen del estudiante -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Tags totales -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Tags totals</span>
                <span class="text-2xl font-bold text-gray-800">{{ studentTotal(selectedStudent) }}</span>
              </div>
            </div>

            <!-- Tag más alto -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Tag més alt</span>
                <span :class="`text-sm font-medium px-2 py-1 rounded-full ${getTagBadgeClasses(uniqueTags.indexOf(highestTag(selectedStudent)))}`">
                  {{ highestTag(selectedStudent) }} ({{ highestTagValue(selectedStudent) }})
                </span>
              </div>
            </div>

            <!-- Valor percentil -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Percentil</span>
                <span class="text-sm font-medium px-2 py-1 rounded-full" :class="getPercentileClass(studentPercentile(selectedStudent))">
                  {{ studentPercentile(selectedStudent) }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Gráfico de radar personalizado para el estudiante -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-700 mb-3">Perfil Individual</h4>
            <client-only>
              <v-chart class="h-[300px] w-full" :option="studentRadarOption" autoresize />
            </client-only>
          </div>

          <!-- Distribución detallada de tags -->
          <div class="space-y-4 mb-6">
            <h4 class="font-medium text-gray-700 mb-2">Distribució de Tags</h4>
            <div v-for="(tag, index) in uniqueTags" :key="tag" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="flex justify-between items-center mb-2">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-2" :class="getTagBgColor(index)"></div>
                  <h5 class="font-medium" :class="getTagTextColor(index)">{{ tag }}</h5>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getTagBadgeClasses(index)">
                  {{ selectedStudent.tags[tag] || 0 }}
                </span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full" 
                    :class="getTagBgColor(index)"
                    :style="{ width: `${selectedStudent.tags[tag] ? (selectedStudent.tags[tag] / maxTagValue(tag) * 100) : 0}%` }">
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>0</span>
                <span>Màxim: {{ maxTagValue(tag) }}</span>
              </div>
            </div>
          </div>

          <!-- Recomendaciones personalizadas -->
          <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h4 class="font-medium text-blue-800 mb-3 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Recomanacions Personalitzades
            </h4>
            <ul class="list-disc pl-5 text-blue-700 space-y-2">
              <li v-if="selectedStudent.tags['Agressiu'] && selectedStudent.tags['Agressiu'] >= alertThreshold">
                Aquest alumne ha estat identificat com a potencial agressor per {{ selectedStudent.tags['Agressiu'] }} companys. 
                Es recomana fer un seguiment més proper i implementar estratègies de gestió del comportament.
              </li>
              <li v-if="selectedStudent.tags['Víctima'] && selectedStudent.tags['Víctima'] >= alertThreshold">
                Aquest alumne ha estat identificat com a víctima potencial per {{ selectedStudent.tags['Víctima'] }} companys. 
                És important prestar-li atenció i oferir suport per prevenir situacions d'assetjament.
              </li>
              <li v-if="selectedStudent.tags['Rebutjat'] && selectedStudent.tags['Rebutjat'] >= alertThreshold">
                L'alumne presenta un índex alt de rebuig ({{ selectedStudent.tags['Rebutjat'] }} mencions). 
                Considereu implementar activitats d'integració i treballar en habilitats socials.
              </li>
              <li v-if="selectedStudent.tags['Popular'] && selectedStudent.tags['Popular'] >= alertThreshold">
                Aquest alumne és considerat popular per {{ selectedStudent.tags['Popular'] }} companys. 
                Pot ser un bon aliat per a dinàmiques de grup i per ajudar a la integració d'altres alumnes.
              </li>
              <li v-if="selectedStudent.tags['Prosocial'] && selectedStudent.tags['Prosocial'] >= alertThreshold">
                Aquest alumne mostra comportaments prosocials ({{ selectedStudent.tags['Prosocial'] }} mencions). 
                Es pot potenciar aquest punt fort i reconèixer la seva actitud positiva.
              </li>
              <li v-if="!hasAnyHighTag(selectedStudent)">
                Aquest alumne no presenta puntuacions destacables en cap categoria. 
                Es recomana seguir l'evolució normal i fomentar la seva participació en activitats de grup.
              </li>
            </ul>
          </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-b-xl border-t border-gray-200 flex justify-end">
          <button @click="selectedStudent = null" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
            Tancar
          </button>
        </div>
      </div>
    </div>

    <!-- Botón flotante para reabrir el popup de alerta -->
    <button 
      v-if="!showRiskAlert && (topAggressiveStudents.length > 0 || topVictimStudents.length > 0 || topRejectedStudents.length > 0)"
      @click="showRiskAlert = true" 
      class="fixed bottom-6 right-6 bg-gradient-to-r from-red-600 to-yellow-500 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all hover:scale-105 z-40 flex items-center"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <span class="font-medium">Alumnes en Risc</span>
    </button>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import { useCoursesStore } from "~/stores/coursesStore";
import { useResultatCescStore } from "~/stores/resultatsCescStore";
import { useStudentsStore } from "~/stores/studentsStore";
import { useRoute } from "vue-router";
import DashboardNavOrientador from "~/components/Orientador/DashboardNavOrientador.vue";
import RiskAlertPopup from "~/components/CESC/RiskAlertPopup.vue";
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart, PieChart, RadarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, LegendComponent, ToolboxComponent } from 'echarts/components';

// Registrar componentes de ECharts
use([CanvasRenderer, BarChart, PieChart, RadarChart, GridComponent, TooltipComponent, LegendComponent, ToolboxComponent]);

// Mapeo de colores para cada etiqueta
const tagColorMap = {
  'Popular': {
    primary: '#22c55e',
    light: '#dcfce7',
    dark: '#166534',
    bg: 'bg-green-500',
    bgLight: 'bg-green-100',
    text: 'text-green-800'
  },
  'Rebutjat': {
    primary: '#3b82f6',
    light: '#dbeafe',
    dark: '#1e40af',
    bg: 'bg-blue-500',
    bgLight: 'bg-blue-100',
    text: 'text-blue-800'
  },
  'Agressiu': {
    primary: '#dc2626',
    light: '#fee2e2',
    dark: '#991b1b',
    bg: 'bg-red-500',
    bgLight: 'bg-red-100',
    text: 'text-red-800'
  },
  'Prosocial': {
    primary: '#8b5cf6',
    light: '#ede9fe',
    dark: '#5b21b6',
    bg: 'bg-purple-500',
    bgLight: 'bg-purple-100',
    text: 'text-purple-800'
  },
  'Víctima': {
    primary: '#f59e0b',
    light: '#fef3c7',
    dark: '#b45309',
    bg: 'bg-yellow-500',
    bgLight: 'bg-yellow-100',
    text: 'text-yellow-800'
  }
};

// Paleta de colores para los gráficos
const getTagColor = (tag) => tagColorMap[tag]?.primary || '#64748b';
const getTagLightColor = (tag) => tagColorMap[tag]?.light || '#f1f5f9';
const getTagDarkColor = (tag) => tagColorMap[tag]?.dark || '#475569';
const getTagBgColor = (index) => {
  const tag = uniqueTags.value[index];
  return tagColorMap[tag]?.bg || 'bg-gray-500';
};
const getTagTextColor = (index) => {
  const tag = uniqueTags.value[index];
  return tagColorMap[tag]?.text || 'text-gray-800';
};

// Variables de estado
const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);
const coursesStore = useCoursesStore();
const studentsStore = useStudentsStore();
const resultatsCescStore = useResultatCescStore();

// Estado para controlar visualizaciones
const chartType = ref('stacked'); // 'stacked', 'grouped', 'percentage', 'radar'
const selectedStudent = ref(null);
const highlightedStudent = ref(null);
const selectedTags = ref(['Popular', 'Rebutjat', 'Agressiu', 'Prosocial', 'Víctima']);
const searchQuery = ref('');
const alertThreshold = ref(3); // Umbral inicial más bajo para mostrar más alertas
const selectedProfile = ref('Agressiu');
const sortColumn = ref('fullName');
const sortDirection = ref('asc');
const showAlertModal = ref(false); // Control del modal de alerta
const showRiskAlert = ref(false); // Control para el nuevo popup dinámico

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

// Lista completa de posibles tags
const allPossibleTags = ['Popular', 'Rebutjat', 'Agressiu', 'Prosocial', 'Víctima'];

// Inicializar datos
classId.value = route.params.classId;

// Función para ordenar resultados
const sortBy = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortColumn.value = column;
    sortDirection.value = 'desc';
  }
};

// Función para filtrar y ordenar estudiantes
const sortedAndFilteredStudents = computed(() => {
  let result = [...groupedResults.value];
  
  // Filtrar por búsqueda
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(student => 
      student.fullName.toLowerCase().includes(query)
    );
  }
  
  // Ordenar
  result.sort((a, b) => {
    let valueA, valueB;
    
    if (sortColumn.value === 'fullName') {
      valueA = a.fullName;
      valueB = b.fullName;
    } else if (sortColumn.value === 'total') {
      valueA = studentTotal(a);
      valueB = studentTotal(b);
    } else {
      valueA = a.tags[sortColumn.value] || 0;
      valueB = b.tags[sortColumn.value] || 0;
    }
    
    // Comparación para ordenar
    if (typeof valueA === 'string') {
      return sortDirection.value === 'asc' 
        ? valueA.localeCompare(valueB) 
        : valueB.localeCompare(valueA);
    } else {
      return sortDirection.value === 'asc' 
        ? valueA - valueB 
        : valueB - valueA;
    }
  });
  
  return result;
});

// Función para calcular el total de tags de un estudiante
const studentTotal = (student) => {
  return Object.values(student.tags).reduce((sum, val) => sum + val, 0);
};

// Function to check if a student should be highlighted
const isHighlightedStudent = (student, type = null) => {
  if (highlightedStudent.value) {
    return student.fullName === highlightedStudent.value;
  }
  
  return isHighlightedRowByRank(student, type);
};

// Función para obtener el tag más alto de un estudiante
const highestTag = (student) => {
  let maxTag = '';
  let maxValue = 0;
  
  Object.entries(student.tags).forEach(([tag, value]) => {
    if (value > maxValue) {
      maxValue = value;
      maxTag = tag;
    }
  });
  
  return maxTag;
};

// Función para obtener el valor del tag más alto
const highestTagValue = (student) => {
  return student.tags[highestTag(student)] || 0;
};

// Función para calcular el percentil del estudiante en el grupo
const studentPercentile = (student) => {
  const total = studentTotal(student);
  const allTotals = groupedResults.value.map(studentTotal);
  const position = allTotals.filter(t => t <= total).length;
  return Math.round((position / allTotals.length) * 100);
};

// Función para obtener la clase de color según el percentil
const getPercentileClass = (percentile) => {
  if (percentile >= 80) return 'bg-green-100 text-green-800';
  if (percentile >= 60) return 'bg-blue-100 text-blue-800';
  if (percentile >= 40) return 'bg-gray-100 text-gray-800';
  if (percentile >= 20) return 'bg-yellow-100 text-yellow-800';
  return 'bg-red-100 text-red-800';
};

// Valor máximo para un tag específico
const maxTagValue = (tag) => {
  return Math.max(...groupedResults.value.map(student => student.tags[tag] || 0));
};

// Verificar si un estudiante tiene algún tag por encima del umbral
const hasAnyHighTag = (student) => {
  return Object.entries(student.tags).some(([tag, value]) => value >= alertThreshold.value);
};

// Funciones para identificar estudiantes destacados
const studentsWithTag = (tag) => {
  return groupedResults.value.filter(student => student.tags[tag] && student.tags[tag] > 0);
};

const topStudentsByTag = (tag, limit = 3) => {
  return [...studentsWithTag(tag)]
    .sort((a, b) => (b.tags[tag] || 0) - (a.tags[tag] || 0))
    .slice(0, limit);
};

// Obtener los 2 estudiantes con mayor puntuación en cada categoría
const topAggressiveStudents = computed(() => {
  return [...groupedResults.value]
    .filter(s => s.tags['Agressiu'] && s.tags['Agressiu'] > 0)
    .sort((a, b) => (b.tags['Agressiu'] || 0) - (a.tags['Agressiu'] || 0))
    .slice(0, 2);
});

const topVictimStudents = computed(() => {
  return [...groupedResults.value]
    .filter(s => s.tags['Víctima'] && s.tags['Víctima'] > 0)
    .sort((a, b) => (b.tags['Víctima'] || 0) - (a.tags['Víctima'] || 0))
    .slice(0, 2);
});

const topRejectedStudents = computed(() => {
  return [...groupedResults.value]
    .filter(s => s.tags['Rebutjat'] && s.tags['Rebutjat'] > 0)
    .sort((a, b) => (b.tags['Rebutjat'] || 0) - (a.tags['Rebutjat'] || 0))
    .slice(0, 2);
});

// Función para ver los detalles de un estudiante
const viewStudent = (student) => {
  selectedStudent.value = student;
  showRiskAlert.value = false; // Cerrar el popup de riesgo al abrir el modal de estudiante
};

// Función para crear un reporte PDF (placeholder - se puede implementar completamente después)
const createPdfReport = () => {
  alert('Funcionalidad de descarga de informe en desarrollo');
};

// Función para identificar los 2 estudiantes con mayor puntuación en cada categoría
const isHighlightedRowByRank = (student, type = null) => {
  if (!student.tags) return false;
  
  if (type === 'Agressiu') {
    return topAggressiveStudents.value.some(s => s.fullName === student.fullName);
  } else if (type === 'Víctima') {
    return topVictimStudents.value.some(s => s.fullName === student.fullName);
  } else if (type === 'Rebutjat') {
    return topRejectedStudents.value.some(s => s.fullName === student.fullName);
  }
  
  // Si no se especifica tipo, verificar si está en alguna de las listas
  return topAggressiveStudents.value.some(s => s.fullName === student.fullName) || 
         topVictimStudents.value.some(s => s.fullName === student.fullName) || 
         topRejectedStudents.value.some(s => s.fullName === student.fullName);
};

// Manejar clic en el gráfico
const handleChartClick = (params) => {
  if (params.componentType === 'series' && params.seriesType === 'bar') {
    const dataIndex = params.dataIndex;
    selectedStudent.value = groupedResults.value[dataIndex];
  }
};

// Opciones del gráfico principal
const chartOption = computed(() => {
  // Filtrar estudiantes para el gráfico basado en el estudiante destacado
  const filteredStudents = highlightedStudent.value 
    ? groupedResults.value.filter(s => s.fullName === highlightedStudent.value)
    : groupedResults.value;

  // Extraer nombres de estudiantes para el eje X
  const categories = filteredStudents.map(student => student.fullName);

  // Configuración específica para el tipo de gráfico
  if (chartType.value === 'radar') {
    // Configuración para gráfico de radar
    const indicators = selectedTags.value.map(tag => ({
      name: tag,
      max: Math.max(...filteredStudents.map(student => student.tags[tag] || 0)) + 1
    }));

    const seriesData = filteredStudents.map(student => ({
      value: selectedTags.value.map(tag => student.tags[tag] || 0),
      name: student.fullName
    }));

    return {
      tooltip: {
        trigger: 'item'
      },
      legend: {
        data: categories,
        bottom: 10
      },
      radar: {
        indicator: indicators,
        shape: 'circle',
        splitNumber: 5,
        axisName: {
          color: '#666',
          fontSize: 12
        }
      },
      series: [{
        type: 'radar',
        data: seriesData,
        symbol: 'circle',
        symbolSize: 6,
        itemStyle: {
          borderWidth: 2
        },
        emphasis: {
          lineStyle: {
            width: 4
          }
        }
      }]
    };
  } else {
    // Series para gráficos de barras (stacked, grouped, percentage)
    const series = selectedTags.value.map(tag => {
      return {
        name: tag,
        type: 'bar',
        stack: chartType.value === 'grouped' ? undefined : 'total',
        emphasis: {
          focus: 'series'
        },
        itemStyle: {
          color: getTagColor(tag)
        },
        data: filteredStudents.map(student => {
          const value = student.tags[tag] || 0;
          if (chartType.value === 'percentage') {
            const total = studentTotal(student);
            return total > 0 ? (value / total) * 100 : 0;
          }
          return value;
        })
      };
    });

    return {
      tooltip: {
        trigger: 'axis',
        axisPointer: { type: 'shadow' },
        formatter: function(params) {
          const dataIndex = params[0].dataIndex;
          const student = filteredStudents[dataIndex];
          let html = `<div style="font-weight:bold;margin-bottom:5px;">${student.fullName}</div>`;

          params.forEach(param => {
            const color = param.color;
            const seriesName = param.seriesName;
            const value = param.value;
            
            if (chartType.value === 'percentage') {
              html += `<div style="display:flex;align-items:center;margin:5px 0;">
                        <span style="display:inline-block;width:10px;height:10px;background:${color};border-radius:50%;margin-right:5px;"></span>
                        <span>${seriesName}: ${value.toFixed(1)}%</span>
                      </div>`;
            } else {
              const total = studentTotal(student);
              const percentage = total > 0 ? ((student.tags[seriesName] || 0) / total * 100).toFixed(1) : 0;
              html += `<div style="display:flex;align-items:center;margin:5px 0;">
                        <span style="display:inline-block;width:10px;height:10px;background:${color};border-radius:50%;margin-right:5px;"></span>
                        <span>${seriesName}: ${student.tags[seriesName] || 0} (${percentage}%)</span>
                      </div>`;
            }
          });

          return html;
        }
      },
      legend: {
        data: selectedTags.value,
        bottom: 10
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '15%',
        top: '3%',
        containLabel: true
      },
      toolbox: {
        feature: {
          saveAsImage: {}
        }
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
        name: chartType.value === 'percentage' ? 'Percentatge (%)' : 'Puntuació',
        nameLocation: 'middle',
        nameGap: 40,
        axisLabel: {
          formatter: chartType.value === 'percentage' ? '{value}%' : '{value}'
        },
        max: chartType.value === 'percentage' ? 100 : null
      },
      series: series
    };
  }
});

// Opciones del gráfico de radar para perfiles destacados - Mejorado
const radarChartOption = computed(() => {
  // Obtener los estudiantes con la característica seleccionada (forzamos que siempre tenga al menos 1 resultado)
  let profileStudents = topStudentsByTag(selectedProfile.value, 3);
  
  // Si no hay estudiantes con este perfil, mostramos un mensaje alternativo
  if (profileStudents.length === 0) {
    // Devolvemos una configuración básica con mensaje de "sin datos"
    return {
      title: {
        text: 'No hi ha dades disponibles per aquest perfil',
        left: 'center',
        top: 'middle',
        textStyle: {
          fontSize: 16,
          color: '#666'
        }
      },
      graphic: {
        elements: [{
          type: 'text',
          style: {
            text: 'Selecciona un perfil diferent o verifica que hi hagi alumnes amb aquest tag.',
            fill: '#999',
            fontSize: 14
          },
          position: ['50%', '60%'],
          origin: [0, 0],
          textAlign: 'center'
        }]
      }
    };
  }
  
  // Asignar colores basados en el perfil seleccionado
  const profileColors = {
    'Agressiu': ['#dc2626', '#b91c1c', '#7f1d1d'],
    'Víctima': ['#f59e0b', '#d97706', '#b45309'],
    'Popular': ['#22c55e', '#16a34a', '#15803d']
  };
  
  const colors = profileColors[selectedProfile.value] || ['#0080C0', '#22c55e', '#8b5cf6'];
  
  // Configuración del radar con mejor visibilidad
  const indicators = allPossibleTags.map(tag => {
    // Encontrar el valor máximo para esta etiqueta en toda la clase
    const maxValue = Math.max(...groupedResults.value.map(student => student.tags[tag] || 0));
    // Usar un valor mínimo de 3 para evitar escalas vacías
    return {
      name: tag,
      max: Math.max(maxValue, 3) + 2,
      color: '#0080C0'
    };
  });

  // Preparar los datos de la serie con colores diferentes por estudiante
  const seriesData = profileStudents.map((student, index) => ({
    value: allPossibleTags.map(tag => student.tags[tag] || 0),
    name: student.fullName,
    // Asignar un color diferente a cada estudiante
    itemStyle: {
      color: colors[index % colors.length],
      borderColor: colors[index % colors.length],
      borderWidth: 2
    },
    lineStyle: {
      width: 3,
      color: colors[index % colors.length],
      opacity: 0.9
    },
    areaStyle: {
      opacity: 0.15,
      color: colors[index % colors.length]
    },
    symbol: 'circle',
    symbolSize: 8,
    label: {
      show: true,
      position: 'outside',
      fontSize: 12,
      color: '#333'
    }
  }));

  return {
    tooltip: {
      trigger: 'item',
      formatter: function(params) {
        if (params.componentType !== 'series') return '';
        
        const student = params.name;
        const data = params.value;
        let html = `<div style="font-weight:bold;margin-bottom:8px;font-size:15px;">${student} (${selectedProfile.value})</div>`;
        
        allPossibleTags.forEach((tag, index) => {
          const value = data[index];
          const color = tagColorMap[tag]?.primary || '#64748b';
          const percentage = Math.round((value / indicators[index].max) * 100);
          
          html += `<div style="display:flex;align-items:center;margin:8px 0;justify-content:space-between;">
                    <div style="display:flex;align-items:center;">
                      <span style="display:inline-block;width:12px;height:12px;background:${color};border-radius:50%;margin-right:8px;"></span>
                      <span>${tag}:</span>
                    </div>
                    <div>
                      <span style="font-weight:bold;">${value}</span>
                      <span style="color:#999;font-size:11px;margin-left:5px;">(${percentage}%)</span>
                    </div>
                  </div>`;
        });
        
        return html;
      },
      backgroundColor: 'rgba(255, 255, 255, 0.98)',
      borderColor: '#e2e8f0',
      borderWidth: 1,
      padding: 15,
      textStyle: {
        color: '#333'
      }
    },
    legend: {
      data: profileStudents.map(s => s.fullName),
      bottom: 10,
      icon: 'circle',
      type: 'scroll',
      itemGap: 20,
      textStyle: {
        fontSize: 13,
        color: '#333'
      }
    },
    radar: {
      indicator: indicators,
      shape: 'polygon',
      splitNumber: 5,
      center: ['50%', '50%'],
      radius: '70%',
      axisName: {
        color: '#0080C0',
        fontSize: 13,
        fontWeight: 'bold'
      },
      splitLine: {
        lineStyle: {
          color: '#e2e8f0',
          width: 1
        }
      },
      splitArea: {
        show: false
      },
      axisLine: {
        lineStyle: {
          color: '#0080C0',
          width: 2
        }
      }
    },
    series: [{
      type: 'radar',
      data: seriesData,
      emphasis: {
        lineStyle: {
          width: 5
        }
      }
    }]
  };
});

// Opciones del gráfico de radar para un estudiante individual
const studentRadarOption = computed(() => {
  if (!selectedStudent.value) return {};
  
  // Configuración del radar
  const indicators = allPossibleTags.map(tag => ({
    name: tag,
    max: maxTagValue(tag) + 1
  }));

  // Datos del estudiante
  const studentData = {
    value: allPossibleTags.map(tag => selectedStudent.value.tags[tag] || 0),
    name: selectedStudent.value.fullName,
    areaStyle: {
      color: 'rgba(0, 128, 192, 0.3)'
    },
    lineStyle: {
      color: '#0080C0'
    }
  };

  // Datos promedio del grupo
  const avgData = {
    value: allPossibleTags.map(tag => {
      const sum = groupedResults.value.reduce((acc, student) => acc + (student.tags[tag] || 0), 0);
      return sum / groupedResults.value.length;
    }),
    name: 'Promedio del grupo',
    lineStyle: {
      color: '#64748b'
    },
    areaStyle: {
      color: 'rgba(100, 116, 139, 0.2)'
    }
  };

  return {
    tooltip: {
      trigger: 'item'
    },
    legend: {
      data: [selectedStudent.value.fullName, 'Promedio del grupo'],
      bottom: 0
    },
    radar: {
      indicator: indicators,
      shape: 'circle',
      splitNumber: 4,
      axisName: {
        color: '#666',
        fontSize: 11
      },
      splitArea: {
        areaStyle: {
          color: ['#f9fafb', '#f3f4f6', '#e5e7eb', '#d1d5db'],
          shadowColor: 'rgba(0, 0, 0, 0.2)',
          shadowBlur: 10
        }
      }
    },
    series: [{
      type: 'radar',
      data: [studentData, avgData],
      symbol: 'circle',
      symbolSize: 6
    }]
  };
});

// Opciones del gráfico de pie para la distribución de roles
const pieChartOption = computed(() => {
  // Datos para el gráfico
  const data = allPossibleTags.map(tag => {
    const count = studentsWithTag(tag).length;
    return {
      name: tag,
      value: count,
      itemStyle: {
        color: getTagColor(tag)
      }
    };
  });

  return {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} alumnes ({d}%)'
    },
    legend: {
      orient: 'horizontal',
      bottom: 0,
      data: allPossibleTags
    },
    series: [
      {
        name: 'Distribució',
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 10,
          borderColor: '#fff',
          borderWidth: 2
        },
        label: {
          show: false,
          position: 'center'
        },
        emphasis: {
          label: {
            show: true,
            fontSize: '14',
            fontWeight: 'bold'
          }
        },
        labelLine: {
          show: false
        },
        data: data
      }
    ]
  };
});

// Filtrar datos en base al curso y la división
const filtered = computed(() => {
  if (!course.value) return [];

  return resultatsCescStore.getCescByCourseAndDivision(
    course.value.courseName,
    course.value.division.name
  );
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
    
    // Mostrar el popup de riesgo automáticamente después de un breve retraso
    setTimeout(() => {
      // Solo mostrar si hay algún estudiante en riesgo
      if (topAggressiveStudents.value.length > 0 || 
          topVictimStudents.value.length > 0 || 
          topRejectedStudents.value.length > 0) {
        showRiskAlert.value = true;
      }
    }, 1000); // Retraso de 1 segundo para permitir que la página se cargue primero
  } catch (err) {
    console.error("Error en carregar les dades:", err);
    error.value = "Error en carregar les dades";
  } finally {
    isLoading.value = false;
  }
});
</script>

<style>
/* Estilos para la página CESC Profile */
.cesc-results-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem;
}

.cesc-header, .cesc-visualization, .cesc-table-container, .cesc-results-card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  margin-bottom: 1.5rem;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
}

/* Nuevas animaciones para el componente */
@keyframes flash {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes pulseScale {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.flash-animation {
  animation: flash 2s infinite;
}

.pulse-scale {
  animation: pulseScale 2s infinite;
}
</style>
