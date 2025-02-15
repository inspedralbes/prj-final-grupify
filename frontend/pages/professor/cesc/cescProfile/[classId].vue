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

        <!-- Gráfico ECharts -->
        <div v-if="groupedResults.length > 0" class="mt-8 mb-8">
          <v-chart class="w-full h-[400px]" :option="chartOption" autoresize />
        </div>

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
                  :class="{
                    'hover:bg-gray-50 transition-colors': !isHighlightedStudent(student),
                    'bg-red-50 hover:bg-red-100 transition-colors': isHighlightedStudent(student, 'Agressiu'),
                    'bg-yellow-50 hover:bg-yellow-100 transition-colors': isHighlightedStudent(student, 'Víctima')
                  }">
                <td class="px-6 py-4 whitespace-nowrap font-medium">
                  <span :class="{
                    'text-red-700': isHighlightedStudent(student, 'Agressiu'),
                    'text-yellow-700': isHighlightedStudent(student, 'Víctima')
                  }">
                    {{ student.fullName }}
                    <span v-if="isHighlightedStudent(student, 'Agressiu')" 
                          class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                      Agressiu
                    </span>
                    <span v-if="isHighlightedStudent(student, 'Víctima')" 
                          class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                      Víctima
                    </span>
                  </span>
                </td>
                <td v-for="(tag, index) in uniqueTags" :key="tag"
                    class="px-6 py-4 whitespace-nowrap text-center">
                  <span v-if="student.tags[tag]" 
                        class="px-3 py-1 rounded-full"
                        :class="[
                          getTagBadgeClasses(index),
                          { 'font-bold': isHighlightedStudent(student) && (tag === 'Agressiu' || tag === 'Víctima') }
                        ]">
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
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components';

// Registrar componentes de ECharts
use([CanvasRenderer, BarChart, GridComponent, TooltipComponent, LegendComponent]);

// Mapeo de colores "base" para cada etiqueta
const baseColors = {
  'Popular': '#22c55e',
  'Rebutjat': '#3b82f6',
  'Agressiu': '#dc2626',
  'Prosocial': '#8b5cf6',
  'Víctima': '#f59e0b'
};

const chartOption = computed(() => {
  const maxAggressive = maxAggressiveScore.value;
  const maxVictim = maxVictimScore.value;

  const series = uniqueTags.value.map(tag => {
    // Color que quieres ver en la leyenda (ícono)
    const baseColor = baseColors[tag] || '#64748b';

    return {
      name: tag,
      type: 'bar',
      stack: 'total',

      // 1) Este color se usa para la leyenda
      color: baseColor,

      // 2) Este color se usa para las barras, punto por punto
      itemStyle: {
        color: (params) => {
          const student = groupedResults.value[params.dataIndex];
          const value = student.tags[tag] || 0;

          // Ejemplo: colores especiales si es el máximo
          if (tag === 'Agressiu' && value === maxAggressive) {
            return '#ef4444'; // Rojo
          } else if (tag === 'Víctima' && value === maxVictim) {
            return '#eab308'; // Amarillo
          }
          // Por defecto, el color "normal"
          return baseColor;
        }
      },

      data: groupedResults.value.map(student => student.tags[tag] || 0)
    };
  });

  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: { type: 'shadow' }
    },
    legend: {
      data: uniqueTags.value,
      // Si quieres también cambiar la forma del ícono:
      // icon: 'circle' | 'rect' | 'roundRect' | 'triangle' | ...
    },
    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
    xAxis: {
      type: 'category',
      data: groupedResults.value.map(student => student.fullName)
    },
    yAxis: { type: 'value' },
    series,
    emphasis: { focus: 'series' }
  };
});

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

// Computed properties para encontrar los máximos valores
const maxAggressiveScore = computed(() => {
  let max = 0;
  groupedResults.value.forEach(student => {
    const score = student.tags['Agressiu'] || 0;
    if (score > max) max = score;
  });
  return max;
});

const maxVictimScore = computed(() => {
  let max = 0;
  groupedResults.value.forEach(student => {
    const score = student.tags['Víctima'] || 0;
    if (score > max) max = score;
  });
  return max;
});

// Function to check if a student should be highlighted
const isHighlightedStudent = (student, type = null) => {
  const aggressiveScore = student.tags['Agressiu'] || 0;
  const victimScore = student.tags['Víctima'] || 0;

  if (type === 'Agressiu') {
    return aggressiveScore === maxAggressiveScore.value && aggressiveScore > 0;
  } else if (type === 'Víctima') {
    return victimScore === maxVictimScore.value && victimScore > 0;
  }
  
  return (aggressiveScore === maxAggressiveScore.value && aggressiveScore > 0) ||
         (victimScore === maxVictimScore.value && victimScore > 0);
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

