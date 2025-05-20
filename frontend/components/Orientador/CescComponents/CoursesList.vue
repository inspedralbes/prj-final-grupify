<script setup>
import { defineProps } from 'vue'; 
import CoursesListItem from './CoursesListItem.vue'; 

// Propietats del component (Rep la llista de cursos)
const props = defineProps({
  courses: {
    type: Array,
    required: true,
    default: () => [],
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
            <th class="text-left py-3">Resultats</th>
          </tr>
        </thead>
        <tbody>
          <!-- Per cada curs, passa a CoursesListItem -->
          <CoursesListItem
            v-for="course in props.courses" 
            :key="course.id"
            :course="course"
          />
        </tbody>
      </table>
    </div>

    <!-- Mensaje cuando no hay cursos -->
    <div v-if="props.courses.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat cursos amb els filtres seleccionats
    </div>
  </div>
</template>
