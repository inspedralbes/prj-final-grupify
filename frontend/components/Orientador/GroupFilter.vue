<template>
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-col sm:flex-row flex-wrap gap-4">
      <!-- Input de búsqueda -->
      <div class="flex-1 min-w-[200px]">
        <input
          :value="searchQuery"
          type="text"
          placeholder="Buscar per nom o descripció del grup..."
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @input="$emit('update:searchQuery', $event.target.value)"
        />
      </div>

      <!-- Filtros: Curs y Divisió -->
      <div class="flex flex-col sm:flex-row gap-4">
        <select
          :value="selectedCourse"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @change="$emit('update:selectedCourse', $event.target.value)"
        >
          <option value="all">Tots els cursos</option>
          <option value="1">1º ESO</option>
          <option value="2">2º ESO</option>
          <option value="3">3º ESO</option>
          <option value="4">4º ESO</option>
          <option value="1 BATXILLERAT">1º BATXILLERAT</option>
          <option value="2 BATXILLERAT">2º BATXILLERAT</option>
        </select>
        <select
          :value="selectedDivision"
          class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          @change="$emit('update:selectedDivision', $event.target.value)"
        >
          <option value="all">Totes les divisions</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
          <option value="E">E</option>
        </select>
      </div>
    </div>
  </div>
</template>

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
