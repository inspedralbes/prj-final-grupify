<script setup>
import { ref, computed, onMounted } from "vue";
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import DashboardNavTeacher from "~/components/Teacher/DashboardNavTeacher.vue";
import TeacherStudentFilters from "~/components/Teacher/TeacherStudentFilters.vue";
import TeacherStudentList from "~/components/Teacher/TeacherStudentList.vue";

const studentsStore = useStudentsStore();
const { $socket } = useNuxtApp();
const isLoading = ref(true);

// Estados para la generación del enllaç d'invitació
const isGenerating = ref(false);
const invitationLink = ref("");
const invitationError = ref("");

onMounted(async () => {
  await studentsStore.fetchStudents();
  setupSocketListeners();
  isLoading.value = false;
});

const setupSocketListeners = () => {
  $socket.on("user_online", (userId) => {
    studentsStore.setUserOnline(userId);
  });

  $socket.on("user_offline", (userId) => {
    studentsStore.setUserOffline(userId);
  });
};

const students = computed(() => studentsStore.students || []);
const { searchQuery, selectedCourse, selectedDivision, filteredStudents } =
  useStudentSearch(students);

/**
 * Función para generar el enlace de invitación.
 * Se requiere que s'estigui seleccionat un curs i divisió.
 * La petición se hace a un endpoint (por ejemplo, /api/invitations/generate)
 * que debe devolver el token generado.
 */
const generateInvitationLink = async () => {
  // Reinicia mensajes previos
  invitationError.value = "";
  invitationLink.value = "";
  
  // Validar que se haya seleccionado curso i divisió
  if (!selectedCourse.value || !selectedDivision.value) {
    invitationError.value = "Selecciona un curs i divisió per generar la invitació.";
    return;
  }

  isGenerating.value = true;
  try {
    // Realizar la petición POST para generar la invitación
    const response = await $fetch("http://localhost:8000/api/invitations/generate", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        course_id: selectedCourse.value,
        division_id: selectedDivision.value,
      }),
    });

    // Se espera que la respuesta contenga el token generado
    if (response.token) {
      // Generar el enlace usando la URL actual (o ajusta según corresponda)
      invitationLink.value = `${window.location.origin}/register?token=${response.token}`;
    } else {
      invitationError.value = "No s'ha pogut generar l'enllaç d'invitació.";
    }
  } catch (err) {
    invitationError.value = err.message || "Error al generar l'enllaç d'invitació.";
  } finally {
    isGenerating.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Gestió d'Alumnes</h1>
        <p class="mt-2 text-sm text-gray-600">
          Gestiona i supervisa l'alumnat registrat a l'institut
        </p>
      </div>

      <!-- Estado de carga -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Tarjeta de filtros i generació de la invitació -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <TeacherStudentFilters 
            v-model:search-query="searchQuery" 
            v-model:selected-course="selectedCourse"
            v-model:selected-division="selectedDivision" 
          />

          <!-- Botón para generar el enlace de invitación -->
          <div class="mt-4">
            <button 
              @click="generateInvitationLink" 
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
              :disabled="isGenerating"
            >
              {{ isGenerating ? "Generant enllaç..." : "Generar enllaç d'invitació" }}
            </button>
            <!-- Mensajes de error o enlace generado -->
            <div v-if="invitationError" class="mt-2 text-red-600">
              {{ invitationError }}
            </div>
            <div v-if="invitationLink" class="mt-2 text-green-600 break-all">
              Enllaç generat: 
              <a :href="invitationLink" target="_blank" class="underline">
                {{ invitationLink }}
              </a>
            </div>
          </div>
        </div>

        <!-- Lista de estudiantes -->
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

<style scoped>
</style>
