<template>
  <div>
    <!-- Añadido el componente DashboardNavTeacher -->
    <DashboardNavTeacher />

    <div class="min-h-screen bg-gray-100 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Encabezado de la página -->
        <div class="bg-[#00ADEC] text-white rounded-t-lg p-6 mb-0 shadow-lg">
          <h1 class="text-3xl font-bold">Estat dels Formularis</h1>
          <p class="mt-2 text-white/80">Consulta quins alumnes han respost i quins encara han de completar els formularis</p>
        </div>
        
        <!-- Sección de filtros -->
        <div class="bg-white rounded-b-lg shadow-lg p-6 mb-8 border-t-4 border-[#00ADEC]">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Selecciona un formulari</h2>
          
          <!-- Filtros en grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Selector de curso -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Curs</label>
              <div class="relative">
                <select 
                  v-model="selectedCourse" 
                  class="block w-full border border-gray-300 rounded-lg py-3 px-4 pr-10 bg-white focus:outline-none focus:ring-2 focus:ring-[#00ADEC] focus:border-[#00ADEC] transition-all"
                >
                  <option value="">Selecciona un curs</option>
                  <option v-for="course in courses" :key="course.id" :value="course.id">
                    {{ course.name }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- Selector de división -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Divisió</label>
              <div class="relative">
                <select 
                  v-model="selectedDivision" 
                  class="block w-full border border-gray-300 rounded-lg py-3 px-4 pr-10 bg-white focus:outline-none focus:ring-2 focus:ring-[#00ADEC] focus:border-[#00ADEC] transition-all"
                  :disabled="!selectedCourse"
                  :class="{'opacity-50 cursor-not-allowed': !selectedCourse}"
                >
                  <option value="">Selecciona una divisió</option>
                  <option v-for="division in divisions" :key="division.id" :value="division.id">
                    {{ division.division }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- Selector de formulario -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Formulari</label>
              <div class="relative">
                <select 
                  v-model="selectedForm" 
                  class="block w-full border border-gray-300 rounded-lg py-3 px-4 pr-10 bg-white focus:outline-none focus:ring-2 focus:ring-[#00ADEC] focus:border-[#00ADEC] transition-all"
                  :disabled="!selectedDivision"
                  :class="{'opacity-50 cursor-not-allowed': !selectedDivision}"
                >
                  <option value="">Selecciona un formulari</option>
                  <option v-for="form in forms" :key="form.id" :value="form.id">
                    {{ form.title }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Botón para buscar -->
          <button 
            @click="getResponseStatus" 
            class="flex items-center justify-center w-full md:w-auto bg-[#00ADEC] text-white py-3 px-6 rounded-lg hover:bg-[#0096cf] transition-colors font-medium"
            :disabled="!selectedForm || !selectedCourse || !selectedDivision"
            :class="{'opacity-50 cursor-not-allowed': !selectedForm || !selectedCourse || !selectedDivision}"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            Consultar Estat
          </button>
        </div>
        
        <!-- Mensaje de carga -->
        <div v-if="isLoading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#00ADEC]"></div>
          <span class="ml-3 text-lg text-gray-600">Carregant...</span>
        </div>
        
        <!-- Resultados -->
        <div v-if="responseStatus && !isLoading" class="bg-white rounded-lg shadow-lg overflow-hidden">
          <!-- Cabecera de resultados con estadísticas -->
          <div class="bg-gray-50 p-6 border-b">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
              <h2 class="text-xl font-semibold text-gray-800">Resum de Respostes</h2>
              
              <div class="flex flex-wrap gap-4">
                <div class="bg-white shadow-sm rounded-lg px-4 py-3 flex items-center">
                  <div class="rounded-full bg-gray-200 p-2 mr-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-xs font-medium text-gray-500">Total Alumnes</div>
                    <div class="text-xl font-bold text-gray-700">{{ responseStatus.total_students }}</div>
                  </div>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg px-4 py-3 flex items-center">
                  <div class="rounded-full bg-green-100 p-2 mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-xs font-medium text-gray-500">Completats</div>
                    <div class="text-xl font-bold text-green-600">{{ responseStatus.answered_count }}</div>
                  </div>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg px-4 py-3 flex items-center">
                  <div class="rounded-full bg-red-100 p-2 mr-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-xs font-medium text-gray-500">Pendents</div>
                    <div class="text-xl font-bold text-red-600">{{ responseStatus.pending_count }}</div>
                  </div>
                </div>
                
                <div class="bg-white shadow-sm rounded-lg px-4 py-3 flex items-center">
                  <div class="rounded-full bg-blue-100 p-2 mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-xs font-medium text-gray-500">Percentatge</div>
                    <div class="text-xl font-bold text-blue-600">
                      {{ Math.round((responseStatus.answered_count / responseStatus.total_students) * 100) || 0 }}%
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Barra de progreso -->
            <div class="mt-6 bg-gray-200 rounded-full h-4 overflow-hidden">
              <div 
                class="bg-[#00ADEC] h-4 rounded-full transition-all duration-500" 
                :style="{width: `${Math.round((responseStatus.answered_count / responseStatus.total_students) * 100) || 0}%`}"
              ></div>
            </div>
          </div>
          
          <!-- Filtro de búsqueda y descarga -->
          <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4 border-b">
            <div class="w-full md:w-1/3 relative">
              <input 
                v-model="searchTerm"
                type="text" 
                placeholder="Cerca per nom o cognom..." 
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00ADEC] focus:border-[#00ADEC]"
              />
              <div class="absolute left-0 top-0 ml-3 h-full flex items-center">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
            
            <div class="flex space-x-3">
              <button 
                @click="exportToCSV" 
                class="flex items-center justify-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Exportar CSV
              </button>
              
              <button 
                @click="printList" 
                class="flex items-center justify-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir
              </button>
            </div>
          </div>
          
          <!-- Tabla de estudiantes -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cognoms</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estat</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="filteredStudents.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                      <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <p>No s'han trobat estudiants que coincideixin amb la cerca.</p>
                    </div>
                  </td>
                </tr>
                <tr v-for="student in filteredStudents" :key="student.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">{{ student.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ student.last_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ student.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="student.has_answered" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                      Completat
                    </span>
                    <span v-else class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Pendent
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button 
                      @click="sendReminder(student)" 
                      class="text-blue-600 hover:text-blue-900 font-medium text-sm"
                      :disabled="student.has_answered"
                      :class="{'opacity-50 cursor-not-allowed': student.has_answered}"
                    >
                      <span v-if="!student.has_answered">Enviar recordatori</span>
                      <span v-else>No necessari</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Paginación -->
          <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Mostrant
                  <span class="font-medium">{{ (currentPage - 1) * pageSize + 1 }}</span>
                  a
                  <span class="font-medium">{{ Math.min(currentPage * pageSize, filteredStudents.length) }}</span>
                  de
                  <span class="font-medium">{{ filteredStudents.length }}</span>
                  alumnes
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="currentPage = Math.max(1, currentPage - 1)"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :disabled="currentPage === 1"
                    :class="{'opacity-50 cursor-not-allowed': currentPage === 1}"
                  >
                    <span class="sr-only">Anterior</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button
                    v-for="page in totalPages"
                    :key="page"
                    @click="currentPage = page"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                      currentPage === page
                        ? 'z-10 bg-[#00ADEC] border-[#00ADEC] text-white'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <button
                    @click="currentPage = Math.min(totalPages, currentPage + 1)"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :disabled="currentPage === totalPages"
                    :class="{'opacity-50 cursor-not-allowed': currentPage === totalPages}"
                  >
                    <span class="sr-only">Següent</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Estado vacío - cuando no hay datos seleccionados -->
        <div 
          v-if="!responseStatus && !isLoading" 
          class="bg-white rounded-lg shadow-lg p-12 text-center"
        >
          <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
          </svg>
          <h3 class="text-xl font-medium text-gray-600 mb-2">No hi ha dades per mostrar</h3>
          <p class="text-gray-500 mb-6">Selecciona un curs, una divisió i un formulari per veure l'estat de les respostes</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useAuthStore } from '~/stores/authStore';
import DashboardNavTeacher from '~/components/Teacher/DashboardNavTeacher.vue';

const authStore = useAuthStore();
const router = useRouter();

// Variables para filtros
const selectedCourse = ref('');
const selectedDivision = ref('');
const selectedForm = ref('');

// Datos
const courses = ref([]);
const divisions = ref([]);
const forms = ref([]);
const responseStatus = ref(null);
const isLoading = ref(false);

// Variables para búsqueda y paginación
const searchTerm = ref('');
const currentPage = ref(1);
const pageSize = ref(10);

// Filtrar estudiantes por término de búsqueda
const filteredStudents = computed(() => {
  if (!responseStatus.value || !responseStatus.value.students) return [];
  
  let students = responseStatus.value.students;
  
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    students = students.filter(student => 
      student.name.toLowerCase().includes(term) || 
      student.last_name.toLowerCase().includes(term) || 
      student.email.toLowerCase().includes(term)
    );
  }
  
  return students;
});

// Calcular el número total de páginas
const totalPages = computed(() => {
  return Math.ceil(filteredStudents.value.length / pageSize.value);
});

// Paginación de estudiantes
const paginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredStudents.value.slice(start, end);
});

// Resetear la página al cambiar el término de búsqueda
watch(searchTerm, () => {
  currentPage.value = 1;
});

// Verificar permisos
onMounted(async () => {
  // Solo los profesores y tutores pueden acceder a esta página
  if (!authStore.isProfesor && !authStore.isTutor && !authStore.isAdmin) {
    router.push('/login');
    return;
  }
  
  // Cargar cursos
  await loadCourses();
});

// Cargar cursos
async function loadCourses() {
  try {
    const response = await $fetch('https://api.grupify.cat/api/courses', {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    });
    courses.value = response;
  } catch (error) {
    console.error('Error al cargar cursos:', error);
  }
}

// Cargar divisiones cuando se selecciona un curso
watch(selectedCourse, async (newValue) => {
  if (newValue) {
    try {
      const response = await $fetch(`https://api.grupify.cat/api/course-divisions?course_id=${newValue}`, {
        headers: {
          Authorization: `Bearer ${authStore.token}`
        }
      });
      divisions.value = response;
    } catch (error) {
      console.error('Error al cargar divisions:', error);
    }
  } else {
    divisions.value = [];
  }
  selectedDivision.value = '';
  selectedForm.value = '';
});

// Cargar formularios cuando se selecciona una división
watch(selectedDivision, async (newValue) => {
  if (newValue && selectedCourse.value) {
    try {
      const response = await $fetch('https://api.grupify.cat/api/forms/active', {
        headers: {
          Authorization: `Bearer ${authStore.token}`
        }
      });
      forms.value = response;
    } catch (error) {
      console.error('Error al cargar formularis:', error);
    }
  } else {
    forms.value = [];
  }
  selectedForm.value = '';
});

// Obtener estado de respuestas
async function getResponseStatus() {
  if (!selectedForm.value || !selectedCourse.value || !selectedDivision.value) return;
  
  isLoading.value = true;
  
  try {
    const response = await $fetch('https://api.grupify.cat/api/forms/response-status', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        form_id: selectedForm.value,
        course_id: selectedCourse.value,
        division_id: selectedDivision.value
      })
    });
    
    responseStatus.value = response;
    currentPage.value = 1; // Resetear a la primera página
  } catch (error) {
    console.error('Error al obtenir l\'estat de respostes:', error);
  } finally {
    isLoading.value = false;
  }
}

// Enviar recordatorio a un estudiante
function sendReminder(student) {
  // Aquí implementarías la lógica para enviar un recordatorio
  alert(`S'enviarà un recordatori a ${student.name} ${student.last_name} (${student.email})`);
}

// Exportar a CSV
function exportToCSV() {
  if (!responseStatus.value || !responseStatus.value.students) return;
  
  // Crear el contenido del CSV
  let csvContent = "data:text/csv;charset=utf-8,";
  
  // Cabecera
  csvContent += "Nom,Cognoms,Email,Estat\n";
  
  // Datos
  responseStatus.value.students.forEach(student => {
    const estado = student.has_answered ? "Completat" : "Pendent";
    csvContent += `${student.name},${student.last_name},${student.email},${estado}\n`;
  });
  
  // Crear el enlace de descarga
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "estat_formulari.csv");
  document.body.appendChild(link);
  
  // Descargar
  link.click();
  document.body.removeChild(link);
}

// Imprimir la lista
function printList() {
  window.print();
}
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }
  
  .min-h-screen {
    min-height: auto !important;
  }
  
  table, table * {
    visibility: visible;
  }
  
  table {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>