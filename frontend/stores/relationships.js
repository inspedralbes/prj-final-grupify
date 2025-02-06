import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useStudentsStore } from "~/stores/studentsStore";

export const useRelationshipsStore = defineStore("relationships", () => {
  const relationships = ref([]);
  const isLoading = ref(false);
  const error = ref(null);

  // Store de estudiantes
  const studentsStore = useStudentsStore();

  // Fetch de relaciones
  const fetchRelationships = async () => {
    isLoading.value = true;
    try {
      const response = await fetch(
        "http://localhost:8000/api/sociogram-relationships/sociogram-relationships"
      );
      if (!response.ok) throw new Error("Error al obtener las relaciones");
      relationships.value = await response.json();
    } catch (err) {
      console.error("Error al cargar relaciones:", err);
      error.value = "Error al cargar relaciones";
    } finally {
      isLoading.value = false;
    }
  };

  // Obtener relaciones por curso y división
  const getRelationshipsByCourseAndDivision = (courseName, divisionName) => {
    const studentIds = studentsStore.students
      .filter(
        student =>
          student.course === courseName && student.division === divisionName
      )
      .map(student => student.id);

    return computed(() => {
      const result = relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            (rel.question_id === 15 || rel.question_id === 16)
        )
        .map(rel => {
          const user = studentsStore.students.find(
            student => student.id === rel.user_id
          );
          const peer = studentsStore.students.find(
            student => student.id === rel.peer_id
          );
          return {
            ...rel,
            user_name: user ? user.name : "Desconocido",
            user_last_name: user ? user.last_name : "",
            peer_name: peer ? peer.name : "Desconocido",
            peer_last_name: peer ? peer.last_name : "",
          };
        });

      // console.log('getRelationshipsByCourseAndDivision result:', result);
      return result;
    });
  };

  // Obtener habilidades por curso y división (misma lógica que relaciones)
  const getSkillsByCourseAndDivision = (courseName, divisionName) => {
    const studentIds = studentsStore.students
      .filter(
        student =>
          student.course === courseName && student.division === divisionName
      )
      .map(student => student.id);

    return computed(() => {
      const skillsMap = {};

      relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            [18, 19, 20].includes(rel.question_id)
        )
        .forEach(rel => {
          const peer = studentsStore.students.find(
            student => student.id === rel.peer_id
          );
          if (peer) {
            if (!skillsMap[peer.id]) {
              skillsMap[peer.id] = {
                peer_id: peer.id,
                peer_name: peer.name,
                peer_last_name: peer.last_name,
                liderazgo: 0,
                creatividad: 0,
                organizacion: 0,
              };
            }
            if (rel.question_id === 18) skillsMap[peer.id].liderazgo++;
            if (rel.question_id === 19) skillsMap[peer.id].creatividad++;
            if (rel.question_id === 20) skillsMap[peer.id].organizacion++;
          }
        });

      return Object.values(skillsMap);
    });
  };

  return {
    relationships,
    isLoading,
    error,
    fetchRelationships,
    getRelationshipsByCourseAndDivision,
    getSkillsByCourseAndDivision,
  };
});
