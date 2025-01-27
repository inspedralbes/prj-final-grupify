<script setup>
import { useStudentSearch } from "@/composables/useStudentSearch";
import { useStudentsStore } from "@/stores/studentsStore";

const studentsStore = useStudentsStore();
const isLoading = ref(true);

// Llamar a la API al montar el componente
onMounted(async () => {
  await studentsStore.fetchStudents();
  isLoading.value = false;
});

// Utilizar computed para asegurar que reaccionen cambios en el estado
const students = computed(() => studentsStore.students || []);
const { searchQuery, selectedCourse, selectedDivision, filteredStudents } =
  useStudentSearch(students);

// Método para navegar al dashboard
const goToDashboard = () => {
  navigateTo("/professor/dashboard");
};
</script>

<template>
  <div class="p-6">
    <div class="relative flex items-center mb-6">
      <!-- Botón de volver -->
      <button
        class="absolute left-0 flex items-center space-x-1 text-gray-700 hover:text-gray-900"
        @click="goToDashboard"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        <span>Tornar</span>
      </button>

      <!-- Título centrado -->
      <h1 class="flex-grow text-center text-2xl font-bold">
        Gestión de Alumnos
      </h1>
    </div>
    <div v-if="isLoading" class="text-center p-8">Carregant estudiants...</div>
    <div v-else>
      <TeacherStudentFilters
        v-model:search-query="searchQuery"
        v-model:selected-course="selectedCourse"
        v-model:selected-division="selectedDivision"
      />
      <TeacherStudentList :students="filteredStudents" />
    </div>
  </div>
</template>
