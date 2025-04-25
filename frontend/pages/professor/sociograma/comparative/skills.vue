<script setup>
import { ref, computed, onMounted } from "vue";
import { useCoursesStore } from "~/stores/coursesStore";
import { useRelationshipsStore } from "~/stores/relationships"; 
import { useStudentsStore } from "~/stores/studentsStore";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import CourseFilter from "@/components/Teacher/SociogramaComponents/CourseFilterComponent.vue"; // Importar el nuevo componente
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
const selectedCourseAndDivision = ref(null);
const allCourses = ref([]);
const highlightedData = ref(null);
const coursesComparison = ref([]);

// Umbral para considerar a un estudiante destacado (en desviaciones estándar)
const threshold = ref(1.5);

// Extraer curso y división del valor seleccionado
const parseCourseAndDivision = (value) => {
  if (!value) return { courseName: null, divisionName: null };
  
  const [courseName, divisionName] = value.split('_');
  return { courseName, divisionName };
};

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
      selectedCourseAndDivision.value = `${firstCourse.courseName}_${firstCourse.division.name}`;

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
  if (!selectedCourseAndDivision.value) return;
  
  const { courseName, divisionName } = parseCourseAndDivision(selectedCourseAndDivision.value);
  if (!courseName || !divisionName) return;

  // Usar la función actualizada que devuelve datos específicos del curso
  highlightedData.value = relationshipsStore.getHighlightedStudentsByCourse(
    courseName,
    divisionName,
    threshold.value
  ).value; // Acceder al valor del computed
};

// Generar datos para la comparativa entre todos los cursos
const generateCoursesComparison = async () => {
  // Usar el nuevo método para obtener la comparación entre cursos
  coursesComparison.value = relationshipsStore.generateCoursesComparison(threshold.value);
};

// Funciones de actualización que serán llamadas desde el componente hijo
const updateCourseAndDivision = (value) => {
  selectedCourseAndDivision.value = value;
};

const updateThreshold = (value) => {
  threshold.value = value;
};

// Cargar datos cuando cambian los filtros
const loadData = async () => {
  loadHighlightedData();
  await generateCoursesComparison();
};

