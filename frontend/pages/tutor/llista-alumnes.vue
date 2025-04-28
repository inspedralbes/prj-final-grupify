<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "~/stores/authStore";
import { useRouter } from "vue-router";

const studentsStore = useStudentsStore();
const authStore = useAuthStore();
const router = useRouter();
const { $socket } = useNuxtApp();
const isLoading = ref(true);

// VARIABLES REACTIVES PER FILTRAR ELS ESTUDIANTS
const students = computed(() => studentsStore.students || []);
const { searchQuery, selectedCourse, selectedDivision, filteredStudents } =
  useStudentSearch(students);

// Variables para mostrar la información del curso del tutor
const tutorAssignment = ref(null);

// >>> VARIABLES PER LA PAGINACIÓ <<<
const currentPage = ref(1);
const itemsPerPage = ref(20); // Ajusta aquí la quantitat d'estudiants per pàgina

const totalPages = computed(() =>
  Math.ceil(filteredStudents.value.length / itemsPerPage.value)
);

const paginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredStudents.value.slice(start, start + itemsPerPage.value);
});

// Reiniciar a página 1 cuando cambia la lista filtrada
watch(filteredStudents, () => {
  currentPage.value = 1;
});

// Configuració dels listeners del socket
const setupSocketListeners = () => {
  $socket.on('user_online', (userId) => {
    studentsStore.setUserOnline(userId);
  });
  $socket.on('user_offline', (userId) => {
    studentsStore.setUserOffline(userId);
  });
};

// Función para seleccionar un curso y división específicos (similar a la de profesor)
const selectCourseAndDivision = async (courseName, divisionName) => {
  // Actualizar estado visual primero para feedback inmediato
  selectedCourse.value = courseName;
  selectedDivision.value = divisionName;
  
  // Actualizar datos - mostrar loading state
  isLoading.value = true;
  
  try {
    // Buscar el course_id y division_id correspondientes en las asignaciones del tutor
    const assignment = authStore.user?.course_divisions?.find(
      cd => cd.course_name === courseName && cd.division_name === divisionName
    );
    
    // Si se encuentra la asignación, cargar los estudiantes de ese curso y división
    if (assignment) {
      await studentsStore.fetchStudents(true, assignment.course_id, assignment.division_id);
    }
    
    // Resetear a página 1 al cambiar de selección
    currentPage.value = 1;
  } catch (error) {
    console.error("Error al cargar estudiantes:", error);
  } finally {
    isLoading.value = false;
  }
};

// Función para cargar inicialmente los datos del tutor
const loadInitialData = async () => {
  try {
    isLoading.value = true;
    
    // Asegurarnos de que tenemos datos del usuario actualizados
    if (authStore.token && (!authStore.user || !authStore.user.role)) {
      await authStore.checkAuth();
    }
    
    // Si el tutor tiene curso asignado mediante course_divisions
    if (authStore.user?.course_divisions?.length > 0) {
      // Guardar la asignación
      tutorAssignment.value = authStore.user.course_divisions[0];
      
      // Seleccionar automáticamente el curso/división
      selectedCourse.value = tutorAssignment.value.course_name;
      selectedDivision.value = tutorAssignment.value.division_name;
      
      // Cargar los estudiantes del curso/división asignado
      await studentsStore.fetchStudents(true, tutorAssignment.value.course_id, tutorAssignment.value.division_id);
    } 
    // Plan B: Usar course_id/division_id (método original)
    else if (authStore.user?.course_id && authStore.user?.division_id) {
      // Crear un objeto de asignación compatible con la estructura esperada
      tutorAssignment.value = {
        course_id: authStore.user.course_id,
        course_name: authStore.user.course_name,
        division_id: authStore.user.division_id,
        division_name: authStore.user.division_name
      };
      
      // Seleccionar automáticamente el curso/división
      selectedCourse.value = authStore.user.course_name;
      selectedDivision.value = authStore.user.division_name;
      
      // Cargar estudiantes usando el método anterior
      await studentsStore.fetchStudents(true, authStore.user.course_id, authStore.user.division_id);
    } 
    else {
      console.error("El tutor no tiene un curso y división asignados");
    }
  } catch (error) {
    console.error("Error al cargar datos iniciales:", error);
  } finally {
    isLoading.value = false;
  }
};

