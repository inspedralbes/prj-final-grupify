<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCoursesStore } from '~/stores/coursesStore'; 
import { useCourseSearch } from '~/composables/useCourseSearch';
import CoursesFilters from '~/components/Teacher/CescComponents/CoursesFilters.vue';
import DashboardNavTeacher from '~/components/Teacher/DashboardNavTeacher.vue';
import CoursesList from '~/components/Teacher/CescComponents/CoursesList.vue';

const error = ref(null);
const isLoading = ref(true);

// Usar el store de cursos
const coursesStore = useCoursesStore();

onMounted(async () => {
  try {
    // Cargar los cursos desde el store
    await coursesStore.fetchCourses();
  } catch (err) {
    error.value = 'Error al cargar los cursos';
  } finally {
    isLoading.value = false;
  }
});

// Computed para acceder a los cursos cargados
const courses = computed(() => coursesStore.courses || []);

const { searchQuery, selectedCourse, selectedDivision, filteredCourses } = useCourseSearch(courses);
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navbar del profesor -->
    <DashboardNavTeacher />
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Gestió del CESC</h1>
        <p class="mt-2 text-sm text-gray-600">
          Gestiona i supervisa la gestió del CESC
        </p>
      </div>

      <!-- Estado de carga -->
      <div
        v-if="isLoading"
        class="bg-white rounded-lg shadow-sm p-8 text-center"
      >
        <div
          class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"
        ></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant cursos...</p>
      </div>

      <!-- Mostrar error -->
      <div v-if="error" class="bg-red-100 text-red-800 p-4 rounded-lg text-center">
        {{ error }}
      </div>

      <!-- Contenido cuando no hay error y carga está completa -->
      <div v-else class="space-y-6">
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <CoursesFilters
            v-model:searchQuery="searchQuery"
            v-model:selectedCourse="selectedCourse"
            v-model:selectedDivision="selectedDivision"
          />
        </div>

        <!-- Lista de cursos -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">
                Llistat de cursos
              </h2>
              <span
                class="px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-full"
              >
                {{ filteredCourses.length }} Cursos
              </span>
            </div>
          </div>

          <CoursesList
            :courses="filteredCourses"
            class="divide-y divide-gray-200"
          />
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Estilos personalizables */
</style>
