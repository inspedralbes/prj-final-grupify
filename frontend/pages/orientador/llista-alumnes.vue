<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "~/stores/authStore";
import OrientadorDashboardNavOrientador from "~/components/Orientador/DashboardNavOrientador.vue";
import OrientadorStudentList from "~/components/Orientador/OrientadorStudentList.vue";

const router = useRouter();

const studentsStore = useStudentsStore();
const authStore = useAuthStore();
const { $socket } = useNuxtApp();
const isLoading = ref(true);

// VARIABLES REACTIVES PER FILTRAR ELS ESTUDIANTS
const allAssignedStudents = ref([]);
const students = computed(() => allAssignedStudents.value);

// Iniciar los valores con cadenas vacías para indicar "todos los estudiantes"
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

// Selector de cursos exclusivamente asignados al orientador
const assignedClasses = ref([]);

// Función para cargar todos los estudiantes asignados al orientador
const fetchAllAssignedStudents = async () => {
  try {
    isLoading.value = true;
    console.log("Iniciando carga de estudiantes para orientador");
    
    // Verificar que sea un orientador
    if (!authStore.isOrientador) {
      console.log("No es un orientador, redirigiendo...");
      router.push('/login');
      return;
    }
    
    // Si el orientador tiene clases asignadas mediante course_divisions
    if (authStore.user?.course_divisions?.length > 0) {
      console.log("El orientador tiene", authStore.user.course_divisions.length, "clases asignadas");
      
      // Lista para almacenar todos los estudiantes asignados
      const allStudents = [];
      const uniqueStudentIds = new Set();
      
      assignedClasses.value = []; // Limpiar clases asignadas
      
      // Para cada clase asignada, obtener la lista de estudiantes
      for (const cd of authStore.user.course_divisions) {
        console.log("Procesando clase:", cd.course_name, cd.division_name);
        
        // Cargar estudiantes específicos para esta clase
        await studentsStore.fetchStudents(true, cd.course_id, cd.division_id);
        console.log("Estudiantes cargados para la clase:", studentsStore.students.length);
        
        // Añadir la clase a la lista de clases asignadas
        assignedClasses.value.push({
          name: `${cd.course_name} ${cd.division_name}`,
          courseId: cd.course_id,
          divisionId: cd.division_id,
          courseName: cd.course_name,
          divisionName: cd.division_name
        });
        
        // Añadir estudiantes a la lista general, evitando duplicados y asegurando que tengan course_name y division_name
        studentsStore.students.forEach(student => {
          if (!uniqueStudentIds.has(student.id)) {
            uniqueStudentIds.add(student.id);
            // Asegurar que el estudiante tenga los campos necesarios para el filtrado
            const studentWithCourseInfo = {
              ...student,
              course_name: student.course_name || cd.course_name,
              division_name: student.division_name || cd.division_name
            };
            allStudents.push(studentWithCourseInfo);
          }
        });
      }
      
      console.log("Total de estudiantes únicos cargados:", allStudents.length);
      
      // Actualizar la lista de estudiantes asignados
      allAssignedStudents.value = allStudents;
    } else {
      // Comprobar si en realidad no tiene clases asignadas o solo es que no están en course_divisions
      console.log("El orientador no tiene clases asignadas mediante course_divisions, verificando en el backend...");
      
      try {
        // Obtener asignaciones desde el backend para este orientador específico
        const response = await fetch(`http://localhost:8000/api/orientador-assignments/${authStore.user.id}`, {
          headers: {
            Authorization: `Bearer ${authStore.token}`,
            Accept: 'application/json',
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          
          if (data.assignments && data.assignments.length > 0) {
            console.log("Se encontraron asignaciones en el backend:", data.assignments.length);
            
            // Lista para almacenar todos los estudiantes asignados
            const allStudents = [];
            const uniqueStudentIds = new Set();
            
            assignedClasses.value = []; // Limpiar clases asignadas
            
            // Para cada asignación del orientador, obtener la lista de estudiantes
            for (const assignment of data.assignments) {
              console.log("Procesando asignación:", assignment.course_name, assignment.division_name);
              
              // Cargar estudiantes específicos para esta asignación
              await studentsStore.fetchStudents(true, assignment.course_id, assignment.division_id);
              console.log("Estudiantes cargados para la asignación:", studentsStore.students.length);
              
              // Añadir la clase a la lista de clases asignadas
              assignedClasses.value.push({
                name: `${assignment.course_name} ${assignment.division_name}`,
                courseId: assignment.course_id,
                divisionId: assignment.division_id,
                courseName: assignment.course_name,
                divisionName: assignment.division_name
              });
              
              // Añadir estudiantes a la lista general, evitando duplicados y asegurando que tengan course_name y division_name
              studentsStore.students.forEach(student => {
                if (!uniqueStudentIds.has(student.id)) {
                  uniqueStudentIds.add(student.id);
                  // Asegurar que el estudiante tenga los campos necesarios para el filtrado
                  const studentWithCourseInfo = {
                    ...student,
                    course_name: student.course_name || assignment.course_name,
                    division_name: student.division_name || assignment.division_name
                  };
                  allStudents.push(studentWithCourseInfo);
                }
              });
            }
            
            console.log("Total de estudiantes asignados cargados:", allStudents.length);
            allAssignedStudents.value = allStudents;
            return; // Salir de la función después de cargar correctamente
          } else {
            console.log("No se encontraron asignaciones para este orientador en el backend");
          }
        } else {
          console.error("Error al obtener asignaciones del orientador:", response.statusText);
        }
      } catch (error) {
        console.error("Error al consultar asignaciones del orientador:", error);
      }
      
      // Si llegamos aquí, es porque no se pudieron obtener asignaciones específicas
      console.log("No se pudieron obtener asignaciones específicas para el orientador, mostrando mensaje informativo");
      
      // No cargamos estudiantes, dejamos la lista vacía para mostrar mensaje
      allAssignedStudents.value = [];
      assignedClasses.value = [];
    }
  } catch (error) {
    console.error("Error al cargar estudiantes asignados:", error);
    allAssignedStudents.value = [];
  } finally {
    isLoading.value = false;
  }
};

// Función para filtrar estudiantes por curso/división
const filterStudentsByClass = async (cls) => {
  try {
    isLoading.value = true;
    
    if (cls) {
      console.log("Filtrando por clase:", cls.name, "courseId:", cls.courseId, "divisionId:", cls.divisionId);
      
      // Limpiar lista previa para evitar datos antiguos
      allAssignedStudents.value = [];
      
      // Obtener estudiantes específicos para el curso y división seleccionados
      await studentsStore.fetchStudents(true, cls.courseId, cls.divisionId);
      
      // Asegurarse de que todos los estudiantes tengan course_name y division_name correcto
      const studentsWithMetadata = studentsStore.students.map(student => ({
        ...student,
        course_name: cls.courseName,
        division_name: cls.divisionName
      }));
      
      // Actualizar la lista de estudiantes con los datos correctos
      allAssignedStudents.value = studentsWithMetadata;
      
      // Establecer los filtros de UI para que coincidan con los datos
      selectedCourse.value = cls.courseName;
      selectedDivision.value = cls.divisionName;
      
      console.log("Estudiantes cargados para filtrado:", allAssignedStudents.value.length);
      console.log("Ejemplo estudiante:", allAssignedStudents.value[0]);
      
    } else {
      console.log("Mostrando todos los estudiantes asignados");
      
      // Recargar todos los estudiantes asignados
      await fetchAllAssignedStudents();
      
      // Asegurarse de que los datos estén también en el store para acceso por ID
      // Esto es crucial - guardar TODOS los estudiantes asignados también en el store
      studentsStore.setAllStudents(allAssignedStudents.value);
      
      // Resetear filtros para mostrar todo
      selectedCourse.value = '';
      selectedDivision.value = '';
    }
    
    // Resetear a página 1 al cambiar de selección
    currentPage.value = 1;
  } catch (error) {
    console.error("Error al filtrar estudiantes:", error);
  } finally {
    isLoading.value = false;
  }
};

// Configuración de los listeners del socket
const setupSocketListeners = () => {
  $socket.on('user_online', (userId) => {
    studentsStore.setUserOnline(userId);
  });
  $socket.on('user_offline', (userId) => {
    studentsStore.setUserOffline(userId);
  });
};

// Montar el componente y cargar datos
onMounted(async () => {
  console.log("Componente montado: Lista de alumnos del orientador");
  console.log("Rol del usuario:", authStore.userRole);
  console.log("¿Es orientador?", authStore.isOrientador);
  
  if (authStore.user?.course_divisions) {
    console.log("Divisiones asignadas:", authStore.user.course_divisions.length);
  } else {
    console.log("No hay divisiones asignadas en el usuario");
  }
  
  setupSocketListeners();
  
  // Cargar todos los estudiantes primero
  await fetchAllAssignedStudents();
  
  // Importante: guardar todos los estudiantes en el store para permitir navegación por ID
  studentsStore.setAllStudents(allAssignedStudents.value);
  
  // Resetear filtros y asegurarse que se ve "Tots els alumnes"
  selectedCourse.value = '';
  selectedDivision.value = '';
  
  // Importante: esperar un tick para que Vue actualice los datos reactivos
  await nextTick();
  
  console.log("Inicialización completa. Estudiantes cargados:", allAssignedStudents.value.length);
  console.log("Filtros - Curso:", selectedCourse.value, "División:", selectedDivision.value);
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <OrientadorDashboardNavOrientador />

    <main class="container mx-auto px-4 py-6 sm:py-8">
      <!-- Títol i descripció del panell -->
      <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Els meus alumnes</h1>
        <p class="mt-1 sm:mt-2 text-sm text-gray-600">
          Gestiona i supervisa l'alumnat assignat
        </p>
      </div>
      
      <!-- Estado de carga -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow p-8 text-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
      </div>
      
      <div v-else>
        <!-- Tarjetas de clases asignadas -->
        <div v-if="assignedClasses.length > 0" class="bg-white rounded-lg shadow mb-6">
          <div class="p-4 sm:p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Classes assignades</h2>
            <p class="mt-1 text-sm text-gray-600">Selecciona una classe per veure els seus alumnes:</p>
          </div>
          <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
              <!-- Opción para ver todos los alumnos -->
              <div 
                @click="filterStudentsByClass(null)"
                class="course-selection-card border rounded-lg p-4 cursor-pointer transition-all duration-300 flex flex-col items-center text-center"
                :class="[
                  !selectedCourse && !selectedDivision
                    ? 'bg-primary-50 border-primary-500 shadow-md' 
                    : 'bg-white hover:bg-gray-50 border-gray-200'
                ]"
              >
                <div class="bg-indigo-100 p-4 rounded-full mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">Tots els alumnes</h3>
                <p class="text-sm text-gray-600">Veure tots els alumnes assignats</p>
                <div class="mt-3" v-if="!selectedCourse && !selectedDivision">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                    Seleccionat
                  </span>
                </div>
              </div>
              
              <!-- Tarjeta para cada clase específica -->
              <div 
                v-for="(cls, index) in assignedClasses" 
                :key="index" 
                @click="filterStudentsByClass(cls)"
                class="course-selection-card border rounded-lg p-4 cursor-pointer transition-all duration-300 flex flex-col items-center text-center"
                :class="[
                  selectedCourse === cls.courseName && selectedDivision === cls.divisionName 
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
                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ cls.name }}</h3>
                <p class="text-sm text-gray-600">Veure alumnes d'aquest curs</p>
                <div class="mt-3" v-if="selectedCourse === cls.courseName && selectedDivision === cls.divisionName">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                    Seleccionat
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Mensaje si no hay clases asignadas -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-1">No tens classes assignades</h3>
          <p class="text-sm text-gray-600">Contacta amb l'administrador per assignar-te classes</p>
        </div>
        
        <!-- Lista de estudiantes -->
        <div v-if="allAssignedStudents.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
          <div class="p-4 sm:p-6 border-b border-gray-200">
            <!-- Encabezado con información de la selección -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-lg font-medium text-gray-900">
                  {{ selectedCourse && selectedDivision 
                    ? `Alumnes de ${selectedCourse} ${selectedDivision}` 
                    : 'Tots els alumnes assignats' }}
                </h2>
              </div>
              
              <div class="mt-3 sm:mt-0">
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
          
          <!-- Lista de estudiantes paginados -->
          <div v-if="isLoading" class="text-center py-8">
            <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="mt-3 text-gray-600">Carregant estudiants de la classe...</p>
          </div>
          <OrientadorStudentList v-else :students="paginatedStudents" class="divide-y divide-gray-200" />

          <!-- Controles de paginación -->
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
        
        <!-- Mensaje si no hay estudiantes -->
        <div v-else-if="!isLoading" class="bg-white rounded-lg shadow p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-1">No s'han trobat alumnes assignats</h3>
          <p class="text-sm text-gray-600">No tens classes assignades. Contacta amb l'administrador perquè t'assigni les classes corresponents.</p>
          <div class="mt-4">
            <button 
              @click="fetchAllAssignedStudents" 
              class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors"
            >
              Tornar a verificar
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
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

.bg-primary {
  background-color: #4f46e5;
}

.bg-primary-dark {
  background-color: #3730a3;
}

.bg-primary-500 {
  background-color: #6366f1;
}

.bg-primary-600 {
  background-color: #4f46e5;
}

</style>