<script setup>
import { ref, onMounted, computed } from 'vue';
import { useCoursesStore } from "~/stores/coursesStore";
import { useResultatCescStore } from "~/stores/resultatsCescStore";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import RelationsCesc from "@/components/Teacher/CescComponents/RelationsCesc.vue";
import { useRoute } from 'vue-router';

// Initialize stores
const coursesStore = useCoursesStore();
const resultatStore = useResultatCescStore();
const route = useRoute();

// Get class ID from route parameter
const classId = computed(() => route.params.classId);

// State for current course and division
const currentCourse = ref(null);
const currentDivision = ref(null);

// Computed property for tag names
const tags = computed(() => {
  const uniqueTags = new Set();
  resultatStore.results.forEach(result => {
    if (result.tag) {
      uniqueTags.add({
        id: result.tag_id,
        name: result.tag.name
      });
    }
  });
  return Array.from(uniqueTags);
});

// Computed property for formatted evaluations data
const evaluationsData = computed(() => {
  if (!resultatStore.filteredResults.length) return [];

  return resultatStore.filteredResults.map(result => {
    const tagScores = {};
    result.tags.forEach(tag => {
      tagScores[tag.tag_id] = tag.vote_count;
    });

    return {
      peer_id: result.peer_id,
      peer_name: `${result.peer_name} ${result.peer_last_name}`,
      tag_scores: tagScores
    };
  });
});

// Initialize component
onMounted(async () => {
  // Load necessary data
  await Promise.all([
    coursesStore.fetchCourses(),
    resultatStore.initialize()
  ]);

  // Get course and division from classId
  const [courseId, divisionId] = classId.value.split('-').map(Number);
  currentCourse.value = coursesStore.courses.find(c => c.id === courseId);
  currentDivision.value = currentCourse.value?.divisions.find(d => d.id === divisionId);

  if (currentCourse.value && currentDivision.value) {
    resultatStore.setCurrentCourseAndDivision(
      currentCourse.value.name,
      courseId,
      currentDivision.value.name,
      divisionId
    );
  }
});
</script>

<template>
  <div class="p-6">
    <DashboardNavTeacher />
    
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold mb-2">
        Evaluación CESC: {{ currentCourse?.name }} - {{ currentDivision?.name }}
      </h1>
      <p class="text-gray-600">
        Resultados por alumno y categoría
      </p>
    </div>

    <!-- Visualización de relaciones -->
    <div v-if="evaluationsData.length > 0" class="mb-8">
      <RelationsCesc 
        :evaluations="evaluationsData"
        :tags="tags"
      />
    </div>

    <!-- Results Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Alumno
            </th>
            <th v-for="tag in tags" 
                :key="tag.id"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ tag.name }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Total Votos
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="result in resultatStore.filteredResults" :key="result.peer_id">
            <td class="px-6 py-4 whitespace-nowrap">
              {{ result.peer_name }} {{ result.peer_last_name }}
            </td>
            <td v-for="tag in tags" 
                :key="tag.id"
                class="px-6 py-4 whitespace-nowrap text-center"
                :class="getScoreClass(result.tags.find(t => t.tag_id === tag.id)?.vote_count || 0)">
              {{ result.tags.find(t => t.tag_id === tag.id)?.vote_count || 0 }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center font-medium">
              {{ result.tags.reduce((sum, tag) => sum + tag.vote_count, 0) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="!evaluationsData.length" class="text-center py-8 text-gray-500">
      No hay datos disponibles para esta clase
    </div>
  </div>
</template>

<script>
// Utility function for score classes
function getScoreClass(score) {
  if (score > 5) return 'bg-red-50';
  if (score > 3) return 'bg-yellow-50';
  if (score > 0) return 'bg-green-50';
  return '';
}
</script>