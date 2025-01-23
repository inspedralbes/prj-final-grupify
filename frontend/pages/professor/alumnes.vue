<script setup>
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";
import DashboardNavTeacher from '~/components/Teacher/DashboardNavTeacher.vue'

const studentsStore = useStudentsStore();
const isLoading = ref(true);

onMounted(async () => {
  await studentsStore.fetchStudents();
  isLoading.value = false;
});

const students = computed(() => studentsStore.students || []);
const { searchQuery, selectedCourse, selectedDivision, filteredStudents } = useStudentSearch(students);
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
          Gestió de Alumnes
        </h1>
        <p class="mt-2 text-sm text-gray-600">
          Gestiona i supervisa l'alumnat registrat a l'institut
        </p>
      </div>

      <!-- Estado de carga -->
      <div v-if="isLoading" 
           class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant estudiants...</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Tarjeta de filtros -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <TeacherStudentFilters
            v-model:search-query="searchQuery"
            v-model:selected-course="selectedCourse"
            v-model:selected-division="selectedDivision"
          />
        </div>

        <!-- Lista de estudiantes -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">
                Lista de Estudiantes
              </h2>
              <span class="px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-full">
                {{ filteredStudents.length }} estudiantes
              </span>
            </div>
          </div>
          
          <TeacherStudentList 
            :students="filteredStudents" 
            class="divide-y divide-gray-200"
          />
        </div>
      </div>
    </main>
  </div>
</template>