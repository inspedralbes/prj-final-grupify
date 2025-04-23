<script setup>
import { ref, computed, onMounted } from "vue";
import { useCoursesStore } from "~/stores/coursesStore";
import { useRelationshipsStore } from "~/stores/relationships"; 
import { useStudentsStore } from "~/stores/studentsStore";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import { use } from "echarts/core";
import { CanvasRenderer } from "echarts/renderers";
import { BarChart, RadarChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
} from "echarts/components";
import VChart from "vue-echarts";
import { useRouter } from "vue-router";

// Registrar componentes de ECharts
use([
  CanvasRenderer,
  BarChart,
  RadarChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
]);

const router = useRouter();
const coursesStore = useCoursesStore();
const relationshipsStore = useRelationshipsStore();
const studentsStore = useStudentsStore();

const isLoading = ref(true);
const error = ref(null);
const selectedCourse = ref(null);
const selectedDivision = ref(null);
const allCourses = ref([]);
const highlightedData = ref(null);
const coursesComparison = ref([]);

// Umbral para considerar a un estudiante destacado (en desviaciones estándar)
const threshold = ref(1.5);

// Cargar datos iniciales
onMounted(async () => {
  try {
    await coursesStore.fetchCourses();
    await studentsStore.fetchStudents();
    await relationshipsStore.fetchRelationships();

    allCourses.value = coursesStore.courses;

    if (allCourses.value.length > 0) {
      // Seleccionar el primer curso por defecto
      const firstCourse = allCourses.value[0];
      selectedCourse.value = firstCourse.courseName;
      selectedDivision.value = firstCourse.division.name;

      // Cargar los datos destacados
      loadHighlightedData();

      // Generar datos para la comparativa entre cursos
      await generateCoursesComparison();
    }
  } catch (err) {
    console.error("Error al cargar los datos:", err);
    error.value = "Error al cargar los datos: " + err.message;
  } finally {
    isLoading.value = false;
  }
});

// Cargar datos destacados para el curso seleccionado
const loadHighlightedData = () => {
  if (!selectedCourse.value || !selectedDivision.value) return;

  // Usar la función actualizada que devuelve datos específicos del curso
  highlightedData.value = relationshipsStore.getHighlightedStudentsByCourse(
    selectedCourse.value,
    selectedDivision.value,
    threshold.value
  ).value; // Acceder al valor del computed
};

// Generar datos para la comparativa entre todos los cursos
const generateCoursesComparison = async () => {
  // Usar el nuevo método para obtener la comparación entre cursos
  coursesComparison.value = relationshipsStore.generateCoursesComparison(threshold.value);
};

// Cambiar el curso seleccionado
const changeCourse = () => {
  loadHighlightedData();
};

// Cambiar el umbral
const changeThreshold = async () => {
  loadHighlightedData();
  await generateCoursesComparison();
};

// Opciones para el gráfico de radar
const radarOptions = computed(() => {
  if (!highlightedData.value || !highlightedData.value.hasHighlighted) {
    return {
      title: {
        text: "No hi ha dades disponibles",
      },
    };
  }

  // Alumnos destacados
  const students = highlightedData.value.students;

  // Verificar que students existe y tiene elementos
  if (!students || students.length === 0) {
    return {
      title: {
        text: "No hi ha dades disponibles",
      },
    };
  }

  // Configurar indicadores con manejo de errores
  try {
    const indicators = [
      { name: "Lideratge", max: Math.max(...students.map(s => s.liderazgo || 0)) + 1 },
      { name: "Creativitat", max: Math.max(...students.map(s => s.creatividad || 0)) + 1 },
      { name: "Organització", max: Math.max(...students.map(s => s.organizacion || 0)) + 1 },
    ];

    return {
      title: {
        text: "Perfil d'Habilitats d'Alumnes Destacats",
        left: "center",
        textStyle: {
          color: "#0080C0",
        },
      },
      tooltip: {
        trigger: "item",
      },
      legend: {
        data: students.map(s => `${s.name || ""} ${s.last_name || ""}`),
        bottom: "bottom",
      },
      radar: {
        indicator: indicators,
      },
      series: [
        {
          type: "radar",
          data: students.map((student, index) => {
            const colors = [
              "#FF9800",
              "#9C27B0",
              "#00BCD4",
              "#4CAF50",
              "#F44336",
              "#2196F3",
            ];
            return {
              name: `${student.name || ""} ${student.last_name || ""}`,
              value: [
                student.liderazgo || 0,
                student.creatividad || 0,
                student.organizacion || 0,
              ],
              lineStyle: {
                color: colors[index % colors.length],
              },
              areaStyle: {
                color: colors[index % colors.length],
                opacity: 0.2,
              },
            };
          }),
        },
      ],
    };
  } catch (error) {
    console.error("Error al generar el gráfico de radar:", error);
    return {
      title: {
        text: "Error al generar el gráfico",
      },
    };
  }
});

