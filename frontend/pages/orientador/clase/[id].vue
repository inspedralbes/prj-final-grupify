<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "~/stores/authStore";
import { useCoursesStore } from "~/stores/coursesStore";
import OrientadorDashboardNavOrientador from "~/components/Orientador/DashboardNavOrientador.vue";
import OrientadorStudentList from "~/components/Orientador/OrientadorStudentList.vue";

const route = useRoute();
const router = useRouter();

const studentsStore = useStudentsStore();
const authStore = useAuthStore();
const coursesStore = useCoursesStore();
const { $socket } = useNuxtApp();
const isLoading = ref(true);

// VARIABLES REACTIVES PER LA CLASSE SELECCIONADA
const selectedClass = ref(null);
const classStudents = ref([]);

// Iniciar los valores con cadenas vacías para indicar "todos los estudiantes"
const { searchQuery, filteredStudents } = useStudentSearch(classStudents);

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

// Función para cargar los estudiantes de la clase específica
const fetchClassStudents = async () => {
  try {
    isLoading.value = true;

    // Obtener el ID de la clase desde los parámetros de la ruta
    const classId = route.params.id;
    if (!classId) {
      throw new Error('ID de clase no proporcionado');
    }

    // Parsear el ID para extraer courseId y divisionId
    const [courseId, divisionId] = classId.split('-');

    if (!courseId || !divisionId) {
      throw new Error('Formato de ID de clase incorrecto');
    }

    // Cargar información sobre el curso/clase
    await coursesStore.fetchCourses(true);
    const courseDivision = coursesStore.courses.find(
      c => c.courseId.toString() === courseId && c.division.id.toString() === divisionId
    );

    if (!courseDivision) {
      throw new Error('Clase no encontrada');
    }

    // Guardar información de la clase seleccionada
    selectedClass.value = {
      name: `${courseDivision.courseName} ${courseDivision.division.name}`,
      courseId: parseInt(courseId),
      divisionId: parseInt(divisionId),
      courseName: courseDivision.courseName,
      divisionName: courseDivision.division.name
    };

    // Cargar estudiantes específicos para esta clase
    await studentsStore.fetchStudents(true, parseInt(courseId), parseInt(divisionId));

    // Preparar la lista de estudiantes con el course_name y division_name
    const studentsWithMetadata = studentsStore.students.map(student => ({
      ...student,
      course_name: courseDivision.courseName,
      division_name: courseDivision.division.name
    }));

    // Actualizar la lista de estudiantes
    classStudents.value = studentsWithMetadata;

    console.log(`Cargados ${classStudents.value.length} estudiantes para la clase ${selectedClass.value.name}`);

  } catch (error) {
    console.error("Error al cargar estudiantes de la clase:", error);
    classStudents.value = [];

    // Redirigir a la página principal de listado si hay un error
    router.push('/orientador/dashboard');
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
  console.log("Componente montado: Estudiantes de clase específica");

  // Verificar que sea un orientador
  if (!authStore.isOrientador) {
    console.log("No es un orientador, redirigiendo...");
    router.push('/login');
    return;
  }

  setupSocketListeners();

  // Cargar los estudiantes de la clase seleccionada
  await fetchClassStudents();

  // Importante: esperar un tick para que Vue actualice los datos reactivos
  await nextTick();

  console.log("Inicialización completa. Estudiantes de la clase cargados:", classStudents.value.length);
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <OrientadorDashboardNavOrientador />

    <main class="container mx-auto px-4 py-6 sm:py-8">
      <!-- Título y descripción de la página -->
      <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
          {{ selectedClass ? selectedClass.name : 'Carregant classe...' }}
        </h1>
      </div>

      <!-- Estado de carga -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow p-8 text-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
      </div>

      <!-- Lista de estudiantes -->
      <div v-else-if="classStudents.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <div class="flex justify-end mb-4">
            <!-- Contador de estudiantes -->
            <span class="px-3 py-1.5 text-sm font-medium text-[#00adec] bg-blue-100 rounded-full">
              {{ filteredStudents.length }} estudiants
            </span>
          </div>

          <!-- Campo de búsqueda de estudiantes -->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input v-model="searchQuery" type="text" placeholder="Buscar alumne per nom, cognom o email..."
              class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00adec] focus:border-[#00adec]" />
          </div>
        </div>

        <!-- Lista de estudiantes paginados -->
        <OrientadorStudentList :students="paginatedStudents" class="divide-y divide-gray-200" />

        <!-- Controles de paginación -->
        <div class="flex items-center justify-between px-4 py-3 sm:px-6">
          <button @click="currentPage--" :disabled="currentPage === 1"
            class="px-3 py-1 text-sm text-[#00adec] bg-white border border-gray-200 rounded disabled:opacity-50">
            Anterior
          </button>
          <div class="hidden sm:block text-sm text-gray-600">
            Pàgina {{ currentPage }} de {{ totalPages }}
          </div>
          <button @click="currentPage++" :disabled="currentPage === totalPages"
            class="px-3 py-1 text-sm text-[#00adec] bg-white border border-gray-200 rounded disabled:opacity-50">
            Següent
          </button>
        </div>
      </div>

      <!-- Mensaje si no hay estudiantes -->
      <div v-else class="bg-white rounded-lg shadow p-8 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none"
          viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No s'han trobat alumnes en aquesta classe</h3>
        <p class="text-sm text-gray-600">Aquesta classe no té alumnes assignats.</p>
        <div class="mt-4">
          <button @click="returnToDashboard"
            class="px-4 py-2 bg-[#00adec] text-white rounded-lg hover:bg-blue-600 transition-colors">
            Tornar al Dashboard
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Colores de la aplicación */
.text-primary {
  color: #00adec;
}

.bg-primary {
  background-color: #00adec;
}

.focus\:ring-primary:focus {
  --tw-ring-color: #00adec;
}

.focus\:border-primary:focus {
  border-color: #00adec;
}

.hover\:bg-primary-dark:hover {
  background-color: #0098d3;
}
</style>