// Opciones para el gráfico de radar mejorado con burbujas
const bubbleRadarOptions = computed(() => {
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

  // Configurar radar con burbujas
  try {
    // Encontrar el valor máximo para cada habilidad
    const maxLiderazgo = Math.max(...students.map(s => s.liderazgo || 0));
    const maxCreatividad = Math.max(...students.map(s => s.creatividad || 0));
    const maxOrganizacion = Math.max(...students.map(s => s.organizacion || 0));

    // Calcular el máximo absoluto para usar como escala en el radar
    const maxValue = Math.max(maxLiderazgo, maxCreatividad, maxOrganizacion) + 1;

    // Colores para cada estudiante
    const colors = [
      "#FF9800", // Naranja
      "#9C27B0", // Púrpura
      "#00BCD4", // Azul claro
      "#4CAF50", // Verde
      "#F44336", // Rojo
      "#2196F3", // Azul
      "#FFEB3B", // Amarillo
      "#795548", // Marrón
      "#607D8B", // Gris azulado
      "#E91E63", // Rosa
    ];

    // Crear serie de datos para cada estudiante
    const series = students.map((student, index) => {
      const color = colors[index % colors.length];
      
      return {
        name: `${student.name || ""} ${student.last_name || ""}`,
        type: 'radar',
        symbol: 'circle',
        symbolSize: 16,
        lineStyle: {
          width: 0 // Sin líneas entre puntos
        },
        data: [
          {
            value: [student.liderazgo || 0, student.creatividad || 0, student.organizacion || 0],
            name: `${student.name || ""} ${student.last_name || ""}`,
            label: {
              show: true,
              formatter: function() {
                return student.name.charAt(0) + student.last_name.charAt(0);
              },
              color: '#fff',
              fontSize: 10
            },
            // Personalización del punto
            itemStyle: {
              color: color,
              borderColor: '#fff',
              borderWidth: 2,
              shadowBlur: 5,
              shadowColor: 'rgba(0,0,0,0.3)'
            }
          }
        ],
        tooltip: {
          formatter: function() {
            return `<strong>${student.name} ${student.last_name}</strong><br/>
                    Lideratge: ${student.liderazgo}<br/>
                    Creativitat: ${student.creatividad}<br/>
                    Organització: ${student.organizacion}<br/>
                    Total: ${student.total}`;
          }
        }
      };
    });

    return {
      title: {
        text: "Perfil d'Habilitats d'Alumnes Destacats",
        left: "center",
        textStyle: {
          color: "#0080C0",
        },
      },
      tooltip: {
        trigger: "item"
      },
      legend: {
        data: students.map(s => `${s.name || ""} ${s.last_name || ""}`),
        bottom: "bottom",
        type: "scroll",
        itemWidth: 25,
        itemHeight: 14,
        textStyle: {
          fontSize: 12
        }
      },
      radar: {
        indicator: [
          { name: 'Lideratge', max: maxValue, axisLabel: { show: true, color: '#FF9800' } },
          { name: 'Creativitat', max: maxValue, axisLabel: { show: true, color: '#9C27B0' } },
          { name: 'Organització', max: maxValue, axisLabel: { show: true, color: '#00BCD4' } }
        ],
        center: ['50%', '50%'],
        radius: '65%',
        axisName: {
          formatter: '{value}',
          textStyle: {
            fontSize: 14,
            fontWeight: 'bold'
          }
        },
        splitArea: {
          areaStyle: {
            opacity: 0.1,
            color: ['#eee']
          }
        },
        axisLine: {
          lineStyle: {
            color: '#ddd'
          }
        },
        splitLine: {
          lineStyle: {
            color: '#ddd'
          }
        }
      },
      series: series
    };
  } catch (error) {
    console.error("Error al generar el gráfico de radar con burbujas:", error);
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
        
            
            // Añadir nuevas métricas
            if (courseData && courseData.coeficienteDeVariacion) {
              const coef = courseData.coeficienteDeVariacion;
              tooltip += `<br/><strong>Coeficiente de Variación:</strong><br/>`;
              tooltip += `Liderazgo: ${coef.liderazgo !== undefined ? coef.liderazgo.toFixed(2) : '0.00'}<br/>`;
              tooltip += `Creatividad: ${coef.creatividad !== undefined ? coef.creatividad.toFixed(2) : '0.00'}<br/>`;
              tooltip += `Organización: ${coef.organizacion !== undefined ? coef.organizacion.toFixed(2) : '0.00'}<br/>`;
            }
            
            if (courseData && courseData.indiceConcentracion) {
              const indice = courseData.indiceConcentracion;
              tooltip += `<br/><strong>Índice de Concentración:</strong><br/>`;
              tooltip += `Liderazgo: ${indice.liderazgo !== undefined ? indice.liderazgo.toFixed(3) : '0.000'}<br/>`;
              tooltip += `Creatividad: ${indice.creatividad !== undefined ? indice.creatividad.toFixed(3) : '0.000'}<br/>`;
              tooltip += `Organización: ${indice.organizacion !== undefined ? indice.organizacion.toFixed(3) : '0.000'}<br/>`;
            }

            return tooltip;
          } catch (error) {
            console.error("Error en el tooltip:", error);
            return "Error al mostrar datos";
          }
        },
      },
      legend: {
        data: [
          "Lideratge", 
          "Creativitat", 
          "Organització", 
          "Mitjana Lideratge", 
          "Mitjana Creativitat", 
          "Mitjana Organització"
        ],
        bottom: "bottom",
        type: "scroll",
        itemWidth: 25,
        itemHeight: 14,
        textStyle: {
          fontSize: 12
        }
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
      },
      series: [
        // Series de barras apiladas
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
        
        // Líneas para las medias de cada competencia
        {
          name: "Mitjana Lideratge",
          type: "line",
          yAxisIndex: 0,
          symbol: "circle",
          symbolSize: 6,
          sampling: "average",
          itemStyle: {
            color: "#FF9800"
          },
          lineStyle: {
            color: "#FF9800",
            width: 2,
            type: "dashed"
          },
          data: coursesComparison.value.map(item => 
            item.mediasConvencionales && item.mediasConvencionales.liderazgo !== undefined ? 
            item.mediasConvencionales.liderazgo : 0
          )
        },
        {
          name: "Mitjana Creativitat",
          type: "line",
          yAxisIndex: 0,
          symbol: "circle",
          symbolSize: 6,
          sampling: "average",
          itemStyle: {
            color: "#9C27B0"
          },
          lineStyle: {
            color: "#9C27B0",
            width: 2,
            type: "dashed"
          },
          data: coursesComparison.value.map(item => 
            item.mediasConvencionales && item.mediasConvencionales.creatividad !== undefined ? 
            item.mediasConvencionales.creatividad : 0
          )
        },
        {
          name: "Mitjana Organització",
          type: "line",
          yAxisIndex: 0,
          symbol: "circle",
          symbolSize: 6,
          sampling: "average",
          itemStyle: {
            color: "#00BCD4"
          },
          lineStyle: {
            color: "#00BCD4",
            width: 2,
            type: "dashed"
          },
          data: coursesComparison.value.map(item => 
            item.mediasConvencionales && item.mediasConvencionales.organizacion !== undefined ? 
            item.mediasConvencionales.organizacion : 0
          )
        }
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
    // Obtener el total de votos para esta habilidad
    let totalVotes = 0;
    
    if (skill === "liderazgo") {
      totalVotes = highlightedData.value.allStudents.reduce((sum, student) => sum + (student.liderazgo || 0), 0);
    } else if (skill === "creatividad") {
      totalVotes = highlightedData.value.allStudents.reduce((sum, student) => sum + (student.creatividad || 0), 0);
    } else if (skill === "organizacion") {
      totalVotes = highlightedData.value.allStudents.reduce((sum, student) => sum + (student.organizacion || 0), 0);
    }

    // Si no hay votos, evitar división por cero
    if (totalVotes === 0) return "0";

    // Calcular porcentaje (ahora basado en los votos reales, no en los posibles)
    const percentage = ((votes || 0) / totalVotes) * 100;

    // Devolver con un decimal
    return percentage.toFixed(1);
  } catch (error) {
    console.error("Error al calcular porcentaje:", error);
    return "0";
  }
};