// Opciones para el gráfico de barras de comparación individual
const barOptions = computed(() => {
  if (
    !highlightedData.value || 
    !highlightedData.value.allStudents || 
    highlightedData.value.allStudents.length === 0
  ) {
    return {
      title: {
        text: "No hi ha dades disponibles",
      },
    };
  }

  try {
    // Todos los alumnos para comparar
    const students = highlightedData.value.allStudents;
    const averages = highlightedData.value.averages || { liderazgo: 0, creatividad: 0, organizacion: 0 };

    // Nombres de estudiantes para el eje X
    const studentNames = students.map(s => `${s.name || ""} ${s.last_name || ""}`);

    return {
      title: {
        text: "Comparativa d'Habilitats",
        left: "center",
        textStyle: {
          color: "#0080C0",
        },
      },
      tooltip: {
        trigger: "axis",
        axisPointer: {
          type: "shadow",
        },
      },
      legend: {
        data: [
          "Lideratge",
          "Creativitat",
          "Organització",
          "Mitjana Lideratge",
          "Mitjana Creativitat",
          "Mitjana Organització",
        ],
        bottom: "bottom",
      },
      grid: {
        left: "3%",
        right: "4%",
        bottom: "15%",
        containLabel: true,
      },
      xAxis: {
        type: "category",
        data: studentNames,
        axisLabel: {
          rotate: 45,
          interval: 0,
          fontSize: 10,
        },
      },
      yAxis: {
        type: "value",
      },
      series: [
        {
          name: "Lideratge",
          type: "bar",
          data: students.map(s => s.liderazgo || 0),
          itemStyle: {
            color: "#FF9800",
          },
        },
        {
          name: "Creativitat",
          type: "bar",
          data: students.map(s => s.creatividad || 0),
          itemStyle: {
            color: "#9C27B0",
          },
        },
        {
          name: "Organització",
          type: "bar",
          data: students.map(s => s.organizacion || 0),
          itemStyle: {
            color: "#00BCD4",
          },
        },
        {
          name: "Mitjana Lideratge",
          type: "line",
          data: students.map(() => averages.liderazgo || 0),
          lineStyle: {
            color: "#FF9800",
            type: "dashed",
          },
        },
        {
          name: "Mitjana Creativitat",
          type: "line",
          data: students.map(() => averages.creatividad || 0),
          lineStyle: {
            color: "#9C27B0",
            type: "dashed",
          },
        },
        {
          name: "Mitjana Organització",
          type: "line",
          data: students.map(() => averages.organizacion || 0),
          lineStyle: {
            color: "#00BCD4",
            type: "dashed",
          },
        },
      ],
    };
  } catch (error) {
    console.error("Error al generar el gráfico de barras:", error);
    return {
      title: {
        text: "Error al generar el gráfico",
      },
    };
  }
});

