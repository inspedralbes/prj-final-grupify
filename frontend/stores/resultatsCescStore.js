import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useStudentsStore } from "~/stores/studentsStore";

export const useResultatCescStore = defineStore("resultatCesc", () => {
  const results = ref([]); // Todos los resultados sin filtrar
  const filteredResults = ref([]); // Resultados filtrados
  const currentCourse = ref(null);
  const currentDivision = ref(null);
  const isLoading = ref(false);
  const error = ref(null);

  // Store de estudiantes
  const studentsStore = useStudentsStore();

  // Definir los tipos de tags
  const tagTypes = [
    { id: 1, name: "Popular" },
    { id: 2, name: "Rechazado" },
    { id: 3, name: "Agresivo" },
    { id: 4, name: "Prosocial" },
    { id: 5, name: "Víctima" },
  ];

  // Fetch de resultados desde la API
  const fetchResults = async () => {
    isLoading.value = true;
    try {
      const response = await fetch("http://localhost:8000/api/cesc/ver-resultados");
      if (!response.ok) throw new Error("Error al obtener resultados");
      results.value = await response.json();
      filterResultsByCourseDivision();
    } catch (err) {
      console.error("Error al cargar resultados:", err);
      error.value = "Error al cargar resultados";
    } finally {
      isLoading.value = false;
    }
  };

  // Setear curso y división
  const setCurrentCourseAndDivision = (courseName, divisionName) => {
    currentCourse.value = courseName;
    currentDivision.value = divisionName;
    filterResultsByCourseDivision();
  };

  // Filtrar los resultados por curso y división
  const filterResultsByCourseDivision = () => {
    if (!currentCourse.value || !currentDivision.value) {
      filteredResults.value = [];
      return;
    }

    // Obtener lista de estudiantes en el curso y división seleccionados
    const studentIds = studentsStore.students
      .filter(student => student.course === currentCourse.value && student.division === currentDivision.value)
      .map(student => student.id);

    // Filtrar resultados que pertenezcan a esos estudiantes
    const filteredData = results.value.filter(result => studentIds.includes(result.peer_id));

    // Agrupar los resultados por `peer_id`
    const groupedResults = {};
    
    filteredData.forEach(result => {
      if (!groupedResults[result.peer_id]) {
        // Obtener datos del estudiante desde `studentsStore`
        const student = studentsStore.students.find(student => student.id === result.peer_id) || {};
        
        groupedResults[result.peer_id] = {
          peer_id: result.peer_id,
          peer_name: student.name || "Desconocido",
          peer_last_name: student.last_name || "",
          tags: {}
        };
      }

      // Guardar los votos de cada tag
      groupedResults[result.peer_id].tags[result.tag_id] = result.vote_count;
    });

    filteredResults.value = Object.values(groupedResults);
  };

  // Obtener los resultados en formato tabla
  const getResultsTable = computed(() => {
    return filteredResults.value.map(student => ({
      name: `${student.peer_name} ${student.peer_last_name}`,
      ...tagTypes.reduce((acc, tag) => ({
        ...acc,
        [tag.name]: student.tags[tag.id] || 0
      }), {})
    }));
  });

  return {
    results,
    filteredResults,
    isLoading,
    error,
    tagTypes,
    fetchResults,
    setCurrentCourseAndDivision,
    getResultsTable,
  };
});