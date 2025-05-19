<template>
  <div class="bg-white rounded-lg p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input
          :value="searchQuery"
          type="text"
          placeholder="Buscar per nom o email..."
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @input="$emit('update:searchQuery', $event.target.value)"
        />
      </div>

      <!-- Si el profesor tiene cursos asignados, mostrar un selector personalizado -->
      <div v-if="teacherCourseDivisions && teacherCourseDivisions.length > 0" class="flex-1">
        <select 
          :value="selectedClassGroup"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @change="handleClassGroupSelection($event.target.value)"
        >
          <option value="all">Tots els meus cursos i divisions</option>
          <option 
            v-for="(cd, index) in teacherCourseDivisions" 
            :key="index" 
            :value="`${cd.course_name}-${cd.division_name}`"
          >
            {{ cd.course_name }} {{ cd.division_name }}
          </option>
        </select>
      </div>
      
      <!-- Si no hay asignaciones específicas, mostrar los filtros tradicionales -->
      <div v-else class="flex flex-col sm:flex-row gap-4">
        <select
          :value="selectedCourse"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @change="$emit('update:selectedCourse', $event.target.value)"
        >
          <option value="all">Tots els cursos</option>
          <option value="1 ESO">1º ESO</option>
          <option value="2 ESO">2º ESO</option>
          <option value="3 ESO">3º ESO</option>
          <option value="4 ESO">4º ESO</option>
          <option value="BATXILLERAT">BATXILLERAT</option>
        </select>
        <select
          :value="selectedDivision"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @change="$emit('update:selectedDivision', $event.target.value)"
        >
          <option value="all">Totes les classes</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
          <option value="E">E</option>
          <option value="1">1</option>
          <option value="2">2</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  searchQuery: String,
  selectedCourse: String,
  selectedDivision: String,
  teacherCourseDivisions: Array,
});

const emit = defineEmits([
  "update:searchQuery",
  "update:selectedCourse",
  "update:selectedDivision",
]);

// Valor para el selector combinado de curso-división
const selectedClassGroup = computed(() => {
  if (props.selectedCourse === 'all' || props.selectedDivision === 'all') {
    return 'all';
  }
  return `${props.selectedCourse}-${props.selectedDivision}`;
});

// Manejar la selección del grupo de clase
const handleClassGroupSelection = (value) => {
  if (value === 'all') {
    // Si se selecciona "Todos", resetear ambos filtros
    emit('update:selectedCourse', 'all');
    emit('update:selectedDivision', 'all');
  } else {
    // Si se selecciona un grupo específico, extraer curso y división
    const [course, division] = value.split('-');
    emit('update:selectedCourse', course);
    emit('update:selectedDivision', division);
  }
};
</script>