// Opciones para el gráfico de barras apiladas para comparar todos los cursos
const coursesComparisonOptions = computed(() => {
  if (!coursesComparison.value || coursesComparison.value.length === 0) {
    return {
      title: {
        text: "No hi ha dades disponibles",
      },
    };
  }

  try {
    const courseNames = coursesComparison.value.map(item => item.course);

    return {
      title: {
        text: "Alumnes Destacats per Competència i Curs",
        left: "center",
        textStyle: {
          color: "#0080C0",
        },
      },
      tooltip: {
        trigger: "axis",
        axisPointer: {
          type: "shadow",
        },
        formatter: function (params) {
          try {
            let tooltip = params[0].name + "<br/>";
            let total = 0;

            // Mostrar las medias específicas del curso en el tooltip
            const courseData = coursesComparison.value.find(c => c.course === params[0].name);
            
            params.forEach(param => {
              tooltip += `<span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:${param.color};"></span>`;
              tooltip += `${param.seriesName}: ${param.value}<br/>`;
              total += param.value || 0;
            });

            tooltip += `<span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#333;"></span>`;
            tooltip += `<strong>Total: ${total}</strong><br/>`;
            
            // Añadir información de medias y desviaciones con verificación de nulos
            if (courseData && courseData.averages) {
              const averages = courseData.averages;
              tooltip += `<br/><strong>Medias del curso:</strong><br/>`;
              tooltip += `Liderazgo: ${averages.liderazgo !== undefined ? averages.liderazgo.toFixed(2) : '0.00'}<br/>`;
              tooltip += `Creatividad: ${averages.creatividad !== undefined ? averages.creatividad.toFixed(2) : '0.00'}<br/>`;
              tooltip += `Organización: ${averages.organizacion !== undefined ? averages.organizacion.toFixed(2) : '0.00'}<br/>`;
            }

            return tooltip;
          } catch (error) {
            console.error("Error en el tooltip:", error);
            return "Error al mostrar datos";
          }
        },
      },
      legend: {
        data: ["Lideratge", "Creativitat", "Organització"],
        bottom: "bottom",
      },
      grid: {
        left: "3%",
        right: "4%",
        bottom: "15%",
        containLabel: true,
      },
      xAxis: {
        type: "category",
        data: courseNames,
        axisLabel: {
          rotate: 45,
          interval: 0,
        },
      },
      yAxis: {
        type: "value",
        name: "Nombre d'alumnes destacats",
      },
      series: [
        {
          name: "Lideratge",
          type: "bar",
          stack: "total",
          emphasis: {
            focus: "series",
          },
          data: coursesComparison.value.map(item => item.liderazgo || 0),
          itemStyle: {
            color: "#FF9800",
          },
        },
        {
          name: "Creativitat",
          type: "bar",
          stack: "total",
          emphasis: {
            focus: "series",
          },
          data: coursesComparison.value.map(item => item.creatividad || 0),
          itemStyle: {
            color: "#9C27B0",
          },
        },
        {
          name: "Organització",
          type: "bar",
          stack: "total",
          emphasis: {
            focus: "series",
          },
          data: coursesComparison.value.map(item => item.organizacion || 0),
          itemStyle: {
            color: "#00BCD4",
          },
        },
      ],
    };
  } catch (error) {
    console.error("Error al generar el gráfico comparativo:", error);
    return {
      title: {
        text: "Error al generar el gráfico",
      },
    };
  }
});

const goBack = () => {
  router.push("/professor/sociograma/comparative");
};

// Calcular el porcentaje de votos recibidos
const calculatePercentage = (votes, skill) => {
  if (!highlightedData.value || !highlightedData.value.allStudents) return "0";

  try {
    // Número total de estudiantes en esta clase
    const totalStudents = highlightedData.value.allStudents.length;

    // Total de votos posibles: 2 por estudiante
    const totalPossibleVotes = totalStudents * 2;

    // Si no hay estudiantes, evitar división por cero
    if (totalPossibleVotes === 0) return "0";

    // Calcular porcentaje
    const percentage = ((votes || 0) / totalPossibleVotes) * 100;

    // Devolver con un decimal
    return percentage.toFixed(1);
  } catch (error) {
    console.error("Error al calcular porcentaje:", error);
    return "0";
  }
};