// Montar el componente
onMounted(async () => {
  // Verificar que el usuario sea un tutor
  if (!authStore.isTutor) {
    router.push('/login');
    return;
  }
  
  setupSocketListeners();
  await loadInitialData();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <TeacherDashboardNavTeacher />

    <main class="container mx-auto px-4 py-6 sm:py-8">
      <!-- Títol i descripció del panell -->
      <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Gestió d'Alumnes</h1>
        <p class="mt-1 sm:mt-2 text-sm text-gray-600">
          Gestiona i supervisa els alumnes del teu curs
        </p>
      </div>
      
      <!-- Selector de cursos (con el mismo diseño que el profesor, pero solo mostrando el curso del tutor) -->
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">El teu curs</h2>
          <p class="mt-1 text-sm text-gray-600">Com a tutor, estàs assignat a aquest curs:</p>
        </div>
        <div class="p-4 sm:p-6">
          <!-- Si el tutor tiene un curso asignado -->
          <div v-if="tutorAssignment" class="grid grid-cols-1 gap-4">
            <!-- Tarjeta para el curso del tutor (usando el mismo estilo que para profesores) -->
            <div 
              @click="selectCourseAndDivision(tutorAssignment.course_name, tutorAssignment.division_name)"
              class="course-selection-card border rounded-lg p-4 cursor-pointer transition-all duration-300 flex flex-col items-center text-center bg-primary-50 border-primary-500 shadow-md"
            >
              <div class="bg-blue-100 p-4 rounded-full mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-1">{{ tutorAssignment.course_name }} {{ tutorAssignment.division_name }}</h3>
              <p class="text-sm text-gray-600">Tutor d'aquest curs</p>
              <div class="mt-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                  Seleccionat
                </span>
              </div>
            </div>
          </div>
          
          <!-- Mensaje si el tutor no tiene curso asignado -->
          <div v-else class="text-center py-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No tens cap curs assignat</h3>
            <p class="text-sm text-gray-600">Contacta amb l'administrador per assignar-te un curs</p>
          </div>
        </div>
      </div>

      <!-- Llistat d'estudiants amb controls -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow p-8 text-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Llistat d'estudiants -->
        <div v-if="selectedCourse && selectedDivision" class="bg-white rounded-lg shadow overflow-hidden">
          <div class="p-4 sm:p-6 border-b border-gray-200">
            <!-- Encabezado con información del curso seleccionado -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-lg font-medium text-gray-900">
                  Llista d'alumnes de {{ selectedCourse }} {{ selectedDivision }}
                </h2>
              </div>
              
              <div class="mt-3 sm:mt-0 flex items-center">
                <!-- Contador de estudiantes -->
                <span class="px-3 py-1.5 text-sm font-medium text-primary-800 bg-primary-100 rounded-full">
                  {{ filteredStudents.length }} estudiants
                </span>
              </div>
            </div>
            
            <!-- Campo de búsqueda de estudiantes -->
            <div class="mt-4">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Buscar alumne per nom, cognom o email..."
                  class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                />
              </div>
            </div>
          </div>
          
          <!-- Mensaje cuando no hay estudiantes -->
          <div v-if="filteredStudents.length === 0" class="p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-600">No hi ha alumnes per mostrar en aquest curs i divisió.</p>
          </div>
          
          <!-- Mostrem els estudiants paginats -->
          <TeacherStudentList 
            v-else
            :students="paginatedStudents" 
            class="divide-y divide-gray-200" 
          />

          <!-- Controls de paginació -->
          <div v-if="filteredStudents.length > 0" class="flex items-center justify-between px-4 py-3 sm:px-6">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1 text-sm text-blue-600 bg-white border border-gray-200 rounded disabled:opacity-50"
            >
              Anterior
            </button>
            <div class="hidden sm:block text-sm text-gray-600">
              Pàgina {{ currentPage }} de {{ totalPages }}
            </div>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 text-sm text-blue-600 bg-white border border-gray-200 rounded disabled:opacity-50"
            >
              Següent
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.pagination {
  display: flex;
  padding-left: 0;
  list-style: none;
  margin: 1rem 0;
  justify-content: center;
}

.page-item:not(:first-child) .page-link {
  margin-left: -1px;
}

.page-link {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 0.75rem;
  margin-left: -1px;
  line-height: 1.25;
  color: #3b82f6;
  background-color: #fff;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  min-width: 2.5rem;
  height: 2.5rem;
}

.page-item.active .page-link {
  z-index: 3;
  color: #fff;
  background-color: #3b82f6;
  border-color: #3b82f6;
}

.page-item.disabled .page-link {
  color: #9ca3af;
  pointer-events: none;
  cursor: auto;
  background-color: #fff;
  border-color: #e2e8f0;
}

.page-link:hover {
  z-index: 2;
  color: #fff;
  text-decoration: none;
  background-color: #60a5fa;
  border-color: #60a5fa;
}

.page-item:first-child .page-link {
  margin-left: 0;
  border-top-left-radius: 0.25rem;
  border-bottom-left-radius: 0.25rem;
}

.page-item:last-child .page-link {
  border-top-right-radius: 0.25rem;
  border-bottom-right-radius: 0.25rem;
}

/* Estilos para las tarjetas de selección de curso */
.course-selection-card {
  transition: all 0.3s ease;
  height: 100%;
}

.course-selection-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Colores para el tema primary */
.bg-primary-50 {
  background-color: #eef2ff;
}

.bg-primary-100 {
  background-color: #e0e7ff;
}

.text-primary-800 {
  color: #3730a3;
}

.border-primary-500 {
  border-color: #6366f1;
}

/* Estilos del botón */
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
