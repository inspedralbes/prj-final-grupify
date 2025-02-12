<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "@/stores/auth"; // Importar el store de auth

const studentsStore = useStudentsStore();
const authStore = useAuthStore();
const { $socket } = useNuxtApp();
const isLoading = ref(true);

// VARIABLES REACTIVES PER FILTRAR ELS ESTUDIANTS (lògica original)
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

// Reinicia la pàgina actual quan canvien els filtres
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

// Funcions per obrir i tancar el modal
const openInvitationModal = () => {
  showInvitationModal.value = true;
};
const closeInvitationModal = () => {
  showInvitationModal.value = false;
};

// Funció per carregar els estudiants i els cursos al montar el component
onMounted(async () => {
  await studentsStore.fetchStudents();
  setupSocketListeners();
  await fetchCourses(); // Carrega els cursos per a la invitació
  isLoading.value = false;
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

// Funció per obtenir tots els cursos
const fetchCourses = async () => {
  try {
    const response = await fetch("http://localhost:8000/api/courses", {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${authStore.token}`, // Usar el token des d'authStore
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
      `http://localhost:8000/api/course-divisions?course_id=${selectedInvitationCourse.value}`,
      {
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          Authorization: `Bearer ${authStore.token}`, // Usar el token des d'authStore
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
    alert("Si us plau, selecciona un curs i una divisió abans de generar l'enllaç.");
    return;
  }
  try {
    const response = await fetch("http://localhost:8000/api/invitations", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${authStore.token}`, // Usar el token des d'authStore
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

      <!-- Llistat d'estudiants amb controls i icona per generar invitació -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Filtres d'estudiants -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <TeacherStudentFilters 
            v-model:search-query="searchQuery" 
            v-model:selected-course="selectedCourse"
            v-model:selected-division="selectedDivision" 
          />
        </div>

        <!-- Llistat d'estudiants -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-sm text-gray-500 uppercase">
                Llistat d'estudiants
              </h2>
              <!-- Container amb l'icona d'invitació i el número d'estudiants -->
              <div class="flex items-center space-x-2">
                <button 
  @click="openInvitationModal" 
  class="flex items-center justify-center w-10 h-10 bg-[rgb(0,173,238)] text-white rounded-full hover:bg-blue-600"
  title="Generar enllaç d'invitació"
>
  <svg 
    xmlns="http://www.w3.org/2000/svg" 
    fill="white" 
    viewBox="0 0 24 24" 
    stroke-width="1.5" 
    stroke="white" 
    class="w-5 h-5"
  >
    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
  </svg>
</button>


                <span class="px-3 py-1 text-sm text-gray-500 bg-gray-100 rounded-full">
                  {{ filteredStudents.length }} estudiants
                </span>
              </div>
            </div>
          </div>
          <!-- Mostrem els estudiants paginats -->
          <TeacherStudentList :students="paginatedStudents" class="divide-y divide-gray-200" />

          <!-- Controls de paginació -->
          <div class="flex justify-center mt-4 space-x-4">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
            >
              Anterior
            </button>
            <span>Pàgina {{ currentPage }} de {{ totalPages }}</span>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal per generar l'enllaç d'invitació -->
    <div 
      v-if="showInvitationModal" 
      class="fixed inset-0 z-50 flex items-center justify-center"
    >
      <!-- Fons semi-transparent -->
      <div 
        class="absolute inset-0 bg-gray-900 opacity-50" 
        @click="closeInvitationModal"
      ></div>
      <!-- Contingut del modal -->
      <div class="bg-white rounded-lg p-6 relative z-10 w-11/12 md:w-1/2">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold">Generar enllaç d'invitació</h2>
          <button 
            @click="closeInvitationModal" 
            class="text-gray-500 hover:text-gray-700"
            title="Tanca"
          >
            <!-- Icono de tancar (X) -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" 
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" 
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Formulari per generar la invitació -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Selecciona un curs
          </label>
          <select 
            v-model="selectedInvitationCourse" 
            @change="fetchInvitationDivisions"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
          >
            <option disabled value="">Selecciona un curs</option>
            <option 
              v-for="course in courses" 
              :key="course.id" 
              :value="course.id"
            >
              {{ course.name }}
            </option>
          </select>
        </div>

        <!-- Selector de divisió (només si hi ha divisions disponibles) -->
        <div class="mb-4" v-if="divisions.length > 0">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Selecciona una divisió
          </label>
          <select 
            v-model="selectedInvitationDivision"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
          >
            <option disabled value="">Selecciona una divisió</option>
            <option 
              v-for="division in divisions" 
              :key="division.id" 
              :value="division.id"
            >
              {{ division.division }}
            </option>
          </select>
        </div>

        <!-- Botó per generar l'enllaç -->
        <button 
          @click="generateInvitation" 
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Generar enllaç
        </button>

        <!-- Mostra l'enllaç generat si existeix -->
        <div 
          v-if="invitationLink" 
          class="mt-4 p-4 bg-green-100 rounded-md"
        >
          <p class="text-green-800 mb-2">Enllaç d'invitació:</p>
          <a :href="invitationLink" class="text-blue-600">
            {{ invitationLink }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