// Nueva función para mostrar información estadística adicional con manejo de nulos
const getStatsInfo = computed(() => {
  if (!highlightedData.value || !highlightedData.value.averages || !highlightedData.value.stdDevs) return null;
  
  try {
    const averages = highlightedData.value.averages || { liderazgo: 0, creatividad: 0, organizacion: 0 };
    const stdDevs = highlightedData.value.stdDevs || { liderazgo: 0, creatividad: 0, organizacion: 0 };
    
    return {
      liderazgo: {
        mean: (averages.liderazgo || 0).toFixed(2),
        stdDev: (stdDevs.liderazgo || 0).toFixed(2),
        threshold: ((averages.liderazgo || 0) + threshold.value * (stdDevs.liderazgo || 0)).toFixed(2)
      },
      creatividad: {
        mean: (averages.creatividad || 0).toFixed(2),
        stdDev: (stdDevs.creatividad || 0).toFixed(2),
        threshold: ((averages.creatividad || 0) + threshold.value * (stdDevs.creatividad || 0)).toFixed(2)
      },
      organizacion: {
        mean: (averages.organizacion || 0).toFixed(2),
        stdDev: (stdDevs.organizacion || 0).toFixed(2),
        threshold: ((averages.organizacion || 0) + threshold.value * (stdDevs.organizacion || 0)).toFixed(2)
      }
    };
  } catch (error) {
    console.error("Error al calcular estadísticas:", error);
    return null;
  }
});
</script>

