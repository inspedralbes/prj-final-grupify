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
  <div class="space-y-4">
    <!-- Search Input -->
    <div class="w-full">
      <input
        :value="searchQuery"
        type="text"
        placeholder="Cercar per nom del curs..."
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
        @input="$emit('update:searchQuery', $event.target.value)"
      />
    </div>

    <!-- Filters Container -->
    <div class="flex flex-col md:flex-row gap-4">
      <!-- Course Filter -->
      <select
        :value="selectedCourse"
        class="w-full md:w-auto px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
        @change="$emit('update:selectedCourse', $event.target.value)"
      >
        <option value="">Tots els cursos</option>
        <option value="1">1º ESO</option>
        <option value="2">2º ESO</option>
        <option value="3">3º ESO</option>
        <option value="4">4º ESO</option>
        <option value="5">BATXILLERAT</option>
      </select>

      <!-- Division Filter -->
      <select
        :value="selectedDivision"
        class="w-full md:w-auto px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
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
</template>