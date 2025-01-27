<script setup>
import { ref, defineProps } from 'vue'; 
import { EyeIcon } from "@heroicons/vue/24/outline";
import CoursesListItem from './CoursesListItem.vue'; 

const router = useRouter();
const viewProfile = (courseId) => {
  if (!courseId) return;
  router.push(`/professor/courseProfile/${courseId}`);
};

// Propiedades del componente (Recibes el array de cursos de los padres)
const props = defineProps({
  courses: {
    type: Array,
    required: true,
    default: () => [],
  },
});
console.log('Cursos recibidos en props:', props.courses);

// Definir estado de carga
const isLoading = ref(false);  // 'isLoading' ahora está definido correctamente
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
          <!-- Por cada curso, pasar a CoursesListItem -->
          <CoursesListItem
            v-for="course in props.courses" 
            :key="course.id"
            :course="course"
          />
        </tbody>
      </table>
    </div>

    <!-- Mensaje cuando no hay cursos -->
    <div v-if="!isLoading && props.courses.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat cursos amb els filtres seleccionats
    </div>

    <!-- Indicador de carga -->
    <div v-if="isLoading" class="text-center py-8 text-gray-500">
      Carregant cursos...
    </div>
  </div>
</template>
