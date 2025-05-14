<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "~/stores/authStore";

const studentsStore = useStudentsStore();
const authStore = useAuthStore();
const { $socket } = useNuxtApp();
const isLoading = ref(true);

// VARIABLES REACTIVES PER FILTRAR ELS ESTUDIANTS
const students = computed(() => studentsStore.students || []);
const { searchQuery, selectedCourse, selectedDivision, filteredStudents } =
  useStudentSearch(students);

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

// VARIABLES PER GENERAR LA INVITACIÓ
const courses = ref([]);
const divisions = ref([]);
const selectedInvitationCourse = ref(""); // ID del curs seleccionat per l'enllaç
const selectedInvitationDivision = ref(""); // ID de la divisió seleccionada per l'enllaç
const invitationLink = ref(""); // Enllaç generat

// VARIABLE PER CONTROLAR EL MODAL
const showInvitationModal = ref(false);

// VARIABLE PER CONTROLAR EL POP-UP DE COPIA
const showCopyPopup = ref(false);
const showErrorToast = ref(false);

// Funcions per obrir i tancar el modal
const openInvitationModal = () => {
  showInvitationModal.value = true;
};
const closeInvitationModal = () => {
  showInvitationModal.value = false;
};

// Función simplificada para seleccionar un curso y división específicos
const selectCourseAndDivision = async (courseName, divisionName) => {
  // Actualizar estado visual primero para feedback inmediato
  selectedCourse.value = courseName;
  selectedDivision.value = divisionName;
  
  // Actualizar datos - mostrar loading state
  isLoading.value = true;
  
  try {
    // Buscar el course_id y division_id correspondientes en las asignaciones del profesor
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

// Función para cargar inicialmente el primer curso del profesor
const loadInitialData = async () => {
  try {
    isLoading.value = true;
    
    // Cargar los cursos para la invitación
    await fetchCourses();
    
    // Si el profesor tiene cursos asignados mediante course_divisions
    if (authStore.user?.course_divisions?.length > 0) {
      // Seleccionar el primer curso/división automáticamente
      const firstAssignment = authStore.user.course_divisions[0];
      selectedCourse.value = firstAssignment.course_name;
      selectedDivision.value = firstAssignment.division_name;
      
      // Cargar los estudiantes del curso/división seleccionado
      await studentsStore.fetchStudents(true, firstAssignment.course_id, firstAssignment.division_id);
    } 
    // Plan B: Usar course_id/division_id (método original)
    else if (authStore.user?.course_id && authStore.user?.division_id) {
      // Cargar estudiantes usando el método anterior
      await studentsStore.fetchStudents(true, authStore.user.course_id, authStore.user.division_id);
      
      // Establecer curso/división en la interfaz
      if (authStore.user?.course_name && authStore.user?.division_name) {
        selectedCourse.value = authStore.user.course_name;
        selectedDivision.value = authStore.user.division_name;
      }
    } 
    // Plan C: Si no hay información de curso/división, cargar todos los estudiantes
    else {
      await studentsStore.fetchStudents(true);
    }
  } catch (error) {
    console.error("Error al cargar datos iniciales:", error);
  } finally {
    isLoading.value = false;
  }
};

// Configuració dels listeners del socket
const setupSocketListeners = () => {
  $socket.on('user_online', (userId) => {
    studentsStore.setUserOnline(userId);
  });
  $socket.on('user_offline', (userId) => {
    studentsStore.setUserOffline(userId);
  });
};

// Funció per obtenir tots els cursos
const fetchCourses = async () => {
  try {
    const response = await fetch("https://api.grupify.cat/api/courses", {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${authStore.token}`,
      },
    });
    if (!response.ok) throw new Error("Error al carregar els cursos");
    const data = await response.json();
    courses.value = data;
  } catch (error) {
    console.error("Error al carregar els cursos:", error);
  }
};

// Funció per obtenir les divisions segons el curs seleccionat
const fetchInvitationDivisions = async () => {
  if (!selectedInvitationCourse.value) {
    divisions.value = [];
    return;
  }
  try {
    const response = await fetch(
      `https://api.grupify.cat/api/course-divisions?course_id=${selectedInvitationCourse.value}`,
      {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );
    if (!response.ok) throw new Error("Error al carregar les divisions");
    const data = await response.json();
    if (data.divisions) {
      divisions.value = data.divisions;
    } else {
      divisions.value = [];
      console.error(data.message || "No s'han trobat divisions");
    }
  } catch (error) {
    console.error("Error al carregar les divisions:", error);
  }
};

// Funció per generar l'enllaç d'invitació
const generateInvitation = async () => {
  if (!selectedInvitationCourse.value || !selectedInvitationDivision.value) {
    showErrorToast.value = true;
    setTimeout(() => {
      showErrorToast.value = false;
    }, 3000);
    return;
  }
  try {
    const response = await fetch("https://api.grupify.cat/api/invitations", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${authStore.token}`,
      },
      body: JSON.stringify({
        course_id: selectedInvitationCourse.value,
        division_id: selectedInvitationDivision.value,
      }),
    });
    if (!response.ok) throw new Error("Error al generar la invitació");
    const data = await response.json();
    invitationLink.value = data.link;
  } catch (error) {
    console.error("Error al generar la invitació:", error);
  }
};

// Funció per copiar l'enllaç al portapapeles
const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(invitationLink.value);
    showCopyPopup.value = true;
    setTimeout(() => {
      showCopyPopup.value = false;
    }, 2000);
  } catch (error) {
    console.error("Error al copiar el enlace:", error);
  }
};

// Montar el componente
onMounted(async () => {
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
          Gestiona i supervisa l'alumnat registrat a l'institut
        </p>
      </div>
      
      <!-- Selector de cursos exclusivamente asignados al profesor -->
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Els meus cursos</h2>
          <p class="mt-1 text-sm text-gray-600">Selecciona un dels teus cursos per veure els seus alumnes:</p>
        </div>
        <div class="p-4 sm:p-6">
          <!-- Si el profesor tiene cursos asignados -->
          <div v-if="authStore.user?.course_divisions?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <!-- Tarjeta para cada curso específico -->
            <div 
              v-for="(cd, index) in authStore.user.course_divisions" 
              :key="index" 
              @click="selectCourseAndDivision(cd.course_name, cd.division_name)"
              class="course-selection-card border rounded-lg p-4 cursor-pointer transition-all duration-300 flex flex-col items-center text-center"
              :class="[
                selectedCourse === cd.course_name && selectedDivision === cd.division_name 
                  ? 'bg-primary-50 border-primary-500 shadow-md' 
                  : 'bg-white hover:bg-gray-50 border-gray-200'
              ]"
            >
              <div class="bg-blue-100 p-4 rounded-full mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-1">{{ cd.course_name }} {{ cd.division_name }}</h3>
              <p class="text-sm text-gray-600">Veure alumnes d'aquest curs</p>
              <div class="mt-3" v-if="selectedCourse === cd.course_name && selectedDivision === cd.division_name">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                  Seleccionat
                </span>
              </div>
            </div>
          </div>
          
          <!-- Mensaje si el profesor no tiene cursos asignados -->
          <div v-else class="text-center py-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No tens cursos assignats</h3>
            <p class="text-sm text-gray-600">Contacta amb l'administrador per assignar-te cursos</p>
          </div>
        </div>
      </div>

      <!-- Llistat d'estudiants amb controls i icona per generar invitació -->
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
                <!-- Botón de invitación -->
                <button
                  @click="openInvitationModal"
                  class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 bg-[rgb(0,173,238)] text-white rounded-full mr-3"
                  title="Generar enllaç d'invitació"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="white" class="w-4 h-4 sm:w-5 sm:h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                  </svg>
                </button>
                
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
          
          <!-- Mensaje de carga -->
          <div v-if="isLoading" class="bg-white rounded-lg p-8 text-center">
            <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
          </div>
          
          <!-- Mostrem els estudiants paginats -->
          <TeacherStudentList :students="paginatedStudents" class="divide-y divide-gray-200" />

          <!-- Controls de paginació -->
          <div class="flex items-center justify-between px-4 py-3 sm:px-6">
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

    <!-- Modal per generar l'enllaç d'invitació -->
    <div v-if="showInvitationModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Fons semi-transparent -->
      <div class="absolute inset-0 bg-gray-900 opacity-50" @click="closeInvitationModal"></div>
      <!-- Contingut del modal -->
      <div class="bg-white rounded-lg p-4 sm:p-6 relative z-10 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg sm:text-xl font-bold">Generar enllaç d'invitació</h2>
          <button @click="closeInvitationModal" class="text-gray-500 hover:text-gray-700" title="Tanca">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Formulari per generar la invitació -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Selecciona un curs</label>
          <select v-model="selectedInvitationCourse" @change="fetchInvitationDivisions"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent">
            <option disabled value="">Selecciona un curs</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.name }}
            </option>
          </select>
        </div>

        <!-- Selector de divisió -->
        <div class="mb-4" v-if="divisions.length > 0">
          <label class="block text-sm font-medium text-gray-700 mb-1">Selecciona una divisió</label>
          <select v-model="selectedInvitationDivision"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent">
            <option disabled value="">Selecciona una divisió</option>
            <option v-for="division in divisions" :key="division.id" :value="division.id">
              {{ division.division }}
            </option>
          </select>
        </div>

        <button @click="generateInvitation"
          class="w-full px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
          Generar enllaç
        </button>

        <!-- Mostra l'enllaç generat -->
        <div v-if="invitationLink" class="mt-6 p-4 bg-gray-100 rounded-lg shadow-sm">
          <p class="text-gray-700 font-semibold mb-2">Enllaç d'invitació generat:</p>
          <div class="flex items-center bg-white border border-gray-300 rounded-md p-2">
            <input type="text" :value="invitationLink"
              class="w-full text-gray-700 text-sm bg-transparent border-none focus:ring-0 cursor-text" readonly />
            <button @click="copyToClipboard"
              class="ml-3 p-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-all duration-200"
              title="Copiar enllaç">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 0 0-9-9Z" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Toast de error -->
        <transition
          enter-active-class="transition ease-out duration-300 transform"
          enter-from-class="opacity-0 translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-200 transform"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-2"
        >
          <div v-if="showErrorToast"
            class="fixed bottom-6 right-6 bg-red-500 text-white px-4 py-2 rounded-lg shadow-md flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>Si us plau, selecciona un curs i una divisió.</span>
          </div>
        </transition>

        <!-- Pop-up per confirmar copia -->
        <transition
          enter-active-class="transition ease-out duration-300 transform"
          enter-from-class="opacity-0 translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-200 transform"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-2"
        >
          <div v-if="showCopyPopup"
            class="fixed bottom-6 right-6 bg-green-500 text-white px-4 py-2 rounded-lg shadow-md flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>Enllaç copiat!</span>
          </div>
        </transition>
      </div>
    </div>
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

.text-primary-600 {
  color: #4f46e5;
}

.text-primary-800 {
  color: #3730a3;
}

.border-primary-500 {
  border-color: #6366f1;
}
</style>