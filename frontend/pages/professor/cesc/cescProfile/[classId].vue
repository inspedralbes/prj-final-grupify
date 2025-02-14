<template>
  <div class="min-h-screen bg-white">
    <DashboardNavTeacher class="w-full" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-semibold text-[#0080C0] text-center">
          RESULTATS CESC
        </h1>
      </div>
      
      <div v-if="isLoading" class="bg-white rounded-lg shadow-md p-8 flex flex-col items-center justify-center min-h-[300px]">
        Cargando...
      </div>
      
      <div v-else-if="error">{{ error }}</div>

      <div v-else>
        <p v-if="filtered.length === 0" class="text-center text-gray-600">
          No hi ha dades filtrades per a aquest curs i divisió.
        </p>

        <!-- Tabla de resultados agrupados -->
        <div v-if="groupedResults.length > 0" class="mt-4 overflow-x-auto">
          <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm">
            <thead class="bg-gradient-to-r from-blue-50 to-purple-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider border-b">
                  Nom Complet
                </th>
                <th v-for="(tag, index) in uniqueTags" :key="tag" 
                    class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider border-b"
                    :class="getTagHeaderColor(index)">
                  {{ tag }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="student in groupedResults" 
                  :key="student.fullName"
                  class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap font-medium">
                  {{ student.fullName }}
                </td>
                <td v-for="(tag, index) in uniqueTags" :key="tag"
                    class="px-6 py-4 whitespace-nowrap text-center">
                  <span v-if="student.tags[tag]" 
                        class="px-3 py-1 rounded-full"
                        :class="getTagBadgeClasses(index)">
                    {{ student.tags[tag] }}
                  </span>
                  <span v-else>-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useCoursesStore } from "~/stores/coursesStore";
import { useResultatCescStore } from "~/stores/resultatsCescStore";
import { useStudentsStore } from "~/stores/studentsStore";
import { useRoute } from "vue-router";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);
const coursesStore = useCoursesStore();
const studentsStore = useStudentsStore();
const resultatsCescStore = useResultatCescStore();

// Color combinations for tags
const tagColors = [
  { bg: 'bg-green-100', text: 'text-green-800' },
  { bg: 'bg-blue-100', text: 'text-blue-800' },
  { bg: 'bg-red-100', text: 'text-red-800' },
  { bg: 'bg-purple-100', text: 'text-purple-800' },
  { bg: 'bg-yellow-100', text: 'text-yellow-800' },
  { bg: 'bg-pink-100', text: 'text-pink-800' },
  { bg: 'bg-indigo-100', text: 'text-indigo-800' },
  { bg: 'bg-orange-100', text: 'text-orange-800' }
];

// Function to get tag header color
const getTagHeaderColor = (index) => {
  const colorIndex = index % tagColors.length;
  return tagColors[colorIndex].text;
};

// Function to get tag badge classes
const getTagBadgeClasses = (index) => {
  const colorIndex = index % tagColors.length;
  return `${tagColors[colorIndex].bg} ${tagColors[colorIndex].text}`;
};

classId.value = route.params.classId;

// Initialize component
onMounted(async () => {
  try {
    if (!classId.value) throw new Error("classId no trobat");

    await coursesStore.fetchCourses();
    course.value = coursesStore.courses.find(c => c.classId == classId.value);
    if (!course.value) throw new Error("Curs no trobat");

    await studentsStore.fetchStudents();
    students.value = studentsStore.students.filter(
      student =>
        student.course === course.value.courseName &&
        student.division === course.value.division.name
    );
    await resultatsCescStore.fetchResults();
  } catch (err) {
    console.error("Error en carregar les dades:", err);
    error.value = "Error en carregar les dades";
  } finally {
    isLoading.value = false;
  }
});

// Filtrar datos en base al curso y la división
const filtered = computed(() => {
  if (!course.value) return [];

  return resultatsCescStore.getCescByCourseAndDivision(
    course.value.courseName,
    course.value.division.name
  );
});

// Obtener tags únicos
const uniqueTags = computed(() => {
  const tags = new Set(filtered.value.map(item => item.tag_name));
  return Array.from(tags);
});

// Agrupar resultados por estudiante
const groupedResults = computed(() => {
  const groupedByStudent = {};
  
  filtered.value.forEach(item => {
    const fullName = `${item.peer_name} ${item.peer_last_name}`;
    
    if (!groupedByStudent[fullName]) {
      groupedByStudent[fullName] = {
        fullName,
        tags: {}
      };
    }
    
    groupedByStudent[fullName].tags[item.tag_name] = item.vote_count;
  });
  
  return Object.values(groupedByStudent);
});
</script>