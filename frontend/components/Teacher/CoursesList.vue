<script setup>
// Usar store
import { useCoursesStore } from "~/stores/coursesStore"; // No olvides importar tu store
import { onMounted, ref } from 'vue';

// Crear un estado local para manejar los cursos y su carga
const coursesStore = useCoursesStore();
const courses = ref([]); // Inicializa courses como un arreglo vacío
const isLoading = ref(true); // Indicador de carga

// Llamar a la API al montar el componente
onMounted(async () => {
  await coursesStore.fetchCourses();
  courses.value = coursesStore.courses || []; // Guarda los cursos cuando se obtienen
  isLoading.value = false;
});

defineProps({
  courses: {
    type: Array,  // Debería ser un Array, no Object, ya que courses es un array en tu store
    required: true,
  },
});
</script>

<template>
  <div class="card">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left py-3">Curs</th>
            <th class="text-left py-3">Divisió</th>
            <th class="text-left py-3">Estat</th>
            <th class="text-left py-3">Resultats</th>
          </tr>
        </thead>
        <tbody>
          <CourseListItem
            v-for="course in courses" 
            :key="course.id"
            :course="course"
          />
        </tbody>
      </table>
    </div>

    <!-- Mensaje cuando no hay cursos -->
    <div v-if="!isLoading && courses.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat cursos amb els filtres seleccionats
    </div>

    <!-- Indicador de carga -->
    <div v-if="isLoading" class="text-center py-8 text-gray-500">
      Carregant cursos...
    </div>
  </div>
</template>