<template>
  <div class="min-h-screen bg-white">
    <DashboardNavTeacher class="w-full" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Encabezado con botón de regreso -->
      <div class="flex items-center justify-between mb-6">
        <button
          @click="goBack"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#0080C0] hover:bg-[#006699] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0080C0]"
        >
          <svg
            class="w-5 h-5 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
          Tornar
        </button>

        <h1 class="text-3xl font-semibold text-[#0080C0] text-center">
          ALUMNES DESTACATS
        </h1>

        <div class="w-[100px]"></div>
        <!-- Espacio para equilibrar el layout -->
      </div>

      <p class="text-center text-gray-600 mb-6">
        Anàlisi d'alumnes amb competències significativament superiors a la
        mitjana de la seva classe
      </p>

      <!-- Estado de carga -->
      <div
        v-if="isLoading"
        class="bg-white rounded-lg shadow-md p-8 flex flex-col items-center justify-center min-h-[300px]"
      >
        <div class="relative w-16 h-16">
          <div
            class="absolute inset-0 rounded-full border-4 border-[#0080C0] border-t-transparent animate-spin"
          ></div>
        </div>
        <p class="mt-4 text-base text-gray-600">Carregant dades...</p>
      </div>

      <!-- Estado de error -->
      <div
        v-else-if="error"
        class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm flex items-center"
      >
        <svg
          class="w-6 h-6 text-red-500 mr-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-red-700">{{ error }}</p>
      </div>

      <!-- Contenido principal -->
      <div v-else class="space-y-6">
        <!-- Selector de curso y umbral -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Selector de curso -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Seleccionar curs</label
              >
              <div class="grid grid-cols-2 gap-2">
                <select
                  v-model="selectedCourse"
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#0080C0] focus:border-[#0080C0] sm:text-sm rounded-md"
                >
                  <option
                    v-for="course in allCourses"
                    :key="course.id"
                    :value="course.courseName"
                  >
                    {{ course.courseName }}
                  </option>
                </select>

                <select
                  v-model="selectedDivision"
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#0080C0] focus:border-[#0080C0] sm:text-sm rounded-md"
                >
                  <option
                    v-for="course in allCourses.filter(
                      c => c.courseName === selectedCourse
                    )"
                    :key="course.id"
                    :value="course.division.name"
                  >
                    {{ course.division.name }}
                  </option>
                </select>
              </div>
              <button
                @click="changeCourse"
                class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#0080C0] hover:bg-[#006699] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0080C0]"
              >
                Carregar dades
              </button>
            </div>

            <!-- Selector de umbral -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Llindar de destacat (desviacions estàndard): {{ threshold }}
              </label>
              <input
                type="range"
                v-model="threshold"
                min="0.5"
                max="3"
                step="0.1"
                @change="changeThreshold"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              />
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>0.5 (Més inclusiu)</span>
                <span>3 (Més selectiu)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Gráfico comparativo de todos los cursos -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">
            Comparativa de Competències per Curs
          </h2>

          <div class="h-96">
            <v-chart
              class="w-full h-full"
              :option="coursesComparisonOptions"
              autoresize
            />
          </div>

          <!-- Panel de información estadística -->
          <div class="mt-4 bg-blue-50 p-4 rounded-md">
            <p class="text-sm text-blue-800 mb-2">
              <span class="font-medium">Nota:</span> Aquest gràfic mostra el
              nombre d'alumnes destacats per competència en cada curs. Un alumne
              pot destacar en més d'una competència, per això els colors es
              mostren apilats.
            </p>
            <p class="text-sm text-blue-800">
              <span class="font-medium">Important:</span> Cada curs té la seva pròpia mitjana i desviació estàndard. 
              Passa el cursor sobre cada barra per veure aquestes estadístiques específiques.
            </p>
          </div>
        </div>

        <!-- Información del curso seleccionado -->
        <div v-if="highlightedData" class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">
            {{ highlightedData.courseName }} {{ highlightedData.divisionName }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div
              class="bg-gray-50 rounded-lg p-4 text-center border-t-4 border-[#FF9800]"
            >
              <h3 class="font-medium text-gray-800">Mitjana Lideratge</h3>
              <p class="text-3xl font-bold mt-2 text-[#FF9800]">
                {{ highlightedData.averages && highlightedData.averages.liderazgo !== undefined ? 
                   highlightedData.averages.liderazgo.toFixed(2) : '0.00' }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                Desv. Estàndard: {{ highlightedData.stdDevs && highlightedData.stdDevs.liderazgo !== undefined ? 
                                    highlightedData.stdDevs.liderazgo.toFixed(2) : '0.00' }}
              </p>
            </div>
            <div
              class="bg-gray-50 rounded-lg p-4 text-center border-t-4 border-[#9C27B0]"
            >
              <h3 class="font-medium text-gray-800">Mitjana Creativitat</h3>
              <p class="text-3xl font-bold mt-2 text-[#9C27B0]">
                {{ highlightedData.averages && highlightedData.averages.creatividad !== undefined ? 
                   highlightedData.averages.creatividad.toFixed(2) : '0.00' }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                Desv. Estàndard: {{ highlightedData.stdDevs && highlightedData.stdDevs.creatividad !== undefined ? 
                                    highlightedData.stdDevs.creatividad.toFixed(2) : '0.00' }}
              </p>
            </div>
            <div
              class="bg-gray-50 rounded-lg p-4 text-center border-t-4 border-[#00BCD4]"
            >
              <h3 class="font-medium text-gray-800">Mitjana Organització</h3>
              <p class="text-3xl font-bold mt-2 text-[#00BCD4]">
                {{ highlightedData.averages && highlightedData.averages.organizacion !== undefined ? 
                   highlightedData.averages.organizacion.toFixed(2) : '0.00' }}
              </p>
              <p class="text-xs text-gray-500 mt-1">
                Desv. Estàndard: {{ highlightedData.stdDevs && highlightedData.stdDevs.organizacion !== undefined ? 
                                    highlightedData.stdDevs.organizacion.toFixed(2) : '0.00' }}
              </p>
            </div>
          </div>

          <!-- Información sobre el umbral -->
          <div class="bg-blue-50 p-4 rounded-md">
            <p class="text-sm text-blue-800">
              <span class="font-medium">Nota:</span> Es considera que un alumne
              destaca en una competència quan la seva puntuació està
              {{ threshold }} o més desviacions estàndard per sobre de la
              mitjana de classe.
            </p>
            <div v-if="getStatsInfo" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
              <div>
                <p class="text-xs text-blue-800">
                  <span class="font-medium">Llindar Lideratge:</span> 
                  {{ getStatsInfo.liderazgo.threshold }}
                </p>
              </div>
              <div>
                <p class="text-xs text-blue-800">
                  <span class="font-medium">Llindar Creativitat:</span> 
                  {{ getStatsInfo.creatividad.threshold }}
                </p>
              </div>
              <div>
                <p class="text-xs text-blue-800">
                  <span class="font-medium">Llindar Organització:</span> 
                  {{ getStatsInfo.organizacion.threshold }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Alumnos destacados -->
        <div
          v-if="highlightedData && highlightedData.hasHighlighted"
          class="bg-white rounded-lg shadow-md p-6"
        >
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">
            Alumnes Destacats ({{ highlightedData.students ? highlightedData.students.length : 0 }})
          </h2>
<!-- Gráfico de radar para alumnos destacados -->
<div class="h-96 mb-6">
            <v-chart class="w-full h-full" :option="radarOptions" autoresize />
          </div>

          <!-- Lista de alumnos destacados -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="student in highlightedData.students"
              :key="student.id"
              class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <div
                    class="w-12 h-12 rounded-full bg-[#0080C0] flex items-center justify-center text-white font-bold"
                  >
                    {{ student.name ? student.name.charAt(0) : '' }}{{ student.last_name ? student.last_name.charAt(0) : '' }}
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">
                    {{ student.name || '' }} {{ student.last_name || '' }}
                  </h3>

                  <!-- Habilidades destacadas con porcentajes -->
                  <div class="mt-2 flex flex-wrap gap-2">
                    <span
                      v-if="student.highlightedSkills && student.highlightedSkills.includes('liderazgo')"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                    >
                      Lideratge: {{ student.liderazgo || 0 }} ({{
                        calculatePercentage(student.liderazgo, "liderazgo")
                      }}%)
                    </span>
                    <span
                      v-if="student.highlightedSkills && student.highlightedSkills.includes('creatividad')"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                    >
                      Creativitat: {{ student.creatividad || 0 }} ({{
                        calculatePercentage(student.creatividad, "creatividad")
                      }}%)
                    </span>
                    <span
                      v-if="student.highlightedSkills && student.highlightedSkills.includes('organizacion')"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800"
                    >
                      Organització: {{ student.organizacion || 0 }} ({{
                        calculatePercentage(
                          student.organizacion,
                          "organizacion"
                        )
                      }}%)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sin alumnos destacados -->
        <div
          v-else-if="highlightedData && !highlightedData.hasHighlighted"
          class="bg-white rounded-lg shadow-md p-6 text-center"
        >
          <svg
            class="w-16 h-16 text-gray-400 mx-auto mb-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h3 class="text-lg font-medium text-gray-900">
            No hi ha alumnes destacats
          </h3>
          <p class="mt-2 text-gray-600">
            No s'han trobat alumnes que superin el llindar de
            {{ threshold }} desviacions estàndard. Prova a reduir el llindar o
            seleccionar un altre curs.
          </p>
        </div>

        <!-- Gráfico comparativo de todos los alumnos -->
        <div
          v-if="highlightedData && highlightedData.allStudents && highlightedData.allStudents.length > 0"
          class="bg-white rounded-lg shadow-md p-6"
        >
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">
            Comparativa de Tots els Alumnes
          </h2>

          <div class="h-96">
            <v-chart class="w-full h-full" :option="barOptions" autoresize />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>