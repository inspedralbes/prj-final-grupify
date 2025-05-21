<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  students: {
    type: Array,
    required: true
  },
  showByCourse: {
    type: Boolean,
    default: false
  }
});

// Calcular estadísticas generales
const totalStudents = computed(() => props.students.length);
const answeredStudents = computed(() => props.students.filter(s => s.answered).length);
const completionPercentage = computed(() => {
  if (totalStudents.value === 0) return 0;
  return Math.round((answeredStudents.value / totalStudents.value) * 100);
});

// Agrupar estudiantes por curso
const courseStats = computed(() => {
  const stats = {};
  
  props.students.forEach(student => {
    // Crear una clave única para el curso (curso + división)
    const courseKey = `${student.course || 'Sin curso'} ${student.division || ''}`.trim();
    
    // Inicializar el objeto de estadísticas si no existe
    if (!stats[courseKey]) {
      stats[courseKey] = {
        total: 0,
        answered: 0,
        percentage: 0
      };
    }
    
    // Incrementar contadores
    stats[courseKey].total += 1;
    if (student.answered) {
      stats[courseKey].answered += 1;
    }
    
    // Calcular porcentaje
    stats[courseKey].percentage = Math.round(
      (stats[courseKey].answered / stats[courseKey].total) * 100
    );
  });
  
  return stats;
});

// Lista ordenada de cursos para mostrar en la interfaz
const sortedCourses = computed(() => {
  return Object.keys(courseStats.value).sort();
});
</script>

<template>
  <!-- Status summary -->
  <div class="mb-6 p-4 bg-gray-50 rounded-lg">
    <div class="flex justify-between items-center mb-2">
      <h3 class="font-medium text-gray-800">Estat de Respostes</h3>
      <span class="text-sm font-bold">{{ completionPercentage }}%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2.5">
      <div class="bg-blue-600 h-2.5 rounded-full" :style="`width: ${completionPercentage}%`"></div>
    </div>
    <div class="mt-2 text-sm text-gray-600">
      {{ answeredStudents }} de {{ totalStudents }} estudiants han contestat
    </div>
    
    <!-- Mostrar estadísticas detalladas por curso si showByCourse es true -->
    <div v-if="showByCourse && sortedCourses.length > 1" class="mt-4 space-y-4">
      <h4 class="font-medium text-gray-700 text-sm">Estat per Cursos:</h4>
      <div v-for="course in sortedCourses" :key="course" class="space-y-2">
        <div class="flex justify-between text-sm">
          <span class="font-medium">{{ course }}</span>
          <span>
            {{ courseStats[course].answered }}/{{ courseStats[course].total }} 
            ({{ courseStats[course].percentage }}%)
          </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="h-2 rounded-full" 
            :class="{
              'bg-green-500': courseStats[course].percentage >= 80,
              'bg-yellow-500': courseStats[course].percentage >= 50 && courseStats[course].percentage < 80,
              'bg-red-500': courseStats[course].percentage < 50
            }"
            :style="`width: ${courseStats[course].percentage}%`">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