// Nuevo método para clasificar estudiantes basado en distribución de votos
const getVoteDistribution = computed(() => {
  if (!highlightedData.value || !highlightedData.value.studentsWithVotes) return null;
  
  try {
    const students = highlightedData.value.studentsWithVotes;
    const totalStudents = students.length;
    
    // Calcular votos totales por competencia
    const totalVotes = {
      liderazgo: students.reduce((sum, s) => sum + (s.liderazgo || 0), 0),
      creatividad: students.reduce((sum, s) => sum + (s.creatividad || 0), 0),
      organizacion: students.reduce((sum, s) => sum + (s.organizacion || 0), 0)
    };
    
    // Clasificar estudiantes según concentración de votos
    const liderazgoDistribution = students
      .map(s => ({
        id: s.id,
        name: s.name,
        last_name: s.last_name,
        votes: s.liderazgo || 0,
        percentage: totalVotes.liderazgo > 0 ? ((s.liderazgo || 0) / totalVotes.liderazgo) * 100 : 0
      }))
      .sort((a, b) => b.percentage - a.percentage)
      .slice(0, 5); // Top 5
      
    const creatividadDistribution = students
      .map(s => ({
        id: s.id,
        name: s.name,
        last_name: s.last_name,
        votes: s.creatividad || 0,
        percentage: totalVotes.creatividad > 0 ? ((s.creatividad || 0) / totalVotes.creatividad) * 100 : 0
      }))
      .sort((a, b) => b.percentage - a.percentage)
      .slice(0, 5); // Top 5
      
    const organizacionDistribution = students
      .map(s => ({
        id: s.id,
        name: s.name,
        last_name: s.last_name,
        votes: s.organizacion || 0,
        percentage: totalVotes.organizacion > 0 ? ((s.organizacion || 0) / totalVotes.organizacion) * 100 : 0
      }))
      .sort((a, b) => b.percentage - a.percentage)
      .slice(0, 5); // Top 5
    
    return {
      liderazgo: liderazgoDistribution,
      creatividad: creatividadDistribution,
      organizacion: organizacionDistribution
    };
  } catch (error) {
    console.error("Error al calcular distribución de votos:", error);
    return null;
  }
});

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
        <!-- Componente de filtro -->
        <CourseFilter 
          :allCourses="allCourses"
          :initialCourseAndDivision="selectedCourseAndDivision"
          :initialThreshold="threshold"
          @update:courseAndDivision="updateCourseAndDivision"
          @update:threshold="updateThreshold"
          @loadData="loadData"
        />

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
            <p class="text-sm text-blue-800 mb-2">
              <span class="font-medium">Important:</span> Cada curs té la seva pròpia mitjana i desviació estàndard. 
              Passa el cursor sobre cada barra per veure aquestes estadístiques específiques.
            </p>
            <p class="text-sm text-blue-800">
              <span class="font-medium">Línies discontínues:</span> Les línies discontínues mostren la mitjana convencional de vots
              per a cada competència a cada curs, permetent comparar fàcilment entre grups.
            </p>
          </div>
          
          <!-- Nueva sección para métricas alternativas -->
          <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-yellow-50 p-4 rounded-md">
              <h3 class="font-semibold text-yellow-800 mb-2">Coeficient de Variació</h3>
              <p class="text-sm text-yellow-800">
                Mesura com estan de dispersos els vots. Un valor alt indica que els vots 
                estan més concentrats en pocs alumnes.
              </p>
            </div>
            <div class="bg-green-50 p-4 rounded-md">
              <h3 class="font-semibold text-green-800 mb-2">Índex de Concentració</h3>
              <p class="text-sm text-green-800">
                Indica com de distribuïts estan els vots: valors propers a 0 signifiquen 
                distribució uniforme, valors propers a 1 significa alta concentració.
              </p>
            </div>
            <div class="bg-purple-50 p-4 rounded-md">
              <h3 class="font-semibold text-purple-800 mb-2">Distribució Percentual</h3>
              <p class="text-sm text-purple-800">
                Mostra quin percentatge del total de vots ha rebut cada alumne, 
                facilitant la comparació entre competències.
              </p>
            </div>
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
          
          <!-- Nuevas métricas - Coeficiente de Variación e Índice de Concentración -->
          <div class="mt-4 mb-6 bg-white border rounded-lg shadow-sm">
            <div class="p-4 border-b">
              <h3 class="font-semibold text-gray-800">Mètriques Alternatives d'Anàlisi</h3>
              <p class="text-sm text-gray-600">
                Aquestes mètriques proporcionen una visió més profunda de la distribució dels vots 
                i complementen la mitjana tradicional
              </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
              <!-- Coeficiente de Variación -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Coeficient de Variació</h4>
                <p class="text-xs text-gray-600 mb-3">
                  Mesura la dispersió relativa. Valors alts indiquen distribució menys uniforme.
                </p>
                <div class="grid grid-cols-3 gap-2">
                  <div class="text-center">
                    <p class="text-xs text-gray-500">Lideratge</p>
                    <p class="text-xl font-bold text-[#FF9800]">
                      {{ highlightedData.mediasReales && highlightedData.stdDevs && highlightedData.mediasReales.liderazgo > 0 ? 
                         (highlightedData.stdDevs.liderazgo / highlightedData.mediasReales.liderazgo).toFixed(2) : '0.00' }}
                    </p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs text-gray-500">Creativitat</p>
                    <p class="text-xl font-bold text-[#9C27B0]">
                      {{ highlightedData.mediasReales && highlightedData.stdDevs && highlightedData.mediasReales.creatividad > 0 ? 
                         (highlightedData.stdDevs.creatividad / highlightedData.mediasReales.creatividad).toFixed(2) : '0.00' }}
                    </p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs text-gray-500">Organització</p>
                    <p class="text-xl font-bold text-[#00BCD4]">
                      {{ highlightedData.mediasReales && highlightedData.stdDevs && highlightedData.mediasReales.organizacion > 0 ? 
                         (highlightedData.stdDevs.organizacion / highlightedData.mediasReales.organizacion).toFixed(2) : '0.00' }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Índice de Concentración - Calculado de forma simplificada -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-800 mb-2">Diversitat de Vots</h4>
                <p class="text-xs text-gray-600 mb-3">
                  Indica la distribuciò de vots: valor més baix indica concentració en pocs alumnes.
                </p>
                <div class="grid grid-cols-3 gap-2">
                  <div class="text-center">
                    <p class="text-xs text-gray-500">Lideratge</p>
                    <p class="text-xl font-bold text-[#FF9800]">
                      {{ highlightedData.studentsWithVotes ? 
                         (highlightedData.studentsWithVotes.filter(s => s.liderazgo > 0).length / 
                          (highlightedData.studentsWithVotes.length || 1) * 100).toFixed(0) + '%' : '0%' }}
                    </p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs text-gray-500">Creativitat</p>
                    <p class="text-xl font-bold text-[#9C27B0]">
                      {{ highlightedData.studentsWithVotes ? 
                         (highlightedData.studentsWithVotes.filter(s => s.creatividad > 0).length / 
                          (highlightedData.studentsWithVotes.length || 1) * 100).toFixed(0) + '%' : '0%' }}
                    </p>
                  </div>
                  <div class="text-center">
                    <p class="text-xs text-gray-500">Organització</p>
                    <p class="text-xl font-bold text-[#00BCD4]">
                      {{ highlightedData.studentsWithVotes ? 
                         (highlightedData.studentsWithVotes.filter(s => s.organizacion > 0).length / 
                          (highlightedData.studentsWithVotes.length || 1) * 100).toFixed(0) + '%' : '0%' }}
                    </p>
                  </div>
                </div>
              </div>
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
            <v-chart class="w-full h-full" :option="bubbleRadarOptions" autoresize />
          </div>
          
          <!-- Leyenda explicativa del gráfico de radar con burbujas -->
          <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="font-medium text-gray-800 mb-2">Com interpretar el gràfic de radar</h3>
            <p class="text-sm text-gray-600 mb-2">
              Aquest gràfic combina la claredat d'un radar (amb els tres eixos de competències) amb la 
              visualització individual de cada alumne mitjançant bombolles.
            </p>
            <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
              <li><span class="text-[#FF9800] font-medium">Lideratge</span>: Representat a l'eix superior</li>
              <li><span class="text-[#9C27B0] font-medium">Creativitat</span>: Representat a l'eix inferior dret</li>
              <li><span class="text-[#00BCD4] font-medium">Organització</span>: Representat a l'eix inferior esquerre</li>
              <li><span class="font-medium">Bombolles</span>: Cada bombolla correspon a un alumne, amb les seves inicials</li>
              <li><span class="font-medium">Colors</span>: Cada alumne té un color diferent per facilitar la identificació</li>
            </ul>
            <p class="text-xs text-gray-500 mt-2">Passa el cursor sobre cada bombolla per veure més detalls.</p>
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

        <!-- Distribución de votos por estudiante -->
        <div
          v-if="getVoteDistribution"
          class="bg-white rounded-lg shadow-md p-6 mb-6"
        >
          <h2 class="text-xl font-semibold text-[#0080C0] mb-4">
            Distribució de Vots per Competència (Top 5)
          </h2>
          
          <p class="text-sm text-gray-600 mb-4">
            Aquesta gràfica mostra els alumnes que concentren més vots per cada competència, 
            indicant quin percentatge del total de vots han rebut.
          </p>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Liderazgo -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="font-medium text-[#FF9800] mb-3">Lideratge</h3>
              <div class="space-y-3">
                <div v-for="(student, index) in getVoteDistribution.liderazgo" :key="student.id">
                  <div class="flex justify-between text-sm">
                    <span class="truncate">{{ student.name }} {{ student.last_name }}</span>
                    <span class="font-medium">{{ student.percentage.toFixed(1) }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-[#FF9800] h-2 rounded-full" 
                      :style="`width: ${student.percentage}%`"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Creatividad -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="font-medium text-[#9C27B0] mb-3">Creativitat</h3>
              <div class="space-y-3">
                <div v-for="(student, index) in getVoteDistribution.creatividad" :key="student.id">
                  <div class="flex justify-between text-sm">
                    <span class="truncate">{{ student.name }} {{ student.last_name }}</span>
                    <span class="font-medium">{{ student.percentage.toFixed(1) }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-[#9C27B0] h-2 rounded-full" 
                      :style="`width: ${student.percentage}%`"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Organización -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="font-medium text-[#00BCD4] mb-3">Organització</h3>
              <div class="space-y-3">
                <div v-for="(student, index) in getVoteDistribution.organizacion" :key="student.id">
                  <div class="flex justify-between text-sm">
                    <span class="truncate">{{ student.name }} {{ student.last_name }}</span>
                    <span class="font-medium">{{ student.percentage.toFixed(1) }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-[#00BCD4] h-2 rounded-full" 
                      :style="`width: ${student.percentage}%`"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
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