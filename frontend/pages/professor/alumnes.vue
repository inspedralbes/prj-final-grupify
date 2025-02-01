<script setup>
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import DashboardNavTeacher from "~/components/Teacher/DashboardNavTeacher.vue";

const studentsStore = useStudentsStore();
const { $socket } = useNuxtApp();
const isLoading = ref(true);

onMounted(async () => {
  await studentsStore.fetchStudents();
  setupSocketListeners();
  isLoading.value = false;
});

const setupSocketListeners = () => {
  $socket.on('user_online', (userId) => {
    studentsStore.setUserOnline(userId);
  });

  $socket.on('user_offline', (userId) => {
    studentsStore.setUserOffline(userId);
  });
};

const students = computed(() => studentsStore.students || []);
const { searchQuery, selectedCourse, selectedDivision, filteredStudents } =
  useStudentSearch(students);
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
        <!-- Tarjeta de filtros -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <TeacherStudentFilters v-model:search-query="searchQuery" v-model:selected-course="selectedCourse"
            v-model:selected-division="selectedDivision" />
        </div>

        <!-- Lista de estudiantes -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <!-- Título con la tipografía de la tabla -->
              <h2 class="text-sm text-gray-500 uppercase">
                Llistat d'estudiants
              </h2>
              <!-- Contador de estudiantes con tipografía coherente -->
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