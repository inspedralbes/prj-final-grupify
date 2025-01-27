<script setup>
defineProps({
  searchQuery: String,
  selectedCourse: String,
  selectedDivision: String,
});

defineEmits([
  "update:searchQuery",
  "update:selectedCourse",
  "update:selectedDivision",
]);
</script>

<template>
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-wrap gap-4">
      <div class="flex-1 min-w-[200px]">
        <input
          :value="searchQuery"
          type="text"
          placeholder="Cercar per nom del curs..."
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @input="$emit('update:searchQuery', $event.target.value)"
        />
      </div>
      <div class="flex space-x-4">
        <!-- Filtro de curso -->
        <select
          :value="selectedCourse"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @change="$emit('update:selectedCourse', $event.target.value)"
        >
          <option value="">Tots els cursos</option>
          <option value="1">1º ESO</option>
          <option value="2">2º ESO</option>
          <option value="3">3º ESO</option>
          <option value="4">4º ESO</option>
          <option value="5">BATXILLERAT</option>
          <option value="6">BATXILLERAT</option>
        </select>
        <!-- Filtro de división -->
        <select
          :value="selectedDivision"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @change="$emit('update:selectedDivision', $event.target.value)"
        >
          <option value="">Totes les classes</option>
          <option value="3">A</option>
          <option value="4">B</option>
          <option value="5">C</option>
          <option value="6">D</option>
          <option value="5">E</option>
          <option value="1">1</option>
          <option value="2">2</option>
        </select>
      </div>
    </div>
  </div>
</template>

