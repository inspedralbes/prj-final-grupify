<script setup>
import { ref, computed, onMounted } from 'vue';
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "@/stores/auth"; // Importar el store de auth

const studentsStore = useStudentsStore();
const authStore = useAuthStore(); // Acceso al store de auth
const { $socket } = useNuxtApp();
const isLoading = ref(true);

// VARIABLES REACTIVES PARA FILTRAR ESTUDIANTES (LOGICA ORIGINAL)
const students = computed(() => studentsStore.students || []);
const { searchQuery, selectedCourse, selectedDivision, filteredStudents } =
  useStudentSearch(students);

// VARIABLES PARA LA GENERACIÓN DE INVITACIÓN
const courses = ref([]);
const divisions = ref([]);
const selectedInvitationCourse = ref(""); // ID del curso seleccionado para el enlace
const selectedInvitationDivision = ref(""); // ID de la división seleccionada para el enlace
const invitationLink = ref(""); // Enlace generado

// Función para cargar los estudiantes y cursos al montar el componente
onMounted(async () => {
  await studentsStore.fetchStudents();
  setupSocketListeners();
  await fetchCourses(); // Carga los cursos para la invitación
  isLoading.value = false;
});

// Configuración de los listeners del socket
const setupSocketListeners = () => {
  $socket.on('user_online', (userId) => {
    studentsStore.setUserOnline(userId);
  });
  $socket.on('user_offline', (userId) => {
    studentsStore.setUserOffline(userId);
  });
};

// Función para obtener todos los cursos
const fetchCourses = async () => {
  try {
    const response = await fetch("https://api.grupify.cat/api/courses", {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${authStore.token}`, // Usar el token desde authStore
      },
    });
    if (!response.ok) throw new Error("Error al cargar los cursos");
    const data = await response.json();
    courses.value = data;
  } catch (error) {
    console.error("Error al cargar los cursos:", error);
  }
};

// Función para obtener las divisiones según el curso seleccionado
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
          Authorization: `Bearer ${authStore.token}`, // Usar el token desde authStore
        },
      }
    );
    if (!response.ok) throw new Error("Error al cargar las divisiones");
    const data = await response.json();
    if (data.divisions) {
      divisions.value = data.divisions;
    } else {
      divisions.value = [];
      console.error(data.message || "No se han encontrado divisiones");
    }
  } catch (error) {
    console.error("Error al cargar las divisiones:", error);
  }
};

// Función para generar el enlace de invitación
const generateInvitation = async () => {
  if (!selectedInvitationCourse.value || !selectedInvitationDivision.value) {
    alert("Por favor, selecciona un curso y una división antes de generar el enlace.");
    return;
  }
  try {
    const response = await fetch("https://api.grupify.cat/api/invitations", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${authStore.token}`, // Usar el token desde authStore
      },
      body: JSON.stringify({
        course_id: selectedInvitationCourse.value,
        division_id: selectedInvitationDivision.value,
      }),
    });
    if (!response.ok) throw new Error("Error al generar la invitación");
    const data = await response.json();
    invitationLink.value = data.link;
  } catch (error) {
    console.error("Error al generar la invitación:", error);
  }
};
</script>


<template>
  <div class="min-h-screen bg-gray-50">
    <TeacherDashboardNavTeacher />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Títol i descripció del panell -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Gestió d'Alumnes</h1>
        <p class="mt-2 text-sm text-gray-600">
          Gestiona i supervisa l'alumnat registrat a l'institut
        </p>
      </div>

      <!-- Secció per generar l’enllaç d’invitació -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Generar enllaç d'invitació</h2>
        <!-- Selector de curs -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Selecciona un curs
          </label>
          <select v-model="selectedInvitationCourse" @change="fetchInvitationDivisions"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent">
            <option disabled value="">Selecciona un curs</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.name }}
            </option>
          </select>
        </div>

        <!-- Selector de divisió (només si hi ha divisions disponibles) -->
        <div class="mb-4" v-if="divisions.length > 0">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Selecciona una divisió
          </label>
          <select v-model="selectedInvitationDivision"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent">
            <option disabled value="">Selecciona una divisió</option>
            <option v-for="division in divisions" :key="division.id" :value="division.id">
              {{ division.division }}
            </option>
          </select>
        </div>

        <!-- Botó per generar l'enllaç -->
        <button @click="generateInvitation" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Generar enllaç
        </button>

        <!-- Mostra l'enllaç generat si existeix -->
        <div v-if="invitationLink" class="mt-4 p-4 bg-green-100 rounded-md">
          <p class="text-green-800 mb-2">Enllaç d'invitació:</p>
          <a :href="invitationLink" class="text-blue-600">{{ invitationLink }}</a>
        </div>
      </div>

      <!-- Llista d'estudiants i filtres -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Filtres d'estudiants -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <TeacherStudentFilters v-model:search-query="searchQuery" v-model:selected-course="selectedCourse"
            v-model:selected-division="selectedDivision" />
        </div>

        <!-- Llistat d'estudiants -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-sm text-gray-500 uppercase">
                Llistat d'estudiants
              </h2>
              <span class="px-3 py-1 text-sm text-gray-500 bg-gray-100 rounded-full">
                {{ filteredStudents.length }} estudiants
              </span>
            </div>
          </div>
          <TeacherStudentList :students="filteredStudents" class="divide-y divide-gray-200" />
        </div>
      </div>
    </main>
  </div>
</template>
