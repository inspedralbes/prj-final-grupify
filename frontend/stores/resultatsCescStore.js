import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useStudentsStore } from "~/stores/studentsStore";

export const useResultatCescStore = defineStore("resultatCesc", () => {
    const resultsCesc = ref([]); // Todos los resultados sin filtrar
    const filteredResults = ref([]); // Resultados filtrados
    const currentCourse = ref(null);
    const currentDivision = ref(null);
    const isLoading = ref(false);
    const error = ref(null);

    // Store de estudiantes
    const studentsStore = useStudentsStore();

    // Definir las etiquetas disponibles
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
            const response = await fetch("https://api.grupify.cat/api/cesc/ver-resultados");
            if (!response.ok) throw new Error("Error al obtener resultados");
            resultsCesc.value = await response.json();
            // console.log("Datos obtenidos del endpoint:", resultsCesc.value);
        } catch (err) {
            console.error("Error al cargar resultados:", err);
            error.value = "Error al cargar resultados";
        } finally {
            isLoading.value = false;
        }
    };

    // Método para obtener los resultados filtrados por curso y división
    const getCescByCourseAndDivision = (courseName, divisionName) => {
        console.log("Filtrando CESC por curso:", courseName, "y división:", divisionName);

        const studentIds = studentsStore.students
            .filter(student => student.course === courseName && student.division === divisionName)
            .map(student => student.id);

        console.log("Estudiantes en el curso y división:", studentIds);

        // Filtrar los resultados
        const filteredResults = resultsCesc.value
            .filter(rel => studentIds.includes(rel.peer_id))
            .map(rel => {
                // Encontrar los datos del estudiante
                const peer = studentsStore.students.find(student => student.id === rel.peer_id);
                
                // Obtener el nombre de la etiqueta (tag)
                const tag = tagTypes.find(tag => tag.id === rel.tag_id);
                
                return {
                    ...rel,
                    peer_name: peer ? peer.name : "Desconocido",
                    peer_last_name: peer ? peer.last_name : "",
                    tag_name: tag ? tag.name : "Desconocido" // Agregar el nombre de la etiqueta
                };
            });

        console.log("Resultados filtrados:", filteredResults);
        return filteredResults;
    };

    return {
        resultsCesc,
        filteredResults,
        isLoading,
        error,
        fetchResults,
        getCescByCourseAndDivision,
    };
});
