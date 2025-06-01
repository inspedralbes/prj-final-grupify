<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCoursesStore } from '~/stores/coursesStore'; 
import { useAuthStore } from '~/stores/authStore';
import { useCourseSearch } from '~/composables/useCourseSearch';
import CoursesFilters from '~/components/Teacher/SociogramaComponents/CoursesFilters.vue';
import DashboardNavTeacher from '~/components/Teacher/DashboardNavTeacher.vue';
import CoursesList from '~/components/Teacher/SociogramaComponents/CoursesList.vue';

const error = ref(null);
const isLoading = ref(true);
const coursesStore = useCoursesStore();

onMounted(async () => {
  try {
    const authStore = useAuthStore();
    const userId = authStore.user?.id;
    await coursesStore.fetchCourses(false, userId);
  } catch (err) {
    error.value = 'Error al cargar los cursos';
  } finally {
    isLoading.value = false;
  }
});

const courses = computed(() => coursesStore.courses || []);
const { searchQuery, selectedCourse, selectedDivision, filteredCourses } = useCourseSearch(courses);
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 md:py-8">
      <!-- Header Section -->
      <div class="mb-4 md:mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Gestió de sociogrames</h1>
        <p class="mt-2 text-sm text-gray-600">
          Gestiona i supervisa la gestió dels sociogrames 
        </p>
      </div>

      <!-- Loading State -->
      <div
        v-if="isLoading"
        class="bg-white rounded-lg shadow-sm p-4 md:p-8 text-center"
      >
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant cursos...</p>
      </div>

      <!-- Error State -->
      <div 
        v-if="error" 
        class="bg-red-100 text-red-800 p-4 rounded-lg text-center mx-4"
      >
        {{ error }}
      </div>

      <!-- Main Content -->
      <div v-else class="space-y-4 md:space-y-6">
        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
          <CoursesFilters
            v-model:searchQuery="searchQuery"
            v-model:selectedCourse="selectedCourse"
            v-model:selectedDivision="selectedDivision"
          />
        </div>

        <!-- Courses List Section -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
              <h2 class="text-lg font-medium text-gray-900">
                Llistat de cursos
              </h2>
              <span class="inline-flex px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-full">
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